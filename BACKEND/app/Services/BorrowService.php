<?php

namespace App\Services;

use App\Models\Borrows;
use App\Models\BorrowsDetail;
use App\Models\DeviceUnits;
use App\Models\User;
use App\Services\BaseSevice;
use App\Notifications\BorrowNotification;
use App\Services\Borrow\BorrowStrategyFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class BorrowService extends BaseService
{
    public function showBorrowingSlip($filters = [], $perPage = 15,)
    {
        $query = Borrows::with([
            'details.deviceUnit.device',
        ]);

        if (auth()->user()->role === "borrower") {
            $query->where('borrower_id', auth()->id());
        }

        if (isset($filters['status'])) {
            $query->whereIn('status', (array)$filters['status']);
        }
        // Gate::authorize('view', $query);
        return $query->paginate($perPage);
    }

    public function createBorrowingSlip(array $data)
    {
        return $this->runInTransactionWithRetry(function () use ($data) {
            try {
                // Lấy user_id từ data (cho staff), hoặc từ auth (cho borrower)
                $userId = $data['borrower_id'] ?? auth('api')->user()->id;
                $expectedReturn = $data['expected_return_date'];
                $devices = collect($data['devices']);

                if ($devices->isEmpty()) {
                    throw ValidationException::withMessages([
                        'devices' => 'Phải chọn ít nhất một thiết bị.'
                    ]);
                }

                // Validate devices trước
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
                }

                // $this->checkUserBorrowLimit($userId);

                $hasExpensive = $deviceUnits->contains(function ($unit) {
                    return $unit->device && $unit->device->category_id == 2;
                });
                if ($hasExpensive && empty($data['commitment_file'])) {
                    throw ValidationException::withMessages([
                        'commitment_file' => 'Thiết bị đắt tiền yêu cầu nộp file cam kết trách nhiệm.'
                    ]);
                }

                $borrow = Borrows::create([
                    'borrower_id' => $userId,
                    'expected_return_date' => $expectedReturn,
                    'status' => 'pending',
                    'notes' => $data['notes'] ?? null,
                    // 'borrowed_date' => today(),
                    'commitment_file' => $data['commitment_file'] ?? null,
                ]);

                $devices->each(function ($deviceData) use ($borrow, $deviceUnits, $expectedReturn, $userId) {
                    $deviceUnitId = $deviceData['device_unit_id'];
                    $deviceUnit = $deviceUnits[$deviceUnitId];

                    $duration = $expectedReturn
                        ? now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($expectedReturn)->startOfDay()) + 1
                        : 1;

                    $strategy = BorrowStrategyFactory::createStrategy($deviceUnit->device);

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

                    BorrowsDetail::create([
                        'borrow_id' => $borrow->id,
                        'device_unit_id' => $deviceUnitId,
                        'status' => $result['status'] ?? 'pending',
                        'condition_at_borrow' => $deviceData['condition_at_borrow'] ?? 'tốt',
                        'deposit_amount' => $result['deposit_amount'] ?? 0,
                    ]);
                });

                DB::afterCommit(function () use ($borrow) {
                    $staffUsers = User::where('role', 'staff')->get();
                    $message = "Phiếu mượn được tạo";
                    foreach ($staffUsers as $staff) {
                        $staff->notify(new BorrowNotification($message, $borrow->id));
                    }
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

            $borrow->status = 'approved';
            $borrow->save();

            DB::afterCommit(function () use ($borrow) {
                // $borrow->borrower->notify(new BorrowNotification("Phiếu mượn của bạn đã được duyệt."));
            });

            return $borrow->load('details');
        });
    }

    public function issueBorrow(int $borrowId)
    {
        return $this->runInTransactionWithRetry(function () use ($borrowId) {
            $borrow = Borrows::with(['details'])->lockForUpdate()->findOrFail($borrowId);

            Gate::authorize('issue', $borrow);

            if (!in_array($borrow->status, ['approved', 'borrowed'], true)) {
                throw ValidationException::withMessages(['Phieu khong o trang thai co the xuat.']);
            }

            foreach ($borrow->details as $detail) {
                $unit = DeviceUnits::lockForUpdate()->findOrFail($detail->device_unit_id);

                if ($detail->status === 'borrowed') {
                    continue;
                }

                if ($unit->status !== 'available') {
                    throw ValidationException::withMessages(["Thiet bi #{$unit->id} khong kha dung."]);
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
                // notify
            });

            return $borrow->load('details.deviceUnit');
        });
    }

    public function createReturnSlip(int $borrowId, array $returnItems)
    {
        // $returnItems: [['device_unit_id' => 1, 'condition_at_return' => 'tot']]
        return $this->runInTransactionWithRetry(function () use ($borrowId, $returnItems) {
            $borrow = Borrows::with('details')->lockForUpdate()->findOrFail($borrowId);

            Gate::authorize('return', $borrow);

            if (!in_array($borrow->status, ['borrowed', 'overdue'], true)) {
                throw ValidationException::withMessages(['Phieu khong o trang thai co the tra.']);
            }

            $deviceUnitIds = collect($returnItems)->pluck('device_unit_id')->all();
            $details = BorrowsDetail::where('borrow_id', $borrowId)
                ->whereIn('device_unit_id', $deviceUnitIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('device_unit_id');

            foreach ($returnItems as $item) {
                $detail = $details[$item['device_unit_id']] ?? null;
                if (!$detail || $detail->status !== 'borrowed') {
                    throw ValidationException::withMessages(["Thiet bi #{$item['device_unit_id']} khong o trang thai dang muon."]);
                }

                $unit = DeviceUnits::lockForUpdate()->findOrFail($item['device_unit_id']);

                if ($detail->status === 'returned' && $unit->status === 'available') {
                    continue;
                }

                $unit->status = 'available';
                $unit->save();

                $detail->status = 'returned';
                $detail->returned_at = now();
                if (!empty($item['condition_at_return'])) {
                    $detail->condition_at_return = $item['condition_at_return'];
                }
                $detail->save();
            }

            $allReturned = !BorrowsDetail::where('borrow_id', $borrowId)
                ->whereIn('status', ['pending', 'borrowed'])
                ->exists();

            if ($allReturned) {
                $borrow->status = 'returned';
                $borrow->actual_return_date = now();
                $borrow->save();
            }

            DB::afterCommit(function () use ($borrow) {
                // notify
            });

            return $borrow->load('details.deviceUnit');
        });
    }
}
