<?php

namespace App\Http\Controllers\Api\Borrower;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateReservation;
use App\Models\DeviceReservation;
use App\Services\ReservationService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    protected $reservationService;
    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DeviceReservation::with([
            'user:id,name,email',
            'details.deviceUnit.device',
            'approver:id,name'
        ]);

        if (auth('api')->user()->role === 'borrower') {
            $query->where('user_id', auth('api')->id());
        }

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

        // Không trả 404 nữa, luôn trả 200
        return response()->json([
            'message' => 'Success',
            'data' => $reservations
        ], 200);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateReservation $request)
    {
        try {
            $reservation = $this->reservationService->createReservation($request->validated());

            return response()->json([
                'message' => 'Đặt trước thiết bị thành công.',
                'reservation' => $reservation,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Tạo đặt trước thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $reservation = DeviceReservation::with([
                'user:id,name,email',
                'details.deviceUnit.device',
                'approver:id,name'
            ])->findOrFail($id);

            if (auth('api')->user()->role === 'borrower' && $reservation->user_id !== auth('api')->id()) {
                return response()->json([
                    'message' => 'Không có quyền truy cập đặt trước này.'
                ], 403);
            }

            return response()->json([
                'message' => 'Success',
                'data' => $reservation
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Đặt trước không tồn tại.'
            ], 404);
        }
    }
}
