<?php

namespace App\Services;

use App\Models\Borrows;
use App\Models\BorrowsDetail;
use App\Models\DeviceUnits;
use App\Models\User;
use App\Notifications\BorrowNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class BorrowService
{
    public function showBorrowingSlip($filters = [], $perPage = 15,)
    {
        // Gate::authorize('viewAny', Borrows::class);
        $query = Borrows::with([
            'details:id,borrow_id,device_unit_id',
            'details.deviceUnit:id,device_id,serial_number',
            'details.deviceUnit.device:id,name'
        ]);

        if (auth()->user()->role === "borrower") {
            $query->where('borrower_id', auth()->id());
        }

        $arrayStatus = ['borrowed', 'returned', 'overdue', 'canceled'];
        if (isset($filters['status'])) {
            $statuses = is_array($filters['status']) ? $filters['status'] : [$filters['status']];
            $validStatuses = array_intersect($statuses, $arrayStatus);
            if (!empty($validStatuses)) {
                $query->whereIn('status', $validStatuses);
            }
        }
        // Gate::authorize('view', $query);
        return $query->paginate($perPage);
    }

    public function createBorrowingSlip(array $data)
    {
        return $this->runInTransactionWithRetry(function () use ($data) {

            $this->checkDevicesAvailable($data['devices']);
            $this->checkUserCancelLimit(auth('api')->user()->id);
            $this->checkUserBorrowLimit(auth('api')->user()->id);

            $borrow = Borrows::create([
                'borrower_id' => auth('api')->user()->id,
                'expected_return_date' => $data['expected_return_date'],
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);
            foreach ($data['devices'] as $device) {
                BorrowsDetail::create([
                    'borrow_id' => $borrow->id,
                    'device_unit_id' => $device['device_unit_id'],
                    'status' => 'pending',
                    'condition_at_borrow' => $device['condition_at_borrow'] ?? 'tốt',
                ]);
            }

            DB::afterCommit(function () use ($borrow) {
                $staffUsers = User::where('role', 'staff')->get();
                $message = auth('api')->user()->name . " đã gửi phiếu mượn";
                foreach ($staffUsers as $staff) {
                    $staff->notify(new BorrowNotification($message, $borrow->id));
                }
            });

            return $borrow->load('details');
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

    private function checkDevicesAvailable(array $devices)
    {
        foreach ($devices as $device) {
            $deviceUnit = DeviceUnits::lockForUpdate()->findOrFail($device['device_unit_id']);
            if ($deviceUnit->status !== 'available') {
                throw ValidationException::withMessages([
                    "Thiết bị #{$deviceUnit->id} không khả dụng."
                ]);
            }
        }
    }

    private function checkUserCancelLimit($id)
    {
        $cancelCount = Borrows::where('borrower_id', $id)
            ->where('status', 'canceled')
            ->whereDate('updated_at', today())
            ->count();

        if ($cancelCount >= 3) {
            throw ValidationException::withMessages([
                "Tai khoan cua ban da huy qua nhieu."
            ]);
        }
    }

    private function checkUserBorrowLimit($id)
    {
        $borrowCount = Borrows::where('borrower_id', $id)
            ->where('status', 'borrowed')
            ->whereDate('updated_at', today())
            ->count();
        if ($borrowCount >= 3) {
            throw ValidationException::withMessages([
                "Tai khoan cua ban da muon qua nhieu."
            ]);
        }
    }

    public function approveBorrowRequest(int $borrowId)
    {
        return $this->runInTransactionWithRetry(function () use ($borrowId) {
            $borrow = Borrows::with('details')->lockForUpdate()->findOrFail($borrowId);

            Gate::authorize('approve', $borrow);

            if (!in_array($borrow->status, ['pending', 'approved'], true)) {
                throw ValidationException::withMessages(['Phieu khong o trang thai co the duyet.']);
            }

            if ($borrow->status !== 'approved') {
                $borrow->status = 'approved';
                $borrow->save();
            }

            DB::afterCommit(function () use ($borrow) {
                // notify approver/borrower if needed
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

    private function runInTransactionWithRetry(callable $callback, int $maxAttempts = 3)
    {
        $attempt = 0;
        beginning:
        $attempt++;
        try {
            return DB::transaction(function () use ($callback) {
                return $callback();
            });
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            $isDeadlock = str_contains($message, 'Deadlock found')
                || str_contains($message, 'SQLSTATE[40001]')
                || str_contains($message, 'SQLSTATE[1213]');
            if ($isDeadlock && $attempt < $maxAttempts) {
                usleep(100000 * $attempt);
                goto beginning;
            }
            throw $e;
        }
    }
}
