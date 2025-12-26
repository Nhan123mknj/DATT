<?php

namespace App\Console\Commands;

use App\Models\DeviceReservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CancelPendingReservations extends Command
{
    protected $signature = 'reservations:cancel-pending {--hours=24}';
    protected $description = 'Tự động hủy phiếu khi chưa được duyệt';

    public function handle()
    {
        $hours = $this->option('hours');
        $cutoffTime = now()->subHours($hours);

        $this->info("Tìm phiếu đặt nào chưa được duyệt trong {$hours} giờ (before {$cutoffTime})...");

        $oldReservations = DeviceReservation::where('status', 'pending')
            ->where('created_at', '<', $cutoffTime)
            ->with('details.deviceUnit', 'user')
            ->get();

        if ($oldReservations->isEmpty()) {
            $this->info('Không tìm thấy phiếu đặt nào chưa được duyệt trong {$hours} giờ.');
            return 0;
        }

        $this->info("{$oldReservations->count()} phiếu đặt chưa được duyệt trong {$hours} giờ.");
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

                    if ($reservation->user) {
                        broadcast(new \App\Events\ReservationRequestCancel($reservation));
                    }

                    $this->line("✓ Hủy phiếu #{$reservation->id} - {$reservation->user->name}");
                    $canceledCount++;
                } catch (\Exception $e) {
                    $this->error("✗ Hủy phiếu #{$reservation->id}: {$e->getMessage()}");
                }
            }
        });

        $this->info("\n✅ Hoàn thành! Hủy {$canceledCount} phiếu.");
        return 0;
    }
}
