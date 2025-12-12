<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationRequestReject
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

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
        $this->message = "Yêu cầu đặt trước #{$reservation->id} của bạn đã bị từ chối.";
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->reservation->user_id),
        ];
    }

    public function broadcastAs()
    {
        return 'reservation.request.reject';
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'reservation_id' => $this->reservation->id,
            'status' => $this->reservation->status,
            'user_id' => $this->reservation->user_id,
            'user_name' => $this->reservation->user->name ?? 'Unknown',
            'created_at' => $this->reservation->created_at->toISOString(),
        ];
    }
}
