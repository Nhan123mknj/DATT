<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class BorrowNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $message;
    public $borrowId;
    public $type;

    /**
     * Create a new notification instance.
     */
    public function __construct($message, $borrowId, $type = 'borrow')
    {
        $this->message = $message;
        $this->borrowId = $borrowId;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['broadcast']; // Only broadcast, no database
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'message' => $this->message,
            'borrow_id' => $this->borrowId,
            'type' => $this->type,
            'created_at' => now()->toISOString(),
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'borrow_id' => $this->borrowId,
            'type' => $this->type,
        ];
    }
}
