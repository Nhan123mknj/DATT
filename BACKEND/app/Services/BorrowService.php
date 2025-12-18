<?php

namespace App\Services;

use App\Events\CreateBorrowingSlip;
use App\Mail\IssueOtpMail;
use App\Models\Borrows;
use App\Models\BorrowsDetail;
use App\Models\DeviceMaintenance;
use App\Models\DeviceUnits;
use App\Models\User;
use App\Services\BaseService;

use App\Services\Borrow\BorrowStrategyFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class BorrowService extends BaseService
{
    public function showBorrowingSlip($filters = [], $perPage = 15,)
    {
        $query = Borrows::with([
            'borrower:id,name,email,role',
            'borrower.student:user_id,student_code,grade_level,class_name',
            'borrower.teacher:user_id,teacher_code,department,position',
            'details.deviceUnit.device',
            'createdBy:id,name',
            'issuedBy:id,name',
        ]);


        if (in_array(auth()->user()->role, ['student', 'teacher'])) {
            $query->where('borrower_id', auth()->id());
        }

        if (isset($filters['status'])) {
            $query->whereIn('status', (array)$filters['status']);
        }
        return $query->paginate($perPage);
    }

    public function createBorrowingSlip(array $data)
    {
        return $this->runInTransactionWithRetry(function () use ($data) {
            try {
                $userId = $data['borrower_id'] ?? auth('api')->user()->id;
                $expectedReturn = $data['expected_return_date'];
                $devices = collect($data['devices']);
                $fromReservation = $data['from_reservation'] ?? false;
                $autoApprove = $data['auto_approve'] ?? false;

                if ($devices->isEmpty()) {
                    throw ValidationException::withMessages([
                        'devices' => 'Phải chọn ít nhất một thiết bị.'
                    ]);
                }

                $deviceUnitIds = $devices->pluck('device_unit_id')->toArray();
                $deviceUnits = DeviceUnits::with('device')->whereIn('id', $deviceUnitIds)->get()->keyBy('id');

                foreach ($deviceUnitIds as $unitId) {
                    if (!isset($deviceUnits[$unitId])) {
                        throw ValidationException::withMessages([
                            'devices' => "Thiết bị ID {$unitId} không tồn tại."
                        ]);
                    }
                    $unit = $deviceUnits[$unitId];
                    if (!$unit->device) {
                        throw ValidationException::withMessages([
                            'devices' => "Thiết bị ID {$unitId} không có thông tin device."
                        ]);
                    }

                    if (!$fromReservation && $unit->status === 'reserved') {
                        throw ValidationException::withMessages([
                            'devices' => "Thiết bị '{$unit->device->name}' (Unit #{$unitId}) đang được đặt trước cho người khác. Không thể tạo phiếu mượn."
                        ]);
                    }
                }

                $this->checkUserBorrowLimit($userId);

                $hasExpensive = $deviceUnits->contains(function ($unit) {
                    return $unit->device && $unit->device->category_id == 2;
                });
                if ($hasExpensive && empty($data['commitment_file'])) {
                    throw ValidationException::withMessages([
                        'commitment_file' => 'Thiết bị đắt tiền yêu cầu nộp file cam kết trách nhiệm.'
                    ]);
                }

                $initialStatus = $autoApprove ? 'approved' : ($fromReservation ? 'pending' : 'pending');


                $creatorId = $fromReservation ? null : auth()->id();

                $borrow = Borrows::create([
                    'borrower_id' => $userId,
                    'borrowed_date' => now(),
                    'expected_return_date' => $expectedReturn,
                    'status' => $initialStatus,
                    'notes' => $data['notes'] ?? null,
                    'commitment_file' => $data['commitment_file'] ?? null,
                    'created_by_user_id' => $creatorId,
                ]);

                $devices->each(function ($deviceData) use ($borrow, $deviceUnits, $expectedReturn, $userId, $fromReservation, $autoApprove) {
                    $deviceUnitId = $deviceData['device_unit_id'];
                    $deviceUnit = $deviceUnits[$deviceUnitId];

                    $duration = $expectedReturn
                        ? now()->startOfDay()->diffInDays(Carbon::parse($expectedReturn)->startOfDay()) + 1
                        : 1;

                    $strategy = BorrowStrategyFactory::createStrategy($deviceUnit->device);

                    if (!$fromReservation) {
                        $strategy->validateBorrow([
                            'device_id' => $deviceUnit->device_id,
                            'device_unit_id' => $deviceUnitId,
                            'quantity' => 1,
                            'duration' => $duration,
                            'user_id' => $userId,
                        ]);

                        $result = $strategy->processBorrow([
                            'device_id' => $deviceUnit->device_id,
                            'device_unit_id' => $deviceUnitId,
                            'quantity' => 1,
                            'duration' => $duration,
                            'user_id' => $userId,
                        ]);
                    } else {
                        $result = [
                            'status' => 'pending',
                            'deposit_amount' => 0
                        ];
                    }

                    if ($autoApprove) {

                        if (!$fromReservation) {
                            $deviceUnit->status = 'reserved';
                            $deviceUnit->save();
                        }
                    }


                    $detailStatus = $autoApprove ? 'pending' : ($result['status'] ?? 'pending');

                    BorrowsDetail::create([
                        'borrow_id' => $borrow->id,
                        'device_unit_id' => $deviceUnitId,
                        'status' => $detailStatus,
                        'condition_at_borrow' => $deviceData['condition_at_borrow'] ?? 'good',
                        'deposit_amount' => $result['deposit_amount'] ?? 0,
                    ]);
                });

                DB::afterCommit(function () use ($borrow) {
                    event(new CreateBorrowingSlip($borrow));
                });

                return $borrow->load('details');
            } catch (\Exception $e) {
                \Log::error('createBorrowingSlip error: ' . $e->getMessage(), [
                    'data' => $data,
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        });
    }

    public function getDetailBorrowingSlip($id)
    {
        $result = Borrows::with([
            'borrower:id,name,email,phone',
            'details:id,borrow_id,device_unit_id',
            'details.deviceUnit:id,device_id,serial_number',
            'details.deviceUnit.device:id,name',
            'createdBy:id,name',
            'issuedBy:id,name',
        ])->findOrFail($id);

        Gate::authorize('view', $result);

        return response()->json($result, 200);
    }


    public function approveBorrowRequest(int $borrowId)
    {
        return $this->runInTransactionWithRetry(function () use ($borrowId) {
            $borrow = Borrows::with('details')->lockForUpdate()->findOrFail($borrowId);

            Gate::authorize('approve', $borrow);

            if ($borrow->status !== 'pending') {
                throw ValidationException::withMessages(['Phiếu không ở trạng thái chờ duyệt.']);
            }

            $this->checkUserBorrowLimit($borrow->borrower_id);

            $borrow->status = 'approved';
            $borrow->save();

            // DB::afterCommit(function () use ($borrow) {

            // });

            return $borrow->load('details');
        });
    }

    public function sendIssueOtp(int $borrowId)
    {
        $borrow = Borrows::with('borrower')->findOrFail($borrowId);

        if ($borrow->status !== 'approved') {
            throw ValidationException::withMessages(['Phiếu không ở trạng thái chờ xuất kho.']);
        }

        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $key = "issue_otp_{$borrowId}";

        Cache::put($key, $otp, now()->addMinutes(5));

        Mail::to($borrow->borrower->email)->queue(new IssueOtpMail($borrow, $otp));

        return [
            'message' => 'Mã OTP đã được gửi đến email người mượn.',
            'email' => $borrow->borrower->email
        ];
    }

    public function sendReturnOtp(int $borrowId)
    {
        $borrow = Borrows::with('borrower')->findOrFail($borrowId);

        if (!in_array($borrow->status, ['borrowed', 'overdue'])) {
            throw ValidationException::withMessages(['Phiếu không ở trạng thái có thể trả.']);
        }

        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $key = "return_otp_{$borrowId}";

        Cache::put($key, $otp, now()->addMinutes(5));

        Mail::to($borrow->borrower->email)->queue(new \App\Mail\ReturnOtpMail($borrow, $otp));

        return [
            'message' => 'Mã OTP đã được gửi đến email người mượn.',
            'email' => $borrow->borrower->email
        ];
    }

    public function issueBorrow(int $borrowId, ?string $otp = null)
    {
        return $this->runInTransactionWithRetry(function () use ($borrowId, $otp) {
            $borrow = Borrows::with(['details', 'borrower'])->lockForUpdate()->findOrFail($borrowId);

            Gate::authorize('issue', $borrow);

            if ($borrow->status === 'approved') {
                if (empty($otp)) {
                    throw ValidationException::withMessages(['otp' => 'Vui lòng nhập mã OTP xác thực.']);
                }

                $key = "issue_otp_{$borrowId}";
                $cachedOtp = Cache::get($key);

                if (!$cachedOtp || $cachedOtp !== $otp) {
                    throw ValidationException::withMessages(['otp' => 'Mã OTP không chính xác hoặc đã hết hạn.']);
                }

                Cache::forget($key);
            }

            if (!in_array($borrow->status, ['approved', 'borrowed'], true)) {
                throw ValidationException::withMessages(['Phieu khong o trang thai co the xuat.']);
            }
            if (!$borrow->borrower->is_active) {
                throw ValidationException::withMessages([
                    'user' => 'Tài khoản người mượn đã bị tạm ngừng. Không thể xuất thiết bị.'
                ]);
            }

            if ($borrow->expected_return_date && \Carbon\Carbon::parse($borrow->expected_return_date)->isPast()) {
                throw ValidationException::withMessages([
                    'expected_return_date' => 'Ngày dự kiến trả đã quá hạn. Vui lòng cập nhật ngày trả mới.'
                ]);
            }

            foreach ($borrow->details as $detail) {
                $unit = DeviceUnits::lockForUpdate()->findOrFail($detail->device_unit_id);

                if ($detail->status === 'borrowed') {
                    continue;
                }
                if (!in_array($unit->status, ['available', 'reserved'])) {
                    throw ValidationException::withMessages([
                        "devices" => "Thiết bị #{$unit->id} không khả dụng (status: {$unit->status})."
                    ]);
                }

                $unit->status = 'borrowed';
                $unit->save();

                $detail->status = 'borrowed';


                if ($unit->device && $unit->device->category_id == 1) {
                    $unit->status = 'consumed';
                    $unit->save();
                    $unit->delete();

                    $detail->status = 'returned';
                    $detail->returned_at = now();
                    $detail->notes = 'Tiêu hao khi xuất kho';
                }

                $detail->save();
            }

            $borrow->status = 'borrowed';
            $borrow->borrowed_date = $borrow->borrowed_date ?? now();
            $borrow->issued_by_user_id = auth()->id();
            $borrow->save();

            DB::afterCommit(function () use ($borrow) {
                // $borrow->borrower->notify(new BorrowNotification("Phiếu mượn của bạn đã được duyệt."));
            });

            return $borrow->load('details.deviceUnit');
        });
    }

    public function createReturnSlip(
        int $borrowId,
        array $returnItems,
        string $otp,
        ?string $notes = null
    ) {
        return $this->runInTransactionWithRetry(function () use (
            $borrowId,
            $returnItems,
            $otp,
            $notes
        ) {
            $borrow = Borrows::with('details.deviceUnit.device')
                ->lockForUpdate()
                ->findOrFail($borrowId);

            Gate::authorize('return', $borrow);

            $key = "return_otp_{$borrowId}";
            $cachedOtp = Cache::get($key);

            if (!$cachedOtp || $cachedOtp !== $otp) {
                throw ValidationException::withMessages([
                    'otp' => 'Mã OTP không chính xác hoặc đã hết hạn.'
                ]);
            }

            Cache::forget($key);

            if (!in_array($borrow->status, ['approved', 'borrowed', 'overdue'], true)) {
                throw ValidationException::withMessages([
                    'error' => 'Phiếu mượn không ở trạng thái có thể trả.'
                ]);
            }

            $deviceUnitIds = collect($returnItems)->pluck('device_unit_id')->all();
            $details = BorrowsDetail::where('borrow_id', $borrowId)
                ->whereIn('device_unit_id', $deviceUnitIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('device_unit_id');

            foreach ($returnItems as $item) {
                $this->processDeviceReturn($borrow, $item, $details);
            }

            $allReturned = !BorrowsDetail::where('borrow_id', $borrowId)
                ->whereIn('status', ['pending', 'borrowed'])
                ->exists();

            if ($allReturned) {
                $scoreChange = $this->calculateCreditScoreChange($borrow, $returnItems);
                if ($scoreChange !== 0) {
                    $borrower = $borrow->borrower;
                    $oldScore = $borrower->credit_score;
                    $newScore = max(0, min(100, $oldScore + $scoreChange));

                    $borrower->credit_score = $newScore;
                    $borrower->save();

                    $borrower->notify(new \App\Notifications\CreditScoreChanged(
                        $oldScore,
                        $newScore,
                        $scoreChange,
                        $this->getCreditScoreChangeReason($borrow, $returnItems)
                    ));
                }

                $borrow->return_notes = $notes;
                $borrow->status = 'returned';
                $borrow->actual_return_date = now();
                $borrow->returned_by_staff_id = auth()->id();
                $borrow->save();
            }

            return $borrow->fresh()->load('details.deviceUnit.device');
        });
    }

    private function processDeviceReturn(Borrows $borrow, array $item, $details)
    {
        $detail = $details[$item['device_unit_id']] ?? null;

        if (!$detail) {
            throw ValidationException::withMessages([
                'error' => "Không tìm thấy chi tiết mượn cho thiết bị #{$item['device_unit_id']}."
            ]);
        }

        if ($detail->status === 'returned') {
            return;
        }

        if (!in_array($detail->status, ['pending', 'borrowed'])) {
            throw ValidationException::withMessages([
                'error' => "Thiết bị #{$item['device_unit_id']} không ở trạng thái đang mượn."
            ]);
        }

        $unit = DeviceUnits::lockForUpdate()->findOrFail($item['device_unit_id']);

        if ($detail->status === 'returned' && $unit->status === 'available') {
            return;
        }

        $condition = $item['condition_at_return'];

        if (in_array($condition, ['damaged', 'broken'])) {
            $unit->status = 'under_maintenance';

            DeviceMaintenance::create([
                'device_unit_id' => $unit->id,
                'type' => 'damage_report',
                'reported_by' => auth()->id(),
                'priority' => $condition === 'broken' ? 'urgent' : 'high',
                'status' => 'pending',
                'description' => "Thiết bị bị {$this->getConditionLabel($condition)} khi trả từ phiếu mượn #{$borrow->id}",
                'notes' => "Người mượn: {$borrow->borrower->name}\nNgày trả: " . now()->format('d/m/Y H:i'),
            ]);
        } else {
            $unit->status = 'available';
        }

        $unit->save();

        $detail->status = 'returned';
        $detail->returned_at = now();
        $detail->condition_at_return = $condition;
        if (!empty($item['photos'])) {
            $detail->return_photos = json_encode($item['photos']);
        }

        $detail->save();
    }

    public function rejectBorrowRequest(string $id)
    {
        $borrow = Borrows::findOrFail($id);
        $borrow->status = 'rejected';
        $borrow->save();
        return $borrow;
    }

    public function cancelBorrow(string $id)
    {
        return $this->runInTransactionWithRetry(function () use ($id) {
            $borrow = Borrows::with('details')->lockForUpdate()->findOrFail($id);

            if (!in_array($borrow->status, ['pending', 'approved'])) {
                throw ValidationException::withMessages(['Chỉ có thể hủy phiếu khi đang chờ duyệt hoặc đã duyệt.']);
            }

            if ($borrow->status === 'approved') {
                foreach ($borrow->details as $detail) {
                    $unit = DeviceUnits::lockForUpdate()->findOrFail($detail->device_unit_id);
                    if ($unit->status === 'reserved') {
                        $unit->status = 'available';
                        $unit->save();
                    }
                }
            }

            $borrow->status = 'cancelled';
            $borrow->save();

            return $borrow;
        });
    }

    private function getConditionLabel(string $condition): string
    {
        return match ($condition) {
            'damaged' => 'hư hỏng nhẹ',
            'broken' => 'hư hỏng nặng',
            'lost' => 'mất',
            default => 'không rõ'
        };
    }


    private function calculateCreditScoreChange($borrow, $returnItems): int
    {
        $scoreChange = 0;

        $expectedReturnDate = \Carbon\Carbon::parse($borrow->expected_return_date);
        $actualReturnDate = now();
        $daysLate = $actualReturnDate->diffInDays($expectedReturnDate, false);

        if ($daysLate < 0) {
            $daysLate = abs($daysLate);
            if ($daysLate >= 1 && $daysLate <= 3) {
                $scoreChange -= 5;
            } elseif ($daysLate >= 4 && $daysLate <= 7) {
                $scoreChange -= 10;
            } elseif ($daysLate > 7) {
                $scoreChange -= 20;
            }
        }

        foreach ($returnItems as $item) {
            $condition = $item['condition_at_return'];
            switch ($condition) {
                case 'damaged':
                    $scoreChange -= 10;
                    break;
                case 'broken':
                    $scoreChange -= 30;
                    break;
                case 'lost':
                    $scoreChange -= 50;
                    break;
            }
        }

        return $scoreChange;
    }

    /**
     * Get human-readable reason for credit score change
     */
    private function getCreditScoreChangeReason($borrow, $returnItems): string
    {
        $reasons = [];

        // Check late return
        $expectedReturnDate = \Carbon\Carbon::parse($borrow->expected_return_date);
        $actualReturnDate = now();
        $daysLate = $actualReturnDate->diffInDays($expectedReturnDate, false);

        if ($daysLate < 0) {
            $daysLate = abs($daysLate);
            $reasons[] = "Trả muộn {$daysLate} ngày";
        }

        // Check device conditions
        $damagedCount = 0;
        $brokenCount = 0;
        $lostCount = 0;

        foreach ($returnItems as $item) {
            $condition = $item['condition_at_return'];
            switch ($condition) {
                case 'damaged':
                    $damagedCount++;
                    break;
                case 'broken':
                    $brokenCount++;
                    break;
                case 'lost':
                    $lostCount++;
                    break;
            }
        }

        if ($damagedCount > 0) {
            $reasons[] = "{$damagedCount} thiết bị bị hư hỏng nhẹ";
        }
        if ($brokenCount > 0) {
            $reasons[] = "{$brokenCount} thiết bị bị hư hỏng nặng";
        }
        if ($lostCount > 0) {
            $reasons[] = "{$lostCount} thiết bị bị mất";
        }

        return implode(', ', $reasons);
    }
}
