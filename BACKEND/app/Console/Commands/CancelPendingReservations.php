<?php

namespace App\Console\Commands;

use App\Models\DeviceReservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CancelPendingReservations extends Command
{
    protected $signature = 'reservations:cancel-pending {--hours=24}';
    protected $description = 'Auto-cancel pending reservations older than specified hours';

    public function handle()
    {
        $hours = $this->option('hours');
        $cutoffTime = now()->subHours($hours);

        $this->info("Checking reservations older than {$hours} hours (before {$cutoffTime})...");

        $oldReservations = DeviceReservation::where('status', 'pending')
            ->where('created_at', '<', $cutoffTime)
            ->with('details.deviceUnit', 'user')
            ->get();

        if ($oldReservations->isEmpty()) {
            $this->info('No old pending reservations found.');
            return 0;
        }

        $this->info("Found {$oldReservations->count()} reservations to cancel.");
        $canceledCount = 0;

        DB::transaction(function () use ($oldReservations, &$canceledCount) {
            foreach ($oldReservations as $reservation) {
                try {
                    foreach ($reservation->details as $detail) {
                        if ($detail->deviceUnit) {
                            $detail->deviceUnit->update(['status' => 'available']);
                        }
                    }

                    $reservation->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                    ]);

                    $reservation->details()->update(['status' => 'cancelled']);

                    // Notify user via Pusher
                    if ($reservation->user) {
                        broadcast(new \App\Events\ReservationRequestCancel($reservation));
                    }

                    $this->line("✓ Canceled #{$reservation->id} - {$reservation->user->name}");
                    $canceledCount++;
                } catch (\Exception $e) {
                    $this->error("✗ Failed #{$reservation->id}: {$e->getMessage()}");
                }
            }
        });

        $this->info("\n✅ Completed! Canceled {$canceledCount} reservations.");
        return 0;
    }
}
