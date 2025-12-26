<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Services\ReturnSlipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReturnSlipController extends Controller
{
    protected ReturnSlipService $returnSlipService;

    public function __construct(ReturnSlipService $returnSlipService)
    {
        $this->returnSlipService = $returnSlipService;
    }

    /**
     * Display a listing of return slips
     */
    public function index(Request $request)
    {
        $filters = $request->only(['borrow_id', 'staff_id', 'from_date', 'to_date']);
        $perPage = $request->get('per_page', 15);

        $returnSlips = $this->returnSlipService->listReturnSlips($filters, $perPage);

        return response()->json($returnSlips);
    }

    /**
     * Store a newly created return slip
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'borrow_id' => 'required|exists:borrows,id',
            'overall_condition' => 'nullable|in:good,minor_damage,major_damage,broken',
            'condition_notes' => 'nullable|string',
            'devices' => 'required|array|min:1',
            'devices.*.borrow_detail_id' => 'required|exists:borrow_details,id',
            'devices.*.device_unit_id' => 'required|exists:device_units,id',
            'devices.*.condition_status' => 'required|in:good,minor_damage,major_damage,broken',
            'devices.*.condition_notes' => 'nullable|string',
            'devices.*.damage_description' => 'nullable|string',
            'devices.*.damage_fee' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $returnSlip = $this->returnSlipService->createReturnSlip($request->all());

            return response()->json([
                'message' => 'Tạo phiếu trả thành công',
                'return_slip' => $returnSlip,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create return slip: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified return slip
     */
    public function show(string $id)
    {
        try {
            $returnSlip = $this->returnSlipService->getReturnSlip($id);

            return response()->json($returnSlip);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Return slip not found: ' . $e->getMessage()
            ], 404);
        }
    }
}
