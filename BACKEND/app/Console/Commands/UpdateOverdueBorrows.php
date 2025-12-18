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
    protected $description = 'Update status of overdue borrows to overdue';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->toDateString();

        $overdueBorrows = Borrows::with('borrower', 'details.deviceUnit.device')
            ->where('status', 'borrowed')
            ->where('expected_return_date', '<', $today)
            ->get();

        $count = 0;
        foreach ($overdueBorrows as $borrow) {
            $borrow->status = 'overdue';
            $borrow->save();

            if ($borrow->borrower) {
                $borrow->borrower->notify(new \App\Notifications\DeviceReturnReminder($borrow));
                $this->info("Updated borrow #{$borrow->id} to overdue and sent notification to {$borrow->borrower->email}");
            }
            $count++;
        }

        if ($count > 0) {
            $this->info("Processed {$count} overdue borrows.");
            \Log::info("Processed {$count} overdue borrows.");
        } else {
            $this->info("No overdue borrows found.");
        }
    }
}
