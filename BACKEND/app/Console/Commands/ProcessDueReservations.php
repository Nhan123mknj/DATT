<?php

namespace App\Console\Commands;

use App\Jobs\AutoCreateBorrowJob;
use App\Models\DeviceReservation;
use Illuminate\Console\Command;

class ProcessDueReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-due-reservations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        info('Processing due reservations at ' . now());

        $dueReservations = DeviceReservation::where('status', 'pending')
            ->where('reserved_from', '<=', now())
            ->where('status', '!=', 'completed')
            ->get();
        if ($dueReservations->isEmpty()) {
            info('No due reservations found.');
            return Command::SUCCESS;
        }
        foreach ($dueReservations as $reservation) {
            AutoCreateBorrowJob::dispatch($reservation)
                ->onQueue('reservations');
            info("Dispatched AutoCreateBorrowJob for reservation ID: {$reservation->id}");
        }
        return Command::SUCCESS;
    }
}
