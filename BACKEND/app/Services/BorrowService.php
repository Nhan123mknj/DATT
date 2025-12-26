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
            'details.deviceUnit' => function ($query) {
                $query->withTrashed()->with('device');
            },
            'createdBy:id,name,email',
            'issuedBy:id,name,email',
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

                $initialStatus = $autoApprove ? 'approved' : 'pending';



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

                        if ($deviceUnit->status === 'available') {
                            $deviceUnit->status = 'reserved';
                            $deviceUnit->save();
                        }
                    } else {
                        $result = [
                            'status' => 'pending',
                            'deposit_amount' => 0
                        ];
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

                DB::afterCommit(function () use ($borrow, $fromReservation, $deviceUnits) {
                    event(new CreateBorrowingSlip($borrow));

                    $borrower = User::find($borrow->borrower_id);
                    activity('borrow')
                        ->performedOn($borrow)
                        ->causedBy(auth()->user() ?? $borrower)
                        ->withProperties([
                            'borrower_name' => $borrower->name ?? 'N/A',
                            'borrower_email' => $borrower->email ?? 'N/A',
                            'device_count' => $borrow->details->count(),
                            'devices' => $borrow->details->map(fn($d) => [
                                'name' => $deviceUnits[$d->device_unit_id]->device->name ?? 'N/A',
                                'serial' => $deviceUnits[$d->device_unit_id]->serial_number ?? 'N/A',
                            ])->toArray(),
                            'expected_return_date' => $borrow->expected_return_date,
                            'source' => $fromReservation ? 'Từ đặt trước' : 'Mượn nhanh',
                        ])
                        ->log($fromReservation ? 'Tạo phiếu mượn từ đặt trước' : 'Tạo phiếu mượn nhanh');
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
            'borrower:id,name,email,role',
            'details:id,borrow_id,device_unit_id',
            'details.deviceUnit' => function ($query) {
                $query->withTrashed()->select('id', 'device_id', 'serial_number')->with('device:id,name');
            },
            'createdBy:id,name,email',
            'issuedBy:id,name,email',
        ])->findOrFail($id);

        Gate::authorize('view', $result);

        return $result;
    }


    // public function approveBorrowRequest(int $borrowId)
    // {
    //     return $this->runInTransactionWithRetry(function () use ($borrowId) {
    //         $borrow = Borrows::with('details')->lockForUpdate()->findOrFail($borrowId);

    //         Gate::authorize('approve', $borrow);

    //         if ($borrow->status !== 'pending') {
    //             throw ValidationException::withMessages(['Phiếu không ở trạng thái chờ duyệt.']);
    //         }

    //         $this->checkUserBorrowLimit($borrow->borrower_id);

    //         $borrow->status = 'approved';
    //         $borrow->save();

    //         activity('borrow')
    //             ->performedOn($borrow)
    //             ->causedBy(auth()->user())
    //             ->withProperties([
    //                 'borrower_name' => $borrow->borrower->name ?? 'N/A',
    //                 'borrower_email' => $borrow->borrower->email ?? 'N/A',
    //                 'device_count' => $borrow->details->count(),
    //             ])
    //             ->log('Duyệt phiếu mượn');

    //         // DB::afterCommit(function () use ($borrow) {

    //         // });

    //         return $borrow->load('details');
    //     });
    // }

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

    public function issueBorrow(int $borrowId, ?string $otp = null)
    {
        return $this->runInTransactionWithRetry(function () use ($borrowId, $otp) {
            $borrow = Borrows::with(['details', 'borrower'])->lockForUpdate()->findOrFail($borrowId);

            Gate::authorize('issue', $borrow);

            if ($borrow->status !== 'approved') {
                throw ValidationException::withMessages([
                    'status' => 'Chỉ có thể xuất phiếu đã được duyệt.'
                ]);
            }

            if (empty($otp)) {
                throw ValidationException::withMessages(['otp' => 'Vui lòng nhập mã OTP xác thực.']);
            }

            $key = "issue_otp_{$borrowId}";
            $cachedOtp = Cache::get($key);

            if (!$cachedOtp || $cachedOtp !== $otp) {
                throw ValidationException::withMessages(['otp' => 'Mã OTP không chính xác hoặc đã hết hạn.']);
            }

            Cache::forget($key);

            if (!$borrow->borrower->is_active) {
                throw ValidationException::withMessages([
                    'user' => 'Tài khoản người mượn đã bị tạm ngừng. Không thể xuất thiết bị.'
                ]);
            }

            if ($borrow->borrowed_date && \Carbon\Carbon::parse($borrow->borrowed_date)->isFuture()) {
                throw ValidationException::withMessages([
                    'borrowed_date' => 'Chưa đến ngày mượn thiết bị. Vui lòng đợi đến ngày ' . \Carbon\Carbon::parse($borrow->borrowed_date)->format('d/m/Y')
                ]);
            }

            foreach ($borrow->details as $detail) {
                $unit = DeviceUnits::lockForUpdate()->findOrFail($detail->device_unit_id);

                if (!in_array($unit->status, ['available', 'reserved'])) {
                    throw ValidationException::withMessages([
                        "devices" => "Thiết bị {$unit->device->name} (SN: {$unit->serial_number}) không khả dụng (status: {$unit->status})."
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

            $borrow->status = 'completed';
            $borrow->borrowed_date = $borrow->borrowed_date ?? now();
            $borrow->issued_at = now();
            $borrow->issued_by_user_id = auth()->id();
            $borrow->save();

            activity('borrow')
                ->performedOn($borrow)
                ->causedBy(auth()->user())
                ->withProperties([
                    'borrower_name' => $borrow->borrower->name ?? 'N/A',
                    'borrower_email' => $borrow->borrower->email ?? 'N/A',
                    'device_count' => $borrow->details->count(),
                    'devices' => $borrow->details->map(fn($d) => [
                        'name' => $d->deviceUnit->device->name ?? 'N/A',
                        'serial' => $d->deviceUnit->serial_number ?? 'N/A',
                        'status' => $d->status,
                    ])->toArray(),
                    'expected_return_date' => $borrow->expected_return_date,
                ])
                ->log('Xuất thiết bị cho người mượn');

            DB::afterCommit(function () use ($borrow) {
                // $borrow->borrower->notify(new BorrowNotification("Phiếu mượn của bạn đã được duyệt."));
            });

            return $borrow->load(['details.deviceUnit' => function ($q) {
                $q->withTrashed();
            }]);
        });
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

            Gate::authorize('cancel', $borrow);

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

            activity('borrow')
                ->performedOn($borrow)
                ->causedBy(auth()->user())
                ->withProperties([
                    'borrower_name' => $borrow->borrower->name ?? 'N/A',
                    'device_count' => $borrow->details->count(),
                ])
                ->log('Hủy phiếu mượn');

            return $borrow;
        });
    }
}
