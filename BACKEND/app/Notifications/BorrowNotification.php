<?php

namespace App\Notifications;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BorrowNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $message;
    public $borrowId;
    /**
     * Create a new notification instance.
     */
    public function __construct($message, $borrowId)
    {
        $this->message = $message;
        $this->borrowId = $borrowId;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toDatabase($notifiable)
    {
        return [
            'message' => $this->message,
            'borrow_id' => $this->borrowId,
        ];
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
        ];
    }
    public function broadcastOn()
    {
        return new PrivateChannel('borrow.' . $this->borrowId);
    }
}
