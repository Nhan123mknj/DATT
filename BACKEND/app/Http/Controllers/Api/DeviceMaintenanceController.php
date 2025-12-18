<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceMaintenance;
use App\Models\DeviceUnits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceMaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $query = DeviceMaintenance::with(['deviceUnit.device', 'reporter', 'assignee']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('device_unit_id')) {
            $query->where('device_unit_id', $request->device_unit_id);
        }

        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $maintenances = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json($maintenances);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_unit_id' => 'required|exists:device_units,id',
            'type' => 'required|in:routine,repair,inspection,damage_report',
            'priority' => 'required|in:low,normal,high,urgent',
            'description' => 'required|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        $maintenance = DeviceMaintenance::create([
            ...$validated,
            'reported_by' => auth()->id(),
            'status' => 'pending',
        ]);

        return response()->json($maintenance, 201);
    }


    public function show(string $id)
    {
        $maintenance = DeviceMaintenance::with(['deviceUnit.device', 'reporter', 'assignee'])->findOrFail($id);
        return response()->json($maintenance);
    }


    public function update(Request $request, string $id)
    {
        $maintenance = DeviceMaintenance::findOrFail($id);

        $validated = $request->validate([
            'status' => 'sometimes|in:pending,in_progress,completed,cancelled',
            'assigned_to' => 'nullable|exists:users,id',
            'cost' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'next_maintenance_date' => 'nullable|date',
        ]);

        $maintenance->update($validated);

        if (isset($validated['status'])) {
            $deviceUnit = $maintenance->deviceUnit;
            if ($validated['status'] === 'in_progress') {
                $deviceUnit->update(['status' => 'maintenance']);
            } elseif ($validated['status'] === 'completed') {
                $deviceUnit->update(['status' => 'available']);
            }
        }

        return response()->json($maintenance);
    }

    public function destroy(string $id)
    {
        $maintenance = DeviceMaintenance::findOrFail($id);
        $maintenance->delete();

        return response()->json(['message' => 'Maintenance record deleted successfully']);
    }
}
