<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeviceReturnReminder extends Notification implements ShouldQueue
{
    use Queueable;
    protected $borrow;
    /**
     * Create a new notification instance.
     */
    public function __construct($borrow)
    {
        $this->borrow = $borrow;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $borrowId = $this->borrow->id;
        $dueDate = \Carbon\Carbon::parse($this->borrow->expected_return_date)->format('d/m/Y');
        $isOverdue = \Carbon\Carbon::parse($this->borrow->expected_return_date)->isPast();

        $subject = $isOverdue
            ? "[QUÁ HẠN] Nhắc nhở trả thiết bị - Phiếu #{$borrowId}"
            : "[NHẮC NHỞ] Sắp đến hạn trả thiết bị - Phiếu #{$borrowId}";

        $line1 = $isOverdue
            ? "Phiếu mượn #{$borrowId} của bạn đã QUÁ HẠN trả (Hạn: {$dueDate})."
            : "Phiếu mượn #{$borrowId} của bạn sắp đến hạn trả vào ngày {$dueDate}.";

        return (new MailMessage)
            ->subject($subject)
            ->greeting("Xin chào {$notifiable->name},")
            ->line($line1)
            ->line("Vui lòng mang thiết bị đến phòng thiết bị để trả đúng hạn.")
            ->line("Danh sách thiết bị:")
            ->line($this->borrow->details->map(fn($d) => "- " . ($d->deviceUnit->device->name ?? 'N/A'))->join("\n"))
            ->action('Xem chi tiết', env('FRONTEND_URL') . "/borrower/borrows?id={$borrowId}")
            ->line('Cảm ơn bạn đã sử dụng dịch vụ!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'borrow_id' => $this->borrow->id,
            'message' => "Phiếu mượn #{$this->borrow->id} cần được trả trước " . \Carbon\Carbon::parse($this->borrow->expected_return_date)->format('d/m/Y'),
            'type' => 'reminder'
        ];
    }
}
