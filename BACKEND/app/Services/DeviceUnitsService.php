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
    public function deleteUnit($id)
    {
        return DB::transaction(function () use ($id) {
            $unit = DeviceUnits::findOrFail($id);
            if (!$unit) {
                throw new \Exception('Device unit not found');
            }
            $unit->delete();
            return true;
        });
    }
}
