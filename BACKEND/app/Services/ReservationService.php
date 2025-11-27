<?php

namespace App\Services;

use App\Jobs\AutoCreateBorrowJob;
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

            // $this->checkUserBorrowLimit($userId);

            $deviceUnits = DeviceUnits::whereIn('id', $devices->pluck('device_unit_id'))
                ->get()
                ->keyBy('id');

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
                    throw ValidationException::withMessages([
                        "devices" => "Thiết bị ID $unitId không tồn tại."
                    ]);
                }
                if ($this->checkConflict($unitId, $reservedFrom, $reservedUntil)) {
                    throw ValidationException::withMessages([
                        'devices' => "Thiết bị ID $unitId đã được đặt trong khoảng thời gian này."
                    ]);
                }
                $duration = $reservedUntil ?
                    now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($reservedUntil)->startOfDay()) + 1 :
                    1;

                $strategy = BorrowStrategyFactory::createStrategy($unit->device);
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
            throw ValidationException::withMessages([
                'status' => 'Yêu cầu không ở trạng thái chờ duyệt.'
            ]);
        }

        $reservation->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $reservation->details()->update(['status' => 'approved']);

        DB::afterCommit(function () use ($reservation) {

            if ($reservation->reserved_from->isFuture()) {
                AutoCreateBorrowJob::dispatch($reservation)
                    ->delay($reservation->reserved_from)
                    ->onQueue('reservations');

                \Log::info('✅ Scheduled AutoCreateBorrowJob với delay', [
                    'reservation_id' => $reservation->id,
                    'reserved_from' => $reservation->reserved_from,
                    'delay_seconds' => $reservation->reserved_from->diffInSeconds(now())
                ]);
            } else {
                AutoCreateBorrowJob::dispatch($reservation)
                    ->onQueue('reservations');

                \Log::info('✅ Dispatched AutoCreateBorrowJob ngay lập tức', [
                    'reservation_id' => $reservation->id
                ]);
            }

            $reservation->user->notify(
                new BorrowNotification(
                    "Yêu cầu đặt trước #{$reservation->id} đã được duyệt.",
                    $reservation->id
                )
            );
        });

        return $reservation->load('details.deviceUnit.device');
    }

    public function autoCreateBorrowFromReservation(int $reservationId)
    {
        try {
            $reservation = DeviceReservation::with('details.deviceUnit.device', 'user')
                ->findOrFail($reservationId);

            if ($reservation->status !== 'approved') {
                throw ValidationException::withMessages([
                    'status' => 'Không thể tạo phiếu mượn.'
                ]);
            }

            $borrowerId = $reservation->user_id;

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
                'borrower_id' => $borrowerId,
            ];

            $borrow = $this->borrowService->createBorrowingSlip($data);

            $reservation->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);

            return $borrow;
        } catch (\Exception $e) {
            \Log::error('autoCreateBorrowFromReservation error: ' . $e->getMessage(), [
                'reservation_id' => $reservationId,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function cancelReservation(int $reservationId)
    {
        $reservation = DeviceReservation::findOrFail($reservationId);

        if (!in_array($reservation->status, ['pending', 'approved'])) {
            throw ValidationException::withMessages([
                'status' => 'Không thể hủy reservation.'
            ]);
        }

        DB::transaction(function () use ($reservation) {
            foreach ($reservation->details as $detail) {
                $detail->deviceUnit->update(['status' => 'available']);
            }

            $reservation->update([
                'status' => 'cancelled',
                'cancelled_by' => auth()->id(),
                'cancelled_at' => now()
            ]);
        });

        return $reservation;
    }

    private function checkConflict($deviceUnitId, $from, $until)
    {
        return DeviceReservationDetail::where('device_unit_id', $deviceUnitId)
            ->whereHas('reservation', function ($q) use ($from, $until) {
                $q->where(function ($query) use ($from, $until) {
                    $query->whereBetween('reserved_from', [$from, $until])
                        ->orWhereBetween('reserved_until', [$from, $until])
                        ->orWhereRaw('? BETWEEN reserved_from AND reserved_until', [$from])
                        ->orWhereRaw('? BETWEEN reserved_from AND reserved_until', [$until]);
                })
                    ->whereNotIn('status', ['cancelled', 'completed']);
            })
            ->exists();
    }
}
