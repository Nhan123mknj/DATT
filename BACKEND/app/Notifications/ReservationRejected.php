<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReservationRejected extends Notification implements ShouldQueue
{
    use Queueable;

    protected $reservation;
    protected $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct($reservation, $reason = null)
    {
        $this->reservation = $reservation;
        $this->reason = $reason;
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
        $message = "Yêu cầu đặt trước #{$this->reservation->id} của bạn đã bị từ chối";
        if ($this->reason) {
            $message .= ". Lý do: {$this->reason}";
        }

        return [
            'message' => $message,
            'reservation_id' => $this->reservation->id,
            'status' => $this->reservation->status,
            'reason' => $this->reason,
            'rejected_at' => now()->toISOString(),
        ];
    }
}
