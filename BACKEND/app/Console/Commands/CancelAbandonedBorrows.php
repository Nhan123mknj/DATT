<?php

namespace App\Console\Commands;

use App\Models\Borrows;
use App\Services\BorrowService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CancelAbandonedBorrows extends Command
{
    protected $signature = 'borrows:cancel-abandoned {--days=3 : Số ngày để hủy}';

    protected $description = 'Hủy phiếu đã được duyệt khi chưa được lấy sau một số ngày nhất định';

    public function handle(BorrowService $borrowService)
    {
        $days = $this->option('days');
        $cutoffDate = now()->subDays($days);

        $this->info("Checking for approved borrows created before {$cutoffDate}...");

        $abandonedBorrows = Borrows::where('status', 'approved')
            ->where('created_at', '<', $cutoffDate)
            ->get();

        if ($abandonedBorrows->isEmpty()) {
            $this->info('No abandoned borrows found.');
            return;
        }

        $count = 0;
        foreach ($abandonedBorrows as $borrow) {
            try {
                $borrowService->cancelBorrow($borrow->id);
                $this->info("Cancelled borrow #{$borrow->id}");
                Log::info("Auto-cancelled abandoned borrow #{$borrow->id}");
                $count++;
            } catch (\Exception $e) {
                $this->error("Failed to cancel borrow #{$borrow->id}: " . $e->getMessage());
                Log::error("Failed to auto-cancel borrow #{$borrow->id}: " . $e->getMessage());
            }
        }

        $this->info("Completed. Cancelled {$count} abandoned borrows.");
    }
}
