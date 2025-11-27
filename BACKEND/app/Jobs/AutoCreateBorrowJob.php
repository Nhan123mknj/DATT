<?php

namespace App\Jobs;

use App\Models\DeviceReservation;
use App\Services\ReservationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AutoCreateBorrowJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    public $reservationId;
    /**
     * Create a new job instance.
     */
    public function __construct(DeviceReservation $reservation)
    {
        $this->reservationId = $reservation instanceof DeviceReservation
            ? $reservation->id
            : $reservation;
    }

    /**
     * Execute the job.
     */
    public function handle(ReservationService $reservationService): void
    {
        try {
            $reservationService->autoCreateBorrowFromReservation($this->reservationId);
        } catch (\Exception $e) {
            \Log::error("Failed to auto create borrow from reservation ID {$this->reservationId}: " . $e->getMessage());
            throw $e;
        }
    }
    public $tries = 3;
    public $backoff = [10, 30, 60];
}
