<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeviceReservation;
use Illuminate\Http\Request;

class ReservationsController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = DeviceReservation::with(['user', 'details.deviceUnit.device'])
                ->orderBy('created_at', 'desc');

            if ($request->has('status')) {
                $query->whereIn('status', (array) $request->status);
            }

            if ($request->has('from_date')) {
                $query->whereDate('reserved_from', '>=', $request->from_date);
            }

            if ($request->has('to_date')) {
                $query->whereDate('reserved_from', '<=', $request->to_date);
            }

            $perPage = $request->get('per_page', 15);
            $reservations = $query->latest()->paginate($perPage);
            return response()->json([
                'success' => true,
                'data' => $reservations
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải dữ liệu',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
