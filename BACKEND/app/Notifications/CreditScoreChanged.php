<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CreditScoreChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public $oldScore;
    public $newScore;
    public $scoreChange;
    public $reason;

    /**
     * Create a new notification instance.
     */
    public function __construct(int $oldScore, int $newScore, int $scoreChange, string $reason)
    {
        $this->oldScore = $oldScore;
        $this->newScore = $newScore;
        $this->scoreChange = $scoreChange;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->scoreChange > 0
                ? 'Điểm tín nhiệm tăng'
                : 'Điểm tín nhiệm giảm',
            'message' => $this->getMessage(),
            'old_score' => $this->oldScore,
            'new_score' => $this->newScore,
            'score_change' => $this->scoreChange,
            'reason' => $this->reason,
            'type' => 'credit_score_changed',
        ];
    }

    private function getMessage(): string
    {
        if ($this->scoreChange > 0) {
            return "Điểm tín nhiệm của bạn tăng {$this->scoreChange} điểm (từ {$this->oldScore} lên {$this->newScore}). Lý do: {$this->reason}";
        } else {
            $change = abs($this->scoreChange);
            return "Điểm tín nhiệm của bạn giảm {$change} điểm (từ {$this->oldScore} xuống {$this->newScore}). Lý do: {$this->reason}";
        }
    }
}
