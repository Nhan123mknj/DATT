<?php

namespace App\Services;

use App\Models\Devices;
use App\Models\CategoriesDevice;
use App\Models\DeviceUnits;
use Illuminate\Support\Facades\DB;

class DeviceCategoryService
{
    public function listCategories($filters = [], $perPage = 15)
    {
        $query = CategoriesDevice::with('devices');

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%$search%");
        }

        $allowSortFields = [
            'name' => 'name',
            'created_at' => 'created_at',
        ];
        if (isset($filters['order_by']) && array_key_exists($filters['order_by'], $allowSortFields)) {
            $orderBy = $allowSortFields[$filters['order_by']];
            $direction = $filters['direction'] ?? 'asc';
            $query->orderBy($orderBy, $direction);
        }

        if (isset($filters['with_devices']) && $filters['with_devices']) {
            $query->whereHas('devices');
        }

        return $query->paginate($perPage);
    }


    public function previewDelete($id)
    {
        $category = CategoriesDevice::findOrFail($id);

        $devices = Devices::where('category_id', $id)
            ->with('units')
            ->get();

        return [
            'category' => $category,
            'devices' => $devices
        ];
    }

    public function deleteCategory($id)
    {
        return DB::transaction(function () use ($id) {
            $category = CategoriesDevice::findOrFail($id);

            foreach ($category->devices as $device) {
                DeviceUnits::where('device_id', $device->id)->delete();
                $device->delete();
            }

            $category->delete();

            return true;
        });
    }
    public function getCategoryById($id)
    {
        return CategoriesDevice::with('devices.units')->findOrFail($id);
    }

    public function createCategory($data)
    {
        return CategoriesDevice::create($data);
    }
    public function updateCategory($id, $data)
    {
        $category = CategoriesDevice::findOrFail($id);
        if (!$category) {
            throw new \Exception("Category not found");
        }
        $category->update($data);
        return $category;
    }
}
