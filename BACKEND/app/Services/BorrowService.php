<?php

namespace App\Services;

use App\Events\CreateBorrowingSlip;

use App\Models\Borrows;
use App\Models\BorrowsDetail;
use App\Models\DeviceUnits;
use App\Models\User;
use App\Services\BaseService;

use App\Services\Borrow\BorrowStrategyFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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

                $initialStatus = $autoApprove ? 'borrowed' : 'pending';

                $borrow = Borrows::create([
                    'borrower_id' => $userId,
                    'borrowed_date' => now(),
                    'expected_return_date' => $expectedReturn,
                    'status' => $initialStatus,
                    'notes' => $data['notes'] ?? null,
                    'commitment_file' => $data['commitment_file'] ?? null,
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
                        $result['status'] = 'borrowed';
                        $deviceUnit->status = 'borrowed';
                        $deviceUnit->save();
                    }

                    BorrowsDetail::create([
                        'borrow_id' => $borrow->id,
                        'device_unit_id' => $deviceUnitId,
                        'status' => $result['status'] ?? 'pending',
                        'condition_at_borrow' => $deviceData['condition_at_borrow'] ?? 'tốt',
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
            'details.deviceUnit.device:id,name'
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

    public function issueBorrow(int $borrowId)
    {
        return $this->runInTransactionWithRetry(function () use ($borrowId) {
            $borrow = Borrows::with(['details', 'borrower'])->lockForUpdate()->findOrFail($borrowId);

            Gate::authorize('issue', $borrow);

            if (!in_array($borrow->status, ['approved', 'borrowed'], true)) {
                throw ValidationException::withMessages(['Phieu khong o trang thai co the xuat.']);
            }

            // Re-check if user is still active before issuing
            if (!$borrow->borrower->is_active) {
                throw ValidationException::withMessages([
                    'user' => 'Tài khoản người mượn đã bị tạm ngừng. Không thể xuất thiết bị.'
                ]);
            }

            // Validate expected_return_date is still in the future
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
                $detail->save();
            }

            $borrow->status = 'borrowed';
            $borrow->borrowed_date = $borrow->borrowed_date ?? now();
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
        array $signatures = [],
        ?string $notes = null
    ) {
        return $this->runInTransactionWithRetry(function () use (
            $borrowId,
            $returnItems,
            $signatures,
            $notes
        ) {
            $borrow = Borrows::with('details.deviceUnit.device')
                ->lockForUpdate()
                ->findOrFail($borrowId);

            Gate::authorize('return', $borrow);

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
                \Log::info('All items returned. Processing signatures...');
                if (!empty($signatures['staff'])) {
                    $path = $this->saveSignatureImage($signatures['staff'], 'staff_signatures');
                    \Log::info('Staff signature saved at: ' . $path);
                    $borrow->staff_signature = $path;
                }
                if (!empty($signatures['borrower'])) {
                    $path = $this->saveSignatureImage($signatures['borrower'], 'borrower_signatures');
                    \Log::info('Borrower signature saved at: ' . $path);
                    $borrow->borrower_signature = $path;
                }

                $borrow->return_notes = $notes;
                $borrow->status = 'returned';
                $borrow->actual_return_date = now();
                $borrow->returned_by_staff_id = auth()->id();
                $borrow->save();

                DB::afterCommit(function () use ($borrow) {
                    try {
                        \Log::info('Generating PDF for borrow ' . $borrow->id);
                        $pdfService = app(\App\Services\ReturnSlipPDFService::class);
                        $path = $pdfService->generate($borrow);
                        \Log::info('PDF generated at: ' . $path);
                    } catch (\Exception $e) {
                        \Log::error('PDF generation failed: ' . $e->getMessage());
                        \Log::error($e->getTraceAsString());
                    }
                });
            }

            return $borrow->fresh()->load('details.deviceUnit.device');
        });
    }

    /**
     * Process individual device return
     */
    private function processDeviceReturn(Borrows $borrow, array $item, $details)
    {
        $detail = $details[$item['device_unit_id']] ?? null;

        if (!$detail || !in_array($detail->status, ['pending', 'borrowed'])) {
            throw ValidationException::withMessages([
                'error' => "Thiết bị #{$item['device_unit_id']} không ở trạng thái đang mượn."
            ]);
        }

        $unit = DeviceUnits::lockForUpdate()->findOrFail($item['device_unit_id']);

        // Skip if already returned
        if ($detail->status === 'returned' && $unit->status === 'available') {
            return;
        }

        // Check condition & set appropriate status
        $condition = $item['condition_at_return'];

        if (in_array($condition, ['damaged', 'broken'])) {
            $unit->status = 'under_maintenance';

            // Calculate damage fee
            $detail->damage_fee = $this->calculateDamageFee($unit, $condition);
        } else {
            $unit->status = 'available';
        }

        $unit->save();

        // Update detail
        $detail->status = 'returned';
        $detail->returned_at = now();
        $detail->condition_at_return = $condition;

        // Save photos if provided
        if (!empty($item['photos'])) {
            $detail->return_photos = json_encode($item['photos']);
        }

        $detail->save();
    }

    /**
     * Calculate damage fee based on condition
     */
    private function calculateDamageFee(DeviceUnits $unit, string $condition): float
    {
        $baseCost = $unit->device->cost ?? 0;

        return match ($condition) {
            'damaged' => $baseCost * 0.2,  // 20% for minor damage
            'broken' => $baseCost * 0.5,   // 50% for major damage
            default => 0
        };
    }

    public function rejectBorrowRequest(string $id)
    {
        $borrow = Borrows::findOrFail($id);
        $borrow->status = 'rejected';
        $borrow->save();
        return $borrow;
    }

    private function saveSignatureImage($base64String, $folder)
    {
        if (empty($base64String)) return null;

        $type = 'png'; // Default type

        // Check if it has data URI prefix
        if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $matches)) {
            $type = strtolower($matches[1]);
            $base64String = substr($base64String, strpos($base64String, ',') + 1);
        }

        if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
            throw new \Exception('Invalid image type');
        }

        $decoded = base64_decode($base64String);

        if ($decoded === false) {
            \Log::error('Base64 decode failed for signature');
            return null;
        }

        $fileName = uniqid() . '.' . $type;
        $path = "signatures/{$folder}/{$fileName}";

        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $decoded);

        return $path;
    }
}
