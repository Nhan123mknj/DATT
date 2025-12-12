<?php

namespace App\Console\Commands;

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
        //
    }
}
