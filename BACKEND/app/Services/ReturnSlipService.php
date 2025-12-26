<?php

namespace App\Services;

use App\Models\ReturnSlip;
use App\Models\ReturnSlipDetail;
use App\Models\DeviceUnits;
use App\Models\Borrows;
use App\Mail\ReturnOtpMail;
use App\Models\DeviceMaintenance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ReturnSlipService
{

    public function sendReturnOtp(int $borrowId)
    {
        $borrow = Borrows::with('borrower')->findOrFail($borrowId);

        if (!in_array($borrow->status, ['completed', 'overdue'])) {
            throw ValidationException::withMessages(['Phiếu không ở trạng thái có thể trả.']);
        }

        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $key = "return_otp_{$borrowId}";

        Cache::put($key, $otp, now()->addMinutes(10));

        Mail::to($borrow->borrower->email)->queue(new ReturnOtpMail($borrow, $otp));

        return [
            'message' => 'Mã OTP đã được gửi đến email người mượn.',
            'email' => $borrow->borrower->email
        ];
    }

    public function verifyReturnOtp(int $borrowId, string $otp)
    {
        $key = "return_otp_{$borrowId}";
        $cachedOtp = Cache::get($key);

        if (!$cachedOtp) {
            return [
                'success' => false,
                'message' => 'Mã OTP đã hết hạn. Vui lòng gửi lại OTP.'
            ];
        }

        if ($cachedOtp !== $otp) {
            return [
                'success' => false,
                'message' => 'Mã OTP không đúng.'
            ];
        }

        Cache::forget($key);

        return [
            'success' => true,
            'message' => 'Xác thực OTP thành công.'
        ];
    }

    public function createReturnSlip(array $data): ReturnSlip
    {
        return DB::transaction(function () use ($data) {
            $borrow = Borrows::findOrFail($data['borrow_id']);

            $returnSlip = ReturnSlip::create([
                'borrow_id' => $data['borrow_id'],
                'return_date' => now(),
                'returned_by_staff_id' => auth()->id(),
                'overall_condition' => $data['overall_condition'] ?? 'good',
                'condition_notes' => $data['condition_notes'] ?? null,
            ]);


            foreach ($data['devices'] as $device) {
                $deviceUnit = DeviceUnits::with(['device', 'borrowDetail'])->find($device['device_unit_id']);
                $borrowDetail = $borrow->details->firstWhere('device_unit_id', $device['device_unit_id']);
                $oldCondition = $borrowDetail->condition_before_borrow ?? 'good';
                $newCondition = $device['condition_status'] ?? 'good';

                ReturnSlipDetail::create([
                    'return_slip_id' => $returnSlip->id,
                    'borrow_detail_id' => $device['borrow_detail_id'],
                    'device_unit_id' => $device['device_unit_id'],
                    'condition_status' => $newCondition,
                    'condition_notes' => $device['condition_notes'] ?? null,
                    'damage_description' => $device['damage_description'] ?? null,
                    'return_date' => now(),
                ]);

                if ($newCondition !== 'good') {
                    activity('device-damaged')
                        ->performedOn($deviceUnit)
                        ->causedBy(auth()->user())
                        ->withProperties([
                            'device_id' => $deviceUnit->device_id,
                            'device_name' => $deviceUnit->device->name,
                            'device_unit_id' => $deviceUnit->id,
                            'serial_number' => $deviceUnit->serial_number,

                            'condition_before' => $oldCondition,
                            'condition_after' => $newCondition,
                            'damage_level' => $this->getDamageLevel($newCondition),
                            'damage_description' => $device['damage_description'] ?? 'Không có mô tả',

                            'caused_by_user_id' => $borrow->borrower_id,
                            'caused_by_user_name' => $borrow->borrower->name,
                            'caused_by_user_email' => $borrow->borrower->email,
                            'caused_by_user_role' => $borrow->borrower->role,
                            'caused_by_user_code' => $borrow->borrower->getUserCode(),

                            'borrow_id' => $borrow->id,
                            'borrow_status' => $borrow->status,
                            'borrowed_date' => $borrow->borrowed_date,
                            'expected_return_date' => $borrow->expected_return_date,
                            'return_date' => $returnSlip->return_date,
                            'borrow_duration_days' => Carbon::parse($returnSlip->return_date)->diffInDays($borrow->borrowed_date),

                            'detected_by_staff_id' => auth()->id(),
                            'detected_by_staff_name' => auth()->user()->name,
                            'detected_at' => now()->toDateTimeString(),
                            'return_slip_id' => $returnSlip->id,
                            'is_late_return' => Carbon::parse($returnSlip->return_date)->gt($borrow->expected_return_date),
                            'late_days' => max(0, Carbon::parse($returnSlip->return_date)->diffInDays($borrow->expected_return_date, false)),
                        ])
                        ->log("🔴 Thiết bị bị hư hỏng: {$deviceUnit->device->name} (SN: {$deviceUnit->serial_number}) - " . ($device['damage_description'] ?? 'Không có mô tả'));

                    DeviceMaintenance::create([
                        'device_unit_id' => $device['device_unit_id'],
                        'type' => 'repair',
                        'description' => $device['damage_description'] ?? 'Hư hỏng khi trả',
                        'status' => 'pending',
                        'reported_by' => auth()->id(),
                    ]);

                    $deviceUnit->update(['status' => 'maintenance']);
                } else {
                    activity('device-returned-good')
                        ->performedOn($deviceUnit)
                        ->causedBy(auth()->user())
                        ->withProperties([
                            'device_name' => $deviceUnit->device->name,
                            'serial_number' => $deviceUnit->serial_number,
                            'returned_by_user' => $borrow->borrower->name,
                            'borrow_id' => $borrow->id,
                            'condition' => 'good',
                        ])
                        ->log("✅ Thiết bị được trả nguyên vẹn: {$deviceUnit->device->name} (SN: {$deviceUnit->serial_number})");

                    $deviceUnit->update(['status' => 'available']);
                }
            }

            activity('borrow')
                ->performedOn($borrow)
                ->causedBy(auth()->user())
                ->withProperties([
                    'borrower_name' => $borrow->borrower->name ?? 'N/A',
                    'borrower_email' => $borrow->borrower->email ?? 'N/A',
                    'device_count' => $returnSlip->details->count(),
                    'devices' => $returnSlip->details->map(fn($d) => [
                        'name' => $d->deviceUnit->device->name ?? 'N/A',
                        'serial' => $d->deviceUnit->serial_number ?? 'N/A',
                        'condition' => $d->condition_status,
                        'damage_fee' => $d->damage_fee ?? 0,
                    ])->toArray(),
                    'overall_condition' => $returnSlip->overall_condition,
                    'return_date' => $returnSlip->return_date->format('d/m/Y H:i'),
                    'expected_return_date' => $borrow->expected_return_date,
                ])
                ->log('Trả thiết bị');


            $borrow->update(['status' => 'returned']);

            $this->updateCreditScore($returnSlip, $borrow);
            return $returnSlip->load('details');
        });
    }



    private function updateCreditScore(ReturnSlip $returnSlip, Borrows $borrow)
    {
        $scoreChange = 0;

        $expectedReturnDate = Carbon::parse($borrow->expected_return_date);
        $actualReturnDate = Carbon::parse($returnSlip->return_date);

        $lateDays = 0;
        if ($actualReturnDate->gt($expectedReturnDate)) {
            $lateDays = $actualReturnDate->diffInDays($expectedReturnDate);
        }

        $hasDamage = $returnSlip->details->contains(function ($detail) {
            return $detail->condition_status !== 'good';
        });

        if ($lateDays > 0) {
            $scoreChange = -10;
        }

        if ($hasDamage) {
            $scoreChange -= 20;
        }

        $returnSlip->update([
            'late_days' => $lateDays,
            'credit_score_change' => $scoreChange,
        ]);


        if ($scoreChange < 0) {
            $borrower = $borrow->borrower;
            $borrower->increment('credit_score', $scoreChange);
        }

        if ($borrow->isFullyReturned()) {
            $borrow->update(['status' => 'returned']);
        }
    }

    public function getReturnSlip(int $id): ReturnSlip
    {
        return ReturnSlip::with([
            'borrow.borrower',
            'borrow.details.deviceUnit.device',
            'returnedByStaff',
            'details.deviceUnit.device'
        ])->findOrFail($id);
    }

    public function listReturnSlips(array $filters = [], int $perPage = 15)
    {
        $query = ReturnSlip::with([
            'borrow.borrower',
            'returnedByStaff',
            'details'
        ]);

        if (isset($filters['borrow_id'])) {
            $query->where('borrow_id', $filters['borrow_id']);
        }
        if (isset($filters['staff_id'])) {
            $query->where('returned_by_staff_id', $filters['staff_id']);
        }

        if (isset($filters['from_date'])) {
            $query->whereDate('return_date', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->whereDate('return_date', '<=', $filters['to_date']);
        }

        return $query->orderBy('return_date', 'desc')->paginate($perPage);
    }

    private function getDamageLevel(string $condition): string
    {
        return match ($condition) {
            'good' => 'Không hư hỏng',
            'minor_damage' => 'Hư hỏng nhẹ',
            'major_damage' => 'Hư hỏng nặng',
            'broken' => 'Hỏng hoàn toàn',
            default => 'Không xác định',
        };
    }
}
