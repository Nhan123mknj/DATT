<?php

namespace App\Console\Commands;

use App\Jobs\AutoCreateBorrowJob;
use App\Models\DeviceReservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessDueReservations extends Command
{
    protected $signature = 'app:process-due-reservations';

    protected $description = 'Fallback: Tạo phiếu mượn từ đặt trước đã miss';

    public function handle()
    {
        $this->info('🔄 Xử lý các đặt trước đã đến hạn lúc ' . now());

        $missedReservations = DeviceReservation::where('status', 'approved')
            ->where('reserved_from', '<=', now())
            ->where('status', '!=', 'completed')
            ->get();

        if ($missedReservations->isEmpty()) {
            $this->info('✅ Không có đặt trước bị miss.');
            return Command::SUCCESS;
        }

        $count = 0;
        foreach ($missedReservations as $reservation) {
            try {
                if (!$this->borrowExists($reservation->id)) {
                    AutoCreateBorrowJob::dispatch($reservation)
                        ->onQueue('reservations');

                    Log::info("🔄 Fallback: Dispatch AutoCreateBorrowJob cho reservation #{$reservation->id}");
                    $this->line("✅ Xử lý lại đặt trước #{$reservation->id}");
                    $count++;
                }
            } catch (\Exception $e) {
                Log::error("❌ Lỗi xử lý reservation #{$reservation->id}: " . $e->getMessage());
                $this->error("❌ Lỗi: " . $e->getMessage());
            }
        }

        $this->info("✅ Xử lý xong {$count} đặt trước bị miss.");
        return Command::SUCCESS;
    }

    private function borrowExists(int $reservationId): bool
    {
        return \App\Models\Borrows::where('notes', 'like', "%đặt trước #{$reservationId}%")
            ->exists();
    }
}
