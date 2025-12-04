<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CategoriesDevice;
use App\Models\Devices;
use App\Models\DeviceUnits;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    /**
     * Get all device categories
     */
    public function categories()
    {
        $categories = CategoriesDevice::where('is_active', true)
            ->select('id', 'name', 'code', 'type', 'description')
            ->get();

        return response()->json([
            'message' => 'Success',
            'data' => $categories
        ], 200);
    }

    /**
     * Get devices by category
     */
    public function devicesByCategory(Request $request, $categoryId)
    {
        $category = CategoriesDevice::findOrFail($categoryId);

        $devices = Devices::where('category_id', $categoryId)
            ->active()
            ->select('id', 'name', 'model', 'manufacturer', 'category_id')
            ->with('category:id,name,code')
            ->get();

        return response()->json([
            'message' => 'Success',
            'category' => $category,
            'data' => $devices
        ], 200);
    }

    /**
     * Get available device units by device
     */
    public function deviceUnitsByDevice(Request $request, $deviceId)
    {
        // $device = Devices::with('category')->findOrFail($deviceId);

        $deviceUnits = DeviceUnits::where('device_id', $deviceId)
            ->where('status', 'available')
            // ->where('is_active', true)
            ->select('id', 'device_id', 'serial_number', 'status')
            ->get();

        return response()->json([
            'message' => 'Success',
            // 'device' => $device,
            'data' => $deviceUnits
        ], 200);
    }
}
