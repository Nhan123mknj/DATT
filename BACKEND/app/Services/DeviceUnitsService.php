<?php

namespace App\Services;

use App\Models\DeviceUnits;
use Illuminate\Support\Facades\DB;

class DeviceUnitsService
{
    public function listUnits($filters = [], $perPage = 15)
    {
        $devicelUnits = DeviceUnits::with('device:id,name');
        $allowSortFields = [
            'created_at' => 'created_at',
        ];

        if (isset($filters['order_by']) && array_key_exists($filters['order_by'], $allowSortFields)) {
            $orderBy = $allowSortFields[$filters['order_by']];
            $direction = $filters['direction'] ?? 'asc';
            $devicelUnits->orderBy($orderBy, $direction);
        }

        if (isset($filters['device_id'])) {
            $devicelUnits->where('device_id', $filters['device_id']);
        }
        if (isset($filters['status'])) {
            $devicelUnits->where('status', $filters['status']);
        }

        if (isset($filters['is_active'])) {
            $devicelUnits->where('is_active', $filters['is_active']);
        }
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $devicelUnits->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%$search%");
            });
        }
        return $devicelUnits->paginate($perPage);
    }

    public function getUnitById($id)
    {
        return DeviceUnits::with('device:id,name')->findOrFail($id);
    }
    public function createUnit($data)
    {
        return DeviceUnits::create($data);
    }
    public function updateUnit($id, $data)
    {
        $unit = DeviceUnits::findOrFail($id);
        if (!$unit) {
            throw new \Exception('Device unit not found');
        }
        $unit->update($data);
        return $unit;
    }
    public function retireUnit($id, $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $unit = DeviceUnits::with('device')->findOrFail($id);

            if ($unit->status === 'borrowed' || $unit->status === 'reserved') {
                throw new \Exception('Không thể thanh lý thiết bị đang được mượn hoặc đặt trước');
            }

            $retirementValue = 0;

            if (isset($data['retirement_method']) && $data['retirement_method'] === 'sold') {
                if (isset($data['retirement_value']) && $data['retirement_value'] > 0) {
                    $retirementValue = $data['retirement_value'];
                } else {
                    $retirementValue = $unit->suggested_retirement_value;
                }
            }

            $unit->update([
                'status' => 'retired',
                'retired_at' => now(),
                'retired_by' => auth()->id(),
                'retire_reason' => $data['retire_reason'],
                'retirement_value' => $retirementValue,
                'retirement_method' => $data['retirement_method'] ?? 'discarded',
                'buyer_info' => $data['buyer_info'] ?? null,
            ]);

            $profitLoss = $retirementValue - ($unit->device->price ?? 0);
            $profitLossPercentage = $unit->device->price > 0
                ? round(($profitLoss / $unit->device->price) * 100, 2)
                : 0;

            activity('device-retired')
                ->performedOn($unit)
                ->causedBy(auth()->user())
                ->withProperties([
                    'device_name' => $unit->device->name ?? 'N/A',
                    'serial_number' => $unit->serial_number,
                    'retire_reason' => $data['retire_reason'],
                    'retirement_method' => $data['retirement_method'] ?? 'discarded',
                    'original_price' => $unit->device->price ?? 0,
                    'retirement_value' => $retirementValue,
                    'profit_loss' => $profitLoss,
                    'profit_loss_percentage' => $profitLossPercentage . '%',
                    'retired_by_name' => auth()->user()->name,
                    'retired_at' => now()->toDateTimeString(),
                    'buyer_info' => $data['buyer_info'] ?? null,
                    'auto_calculated' => !isset($data['retirement_value']) || $data['retirement_value'] <= 0,
                ])
                ->log("Thanh lý thiết bị: {$unit->device->name} (SN: {$unit->serial_number})");


            $unit->delete();

            return $unit;
        });
    }
}
