<?php

namespace App\Services;

use App\Models\DeviceReservation;
use App\Models\DeviceReservationDetail;
use App\Models\DeviceUnits;
use App\Models\User;
use App\Notifications\BorrowNotification;
use App\Services\Borrow\BorrowStrategyFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationService extends BaseService
{
    protected $borrowService;
    public function __construct(BorrowService $borrowService)
    {
        $this->borrowService = $borrowService;
    }

    public function createReservation(array $data)
    {
        return $this->runInTransactionWithRetry(function () use ($data) {
            $userId = auth('api')->user()->id;
            $reservedFrom = $data['reserved_from'];
            $reservedUntil = $data['reserved_until'];
            $devices = collect($data['devices']);

            $this->checkUserBorrowLimit($userId);

            $deviceUnits = DeviceUnits::whereIn('id', $devices->pluck('device_unit_id'))->get()->keyBy('id');

            $reservation = DeviceReservation::create([
                'user_id' => $userId,
                'reserved_from' => $reservedFrom,
                'reserved_until' => $reservedUntil,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($devices as $item) {
                $unitId = $item['device_unit_id'];
                $unit = $deviceUnits[$unitId] ?? null;
                if (!$unit) {
                    throw ValidationException::withMessages(["devices" => "Thiết bị ID $unitId không tồn tại."]);
                }

                $duration = $reservedUntil ? now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($reservedUntil)->startOfDay()) + 1 : 1;

                $strategy = BorrowStrategyFactory::createStrategy($unit->device); // Tái sử dụng từ BorrowService
                $strategy->validateBorrow([
                    'device_id' => $unit->device_id,
                    'device_unit_id' => $unitId,
                    'quantity' => 1,
                    'duration' => $duration,
                    'user_id' => $userId,
                ]);

                $result = $strategy->processBorrow([
                    'device_id' => $unit->device_id,
                    'device_unit_id' => $unitId,
                    'quantity' => 1,
                    'duration' => $duration,
                    'user_id' => $userId,
                ]);

                DeviceReservationDetail::create([
                    'reservation_id' => $reservation->id,
                    'device_unit_id' => $unitId,
                    'status' => 'pending',
                ]);

                $unit->update(['status' => 'reserved']);
            }

            DB::afterCommit(function () use ($reservation) {
                $staffs = User::where('role', 'staff')->get();
                $message = auth('api')->user()->name . " đã tạo yêu cầu đặt trước #{$reservation->id}";
                foreach ($staffs as $staff) {
                    $staff->notify(new BorrowNotification($message, $reservation->id));
                }
            });

            return $reservation->load('details.deviceUnit.device');
        });
    }

    public function approveReservation(int $reservationId)
    {
        $reservation = DeviceReservation::findOrFail($reservationId);

        if ($reservation->status !== 'pending') {
            throw ValidationException::withMessages(['Yêu cầu không ở trạng thái chờ duyệt.']);
        }
        $reservation->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $reservation->details()->update(['status' => 'approved']);

        DB::afterCommit(function () use ($reservation) {
            // $reservation->user->notify(new BorrowNotification("Yêu cầu đặt trước của bạn đã được duyệt."));
        });

        return $reservation->load('details.deviceUnit.device');
    }

    public function autoCreateBorrowFromReservation(int $reservationId)
    {
        $reservation = DeviceReservation::with('details.deviceUnit.device')->findOrFail($reservationId);

        if ($reservation->status !== 'approved') {
            throw ValidationException::withMessages(['Không thể tạo phiếu mượn.']);
        }

        $data = [
            'expected_return_date' => $reservation->reserved_until->toDateString(),
            'devices' => $reservation->details->map(function ($detail) {
                return [
                    'device_unit_id' => $detail->device_unit_id,
                    'condition_at_borrow' => 'tốt',
                ];
            })->toArray(),
            'notes' => "Tự động từ đặt trước #{$reservationId}",
            'commitment_file' => null,
        ];


        $borrow = $this->borrowService->createBorrowingSlip($data);

        $reservation->update(['status' => 'completed']);

        return $borrow;
    }
}
