<?php

namespace App\Http\Controllers\Api\Admin;

use App\Exports\StaticsExport;
use App\Http\Controllers\Controller;
use App\Services\DeviceUnitsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Exp;

class DeviceUnitsController extends Controller
{
    protected DeviceUnitsService $deviceUnitService;
    public function __construct(DeviceUnitsService $deviceUnitService)
    {
        $this->deviceUnitService = $deviceUnitService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['device_id', 'status', 'is_active', 'search', 'order_by', 'direction']);
        $perPage = $request->get('per_page', 10);

        $deviceUnits = $this->deviceUnitService->listUnits($filters, $perPage);

        return response()->json([
            'data' => $deviceUnits
        ], 200);
    }

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

    public function show(string $id)
    {
        try {
            $deviceUnit = $this->deviceUnitService->getUnitById($id);

            $deviceUnit->load('device');

            return response()->json(
                [
                    'message' => 'Success',
                    'device_unit' => $deviceUnit,
                    'suggested_retirement_value' => $deviceUnit->suggested_retirement_value,
                    'depreciation_percentage' => $deviceUnit->depreciation_percentage,
                ],
                200
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Device unit not found: ' . $e->getMessage()], 404);
        }
    }



    public function update(Request $request, string $id)
    {

        $unit = \App\Models\DeviceUnits::find($id);
        if (!$unit) {
            return response()->json(['error' => 'Không tìm thấy thiết bị'], 404);
        }

        if ($unit->status === 'retired') {
            return response()->json(['error' => 'Không thể sửa thiết bị đã thanh lý'], 400);
        }

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



    public function retire(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'retire_reason' => 'required|string|max:500',
            'retirement_method' => 'required|in:sold,donated,discarded,recycled',
            'retirement_value' => 'nullable|numeric|min:0',
            'buyer_info' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $deviceUnit = $this->deviceUnitService->retireUnit($id, $request->only([
                'retire_reason',
                'retirement_method',
                'retirement_value',
                'buyer_info'
            ]));

            return response()->json([
                'message' => 'Thanh lý thiết bị thành công',
                'device_unit' => $deviceUnit->load('device', 'retiredBy'),
                'profit_loss' => $deviceUnit->profit_loss,
                'profit_loss_percentage' => $deviceUnit->profit_loss_percentage,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    /**
     * Thanh lý hàng loạt
     */
    public function bulkRetire(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_unit_ids' => 'required|array|min:1',
            'device_unit_ids.*' => 'required|exists:device_units,id',
            'retire_reason' => 'required|string|max:500',
            'retirement_method' => 'required|in:sold,donated,discarded,recycled',
            'retirement_value' => 'nullable|numeric|min:0',
            'buyer_info' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $retiredUnits = [];
            $errors = [];

            $retirementData = $request->only([
                'retire_reason',
                'retirement_method',
                'retirement_value',
                'buyer_info'
            ]);

            foreach ($request->device_unit_ids as $id) {
                try {
                    $unit = $this->deviceUnitService->retireUnit($id, $retirementData);
                    $retiredUnits[] = $unit;
                } catch (\Exception $e) {
                    $errors[] = [
                        'id' => $id,
                        'error' => $e->getMessage()
                    ];
                }
            }

            return response()->json([
                'message' => 'Đã thanh lý ' . count($retiredUnits) . '/' . count($request->device_unit_ids) . ' thiết bị',
                'retired' => $retiredUnits,
                'errors' => $errors,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }



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

    public function activityLog(string $id)
    {
        try {
            $deviceUnit = \App\Models\DeviceUnits::with('device')->findOrFail($id);

            $activities = \Spatie\Activitylog\Models\Activity::forSubject($deviceUnit)
                ->with('causer')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'device_unit' => [
                    'id' => $deviceUnit->id,
                    'device_name' => $deviceUnit->device->name,
                    'serial_number' => $deviceUnit->serial_number,
                    'current_status' => $deviceUnit->status,
                ],
                'activities' => $activities,
                'total_activities' => $activities->count(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get activity log: ' . $e->getMessage()], 500);
        }
    }

    public function damageHistory(string $id)
    {
        try {
            // Try to find the device unit (including soft-deleted)
            $deviceUnit = \App\Models\DeviceUnits::withTrashed()->with('device')->find($id);

            // If device unit doesn't exist at all, query activities directly by subject_id
            if (!$deviceUnit) {
                $damageActivities = \Spatie\Activitylog\Models\Activity::where('subject_type', \App\Models\DeviceUnits::class)
                    ->where('subject_id', $id)
                    ->where('log_name', 'device-damaged')
                    ->with('causer')
                    ->orderBy('created_at', 'desc')
                    ->get();

                if ($damageActivities->isEmpty()) {
                    return response()->json([
                        'error' => 'Device unit not found',
                        'message' => "Không tìm thấy thiết bị với ID: {$id}"
                    ], 404);
                }

                // Get device info from first activity's properties
                $firstActivity = $damageActivities->first();
                $props = $firstActivity->properties;

                $deviceInfo = [
                    'id' => $id,
                    'device_name' => $props['device_name'] ?? 'Unknown Device',
                    'serial_number' => $props['serial_number'] ?? 'N/A',
                    'current_status' => 'deleted',
                ];
            } else {
                // Device unit exists, use normal flow
                $damageActivities = \Spatie\Activitylog\Models\Activity::forSubject($deviceUnit)
                    ->where('log_name', 'device-damaged')
                    ->with('causer')
                    ->orderBy('created_at', 'desc')
                    ->get();

                $deviceInfo = [
                    'id' => $deviceUnit->id,
                    'device_name' => $deviceUnit->device->name ?? 'Unknown Device',
                    'serial_number' => $deviceUnit->serial_number,
                    'current_status' => $deviceUnit->status,
                ];
            }

            $damageHistory = $damageActivities->map(function ($activity) {
                $props = $activity->properties;
                return [
                    'id' => $activity->id,
                    'damage_date' => $activity->created_at->format('d/m/Y H:i:s'),
                    'damage_date_human' => $activity->created_at->diffForHumans(),
                    'damage_description' => $props['damage_description'] ?? '',
                    'damage_level' => $props['damage_level'] ?? '',
                    'damage_fee' => $props['damage_fee'] ?? 0,
                    'condition_change' => ($props['condition_before'] ?? '') . ' → ' . ($props['condition_after'] ?? ''),

                    'caused_by' => [
                        'id' => $props['caused_by_user_id'] ?? null,
                        'name' => $props['caused_by_user_name'] ?? '',
                        'email' => $props['caused_by_user_email'] ?? '',
                        'role' => $props['caused_by_user_role'] ?? '',
                        'code' => $props['caused_by_user_code'] ?? '',
                    ],

                    'borrow' => [
                        'id' => $props['borrow_id'] ?? null,
                        'borrowed_date' => $props['borrowed_date'] ?? '',
                        'expected_return_date' => $props['expected_return_date'] ?? '',
                        'duration_days' => $props['borrow_duration_days'] ?? 0,
                        'is_late' => $props['is_late_return'] ?? false,
                        'late_days' => $props['late_days'] ?? 0,
                    ],

                    'detected_by' => [
                        'id' => $props['detected_by_staff_id'] ?? null,
                        'name' => $props['detected_by_staff_name'] ?? '',
                        'at' => $props['detected_at'] ?? '',
                    ],

                    'return_slip_id' => $props['return_slip_id'] ?? null,
                ];
            });

            return response()->json([
                'device_unit' => $deviceInfo,
                'total_damages' => $damageHistory->count(),
                'total_repair_cost' => $damageHistory->sum('damage_fee'),
                'damage_history' => $damageHistory,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get damage history: ' . $e->getMessage()], 500);
        }
    }
    public function exportBorrowers()
    {
        return Excel::download(new StaticsExport, 'device_borrow_statics_' . date('Y-m-d_His') . '.xlsx');
    }
}
