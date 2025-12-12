<?php

namespace App\Events;

use App\Models\Borrows;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateBorrowingSlip
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $borrow;
    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Borrows $borrow)
    {
        // Load borrower relationship if not already loaded
        if (!$borrow->relationLoaded('borrower')) {
            $borrow->load('borrower');
        }

        $this->borrow = $borrow;
        $this->message = "Yêu cầu mượn thiết bị #{$borrow->id} của bạn đã được tạo.";
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->borrow->borrower_id);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs()
    {
        return 'borrow.created';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        return [
            'message' => $this->message,
            'borrow_id' => $this->borrow->id,
            'status' => $this->borrow->status,
            'borrower_id' => $this->borrow->borrower_id,
            'borrower_name' => $this->borrow->borrower->name ?? 'Unknown',
            'created_at' => $this->borrow->created_at->toISOString(),
        ];
    }
}
