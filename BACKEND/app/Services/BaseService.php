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

    // protected function checkUserBorrowLimit($userId)
    // {
    //     $count = Borrows::where('borrower_id', $userId)
    //         ->where('status', 'borrowed')
    //         ->count();
    //     if ($count >= 3) {
    //         throw ValidationException::withMessages([
    //             "Tài khoản của bạn đã mượn quá nhiều."
    //         ]);
    //     }
    // }
}
