<?php

namespace App\Console\Commands;

use App\Models\Borrows;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateOverdueBorrows extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'borrows:update-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật phiếu mượn quá hạn';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->toDateString();


        $overdueBorrows = Borrows::with('borrower', 'details.deviceUnit.device')
            ->where('status', 'completed')
            ->where('expected_return_date', '<', $today)
            ->whereDoesntHave('returnSlip') 
            ->get();

        $count = 0;
        foreach ($overdueBorrows as $borrow) {
            $borrow->status = 'overdue';
            $borrow->save();

            if ($borrow->borrower) {
                $borrow->borrower->notify(new \App\Notifications\DeviceReturnReminder($borrow));
                $this->info("Cập nhật phiếu mượn #{$borrow->id} thành quá hạn và gửi thông báo đến {$borrow->borrower->email}");
            }
            $count++;
        }

        if ($count > 0) {
            $this->info("Hoàn thành! Cập nhật {$count} phiếu mượn quá hạn.");
            \Log::info("Hoàn thành! Cập nhật {$count} phiếu mượn quá hạn.");
        } else {
            $this->info("Không tìm thấy phiếu mượn quá hạn.");
        }
    }
}
