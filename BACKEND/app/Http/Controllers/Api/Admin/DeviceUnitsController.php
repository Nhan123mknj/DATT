<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\DeviceUnitsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceUnitsController extends Controller
{
    protected DeviceUnitsService $deviceUnitService;
    public function __construct(DeviceUnitsService $deviceUnitService)
    {
        $this->deviceUnitService = $deviceUnitService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['device_id', 'status', 'is_active', 'search', 'order_by', 'direction']);
        $perPage = $request->get('per_page', 10);

        $deviceUnits = $this->deviceUnitService->listUnits($filters, $perPage);
        if ($deviceUnits->isEmpty()) {
            return response()->json(['message' => 'No device units found'], 404);
        }
        return response()->json($deviceUnits, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|exists:devices,id',
            'serial_number' => 'required|string|max:255|unique:device_units,serial_number',
            'status' => 'required|in:available,in_use,under_maintenance,retired',
            'is_active' => 'boolean',
            'purchase_date' => 'nullable|date|before_or_equal:today',
            'warranty_end' => 'nullable|date|after_or_equal:purchase_date',
            'notes' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            $deviceUnit = $this->deviceUnitService->createUnit($request->all());
            return response()->json(['message' => 'Tạo mới thành công', 'device_unit' => $deviceUnit], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create device unit: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $deviceUnit = $this->deviceUnitService->getUnitById($id);
            return response()->json(
                [
                    'message' => 'Success',
                    'device_unit' => $deviceUnit
                ],
                200
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Device unit not found: ' . $e->getMessage()], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'sometimes|required|exists:devices,id',
            'serial_number' => 'sometimes|required|string|max:255|unique:device_units,serial_number,' . $id,
            'status' => 'sometimes|required|in:available,in_use,maintenance,retired',
            'is_active' => 'boolean',
            'purchase_date' => 'nullable|date|before_or_equal:today',
            'warranty_end' => 'nullable|date|after_or_equal:purchase_date',
            'notes' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        try {
            $deviceUnit = $this->deviceUnitService->updateUnit($id, $request->all());
            return response()->json(['message' => 'Cập nhật thành công', 'device_unit' => $deviceUnit], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update device unit: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $this->deviceUnitService->deleteUnit($id);
            return response()->json(['message' => 'Đã xóa thành công'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete device unit: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export device units to Excel
     */
    public function export()
    {
        try {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\DeviceUnitsExport,
                'device_units_' . date('Y-m-d_His') . '.xlsx'
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to export: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Import device units from Excel
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            \Maatwebsite\Excel\Facades\Excel::import(
                new \App\Imports\DeviceUnitsImport,
                $request->file('file')
            );

            return response()->json([
                'message' => 'Import thành công',
            ], 200);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];

            foreach ($failures as $failure) {
                $errors[] = [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                ];
            }

            return response()->json([
                'message' => 'Import thất bại',
                'errors' => $errors
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to import: ' . $e->getMessage()
            ], 500);
        }
    }
}
