<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoriesDevice;
use App\Models\Devices;
use App\Services\DeviceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceController extends Controller
{
    protected DeviceService $deviceService;
    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['category_id', 'is_active', 'search']);
        $perPage = $request->get('per_page', 15);

        $devices = $this->deviceService->listDevices($filters, $perPage);
        if ($devices->isEmpty()) {
            return response()->json(['message' => 'No devices found'], 404);
        }
        return response()->json([
            'message' => 'Success',
            'devices' => $devices
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:device_categories,id',
            'manufacturer' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'specifications' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            $device = $this->deviceService->createDevice($request->all());
            return response()->json(['message' => 'Tạo mới thành công', 'device' => $device], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create device: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $device = $this->deviceService->getDeviceById($id);
            return response()->json($device, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Device not found: ' . $e->getMessage()], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|exists:device_categories,id',
            'manufacturer' => 'sometimes|required|string|max:255',
            'model' => 'sometimes|required|string|max:255',
            'serial_number' => 'sometimes|required|string|max:255|unique:devices,serial_number,' . $id,
            'purchase_date' => 'sometimes|required|date',
            'warranty_expiry' => 'sometimes|nullable|date|after_or_equal:purchase_date',
            'is_active' => 'sometimes|boolean',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            $device = $this->deviceService->updateDevice($id, $request->all());
            return response()->json(['message' => 'Cập nhật thành công', 'device' => $device], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update device: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->deviceService->deleteDevice($id);
            return response()->json(['message' => 'Đã xóa thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete device: ' . $e->getMessage()], 500);
        }
    }
}
