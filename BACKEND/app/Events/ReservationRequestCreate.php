<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationRequestCreate
{
    use Dispatchable, SerializesModels;

    public $reservation;
    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct($reservation)
    {
        // Load user relationship if not already loaded
        if (!$reservation->relationLoaded('user')) {
            $reservation->load('user');
        }

        $this->reservation = $reservation;
        $this->message = "{$reservation->user->name} đã tạo yêu cầu đặt trước phiếu mượn thiết bị #{$reservation->id}.";
    }
}
