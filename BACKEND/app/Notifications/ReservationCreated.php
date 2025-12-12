<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReservationCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $reservation;

    /**
     * Create a new notification instance.
     */
    public function __construct($reservation)
    {
        // Load user relationship if not already loaded
        if (!$reservation->relationLoaded('user')) {
            $reservation->load('user');
        }

        $this->reservation = $reservation;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'message' => "{$this->reservation->user->name} đã tạo yêu cầu đặt trước #{$this->reservation->id}",
            'reservation_id' => $this->reservation->id,
            'status' => $this->reservation->status,
            'user_id' => $this->reservation->user_id,
            'user_name' => $this->reservation->user->name,
            'created_at' => $this->reservation->created_at->toISOString(),
        ];
    }
}
