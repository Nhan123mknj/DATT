<?php

namespace App\Http\Controllers\Api\Staff;

use App\Http\Controllers\Controller;
use App\Services\ReservationService;
use App\Models\DeviceReservation;
use App\Notifications\ReservationApproved;
use App\Notifications\ReservationRejected;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    protected ReservationService $reservationService;
    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    /**
     * Get all reservations (staff view)
     */
    public function index(Request $request)
    {
        $query = DeviceReservation::with([
            'user:id,name,email,role',
            'user.student:user_id,student_code,grade_level,class_name',
            'user.teacher:user_id,teacher_code,department,position',
            'details.deviceUnit.device',
            'approver:id,name'
        ]);

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
     * Get reservation statistics
     */
    public function statistics()
    {
        try {
            $stats = [
                'pending' => DeviceReservation::where('status', 'pending')->count(),
                'approved' => DeviceReservation::where('status', 'approved')->count(),
                'completed' => DeviceReservation::where('status', 'completed')->count(),
                'rejected' => DeviceReservation::where('status', 'rejected')->count(),
                'cancelled' => DeviceReservation::where('status', 'cancelled')->count(),
                'total' => DeviceReservation::count(),
            ];

            return response()->json([
                'data' => $stats
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Không thể lấy thống kê',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single reservation detail
     */
    public function show($id)
    {
        try {
            $reservation = DeviceReservation::with([
                'user:id,name,email,role',
                'user.student:user_id,student_code,grade_level,class_name',
                'user.teacher:user_id,teacher_code,department,position',
                'details.deviceUnit.device',
                'approvedBy'
            ])->findOrFail($id);

            return response()->json([
                'data' => $reservation
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Yêu cầu đặt trước không tồn tại'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Không thể lấy chi tiết',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function approve($id)
    {
        try {
            if (!in_array(auth('api')->user()->role, ['staff', 'admin'])) {
                return response()->json([
                    'message' => 'Unauthorized. Only staff/admin can approve.'
                ], 403);
            }

            $reservation = $this->reservationService->approveReservation($id);

            // Send notification to borrower
            $reservation->user->notify(new ReservationApproved($reservation));

            return response()->json([
                'message' => 'Đã duyệt đặt trước. Hệ thống sẽ tự động tạo phiếu mượn khi đến thời gian.',
                'data' => $reservation
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Approve thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        try {
            if (!in_array(auth('api')->user()->role, ['staff', 'admin'])) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            $request->validate([
                'reason' => 'required|string|max:500'
            ]);

            $reservation = \App\Models\DeviceReservation::findOrFail($id);

            if ($reservation->status !== 'pending') {
                return response()->json([
                    'message' => 'Chỉ có thể reject đặt trước đang chờ duyệt'
                ], 422);
            }

            \DB::transaction(function () use ($reservation, $request) {
                // Release device units
                foreach ($reservation->details as $detail) {
                    $detail->deviceUnit->update(['status' => 'available']);
                }

                $reservation->update([
                    'status' => 'rejected',
                    'rejection_reason' => $request->reason,
                    'approved_by' => auth('api')->id(),
                    'approved_at' => now()
                ]);
            });

            // Send notification to borrower
            $reservation->user->notify(new ReservationRejected($reservation, $request->reason));

            return response()->json([
                'message' => 'Đã từ chối đặt trước',
                'data' => $reservation->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Reject thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cancel($id)
    {
        try {
            $reservation = \App\Models\DeviceReservation::findOrFail($id);

            // Chỉ user tạo mới cancel được
            if ($reservation->user_id !== auth('api')->id()) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            if (!in_array($reservation->status, ['pending', 'approved'])) {
                return response()->json([
                    'message' => 'Không thể hủy đặt trước này'
                ], 422);
            }

            $reservation = $this->reservationService->cancelReservation($id);

            return response()->json([
                'message' => 'Đã hủy đặt trước thành công',
                'data' => $reservation
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Hủy đặt trước thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function createBorrowManually($id)
    {
        try {
            if (!in_array(auth('api')->user()->role, ['staff', 'admin'])) {
                return response()->json([
                    'message' => 'Unauthorized'
                ], 403);
            }

            $borrow = $this->reservationService->autoCreateBorrowFromReservation($id);

            return response()->json([
                'message' => 'Đã tạo phiếu mượn thủ công thành công',
                'data' => $borrow
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Tạo phiếu mượn thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
