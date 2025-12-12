<?php

namespace App\Services;

use App\Models\Borrows;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BaseService
{
    protected function runInTransactionWithRetry(callable $callback, int $maxAttempts = 3)
    {
        $attempt = 0;
        beginning:
        $attempt++;
        try {
            return DB::transaction($callback);
        } catch (\Throwable $e) {
            $isDeadlock = str_contains($e->getMessage(), 'Deadlock') || in_array($e->getCode(), ['40001', '1213']);
            if ($isDeadlock && $attempt < $maxAttempts) {
                usleep(100000 * $attempt);
                goto beginning;
            }
            throw $e;
        }
    }

    /**
     * Check if user has reached their borrow limit
     * 
     * @param int $userId
     * @throws ValidationException
     */
    protected function checkUserBorrowLimit($userId)
    {
        $user = \App\Models\User::findOrFail($userId);

        // Check if user is suspended
        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'user' => 'Tài khoản của bạn đã bị tạm ngừng. Không thể mượn thiết bị.'
            ]);
        }

        $activeBorrowsCount = Borrows::where('borrower_id', $userId)
            ->whereIn('status', ['borrowed', 'approved', 'pending'])
            ->count();

        $limit = match ($user->role) {
            'teacher' => 5,  
            'student' => 3,  
            default => 1     
        };

        if ($activeBorrowsCount >= $limit) {
            throw ValidationException::withMessages([
                'borrow_limit' => "Bạn đã đạt giới hạn mượn ({$limit} phiếu). Vui lòng trả thiết bị trước khi mượn thêm."
            ]);
        }
    }
}
