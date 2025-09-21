<?php

namespace App\Services;

use App\Models\Devices;
use App\Models\CategoriesDevice;
use App\Models\DeviceUnits;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class DeviceService
{
    public function listDevices($filters = [], $perPage = 15)
    {
        $query = Devices::with('category', 'units');

        $allowSortFields = [
            'id' => 'id',
            'total_units' => 'total_units',
            'created_at' => 'created_at',
        ];

        if (isset($filters['order_by']) && array_key_exists($filters['order_by'], $allowSortFields)) {
            $orderBy = $allowSortFields[$filters['order_by']];
            $direction = $filters['direction'] ?? 'asc';
            $query->orderBy($orderBy, $direction);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('manufacturer', 'like', "%$search%");
            });
        }

        return $query->paginate($perPage);
    }
    public function getDeviceById($id)
    {
        return Devices::with('category:id,name', 'units')->findOrFail($id);
    }

    public function createDevice($data)
    {
        return Devices::create($data);
    }
    public function updateDevice($id, $data)
    {
        $device = Devices::findOrFail($id);
        if (!$device) {
            throw new Exception("Device not found");
        }
        $device->update($data);
        return $device;
    }

    public function deleteDevice($id)
    {
        return DB::transaction(function () use ($id) {
            $device = Devices::findOrFail($id);
            if (!$device) {
                throw new Exception("Device not found");
            }

            DeviceUnits::where('device_id', $id)->delete();
            $device->delete();
            return true;
        });
    }
}
