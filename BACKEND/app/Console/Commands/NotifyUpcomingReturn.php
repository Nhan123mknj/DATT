<?php

namespace App\Console\Commands;

use App\Models\Borrows;
use App\Notifications\DeviceReturnReminder;
use Illuminate\Console\Command;

class NotifyUpcomingReturn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notify-upcoming-return';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gửi thông báo cho người dùng khi sắp đến hạn trả thiết bị';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tomorrow = now()->addDay()->toDateString();
        // $today = now()->toDateString();

        $upcomingBorrows = Borrows::with(['borrower', 'details.deviceUnit.device'])
            ->where('status', 'borrowed')
            ->whereDate('expected_return_date', $tomorrow)
            ->get();

        foreach ($upcomingBorrows as $borrow) {
            if ($borrow->borrower) {
                $borrow->borrower->notify(new DeviceReturnReminder($borrow));
                $this->info("Sent reminder to {$borrow->borrower->email} for borrow #{$borrow->id}");
            }
        }
    }
}
