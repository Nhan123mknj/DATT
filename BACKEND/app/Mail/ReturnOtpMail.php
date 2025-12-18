<?php

namespace App\Mail;

use App\Models\Borrows;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReturnOtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $borrow;
    public $otp;

    /**
     * Create a new message instance.
     */
    public function __construct(Borrows $borrow, string $otp)
    {
        $this->borrow = $borrow;
        $this->otp = $otp;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Mã xác thực trả thiết bị - ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.return_otp',
            with: [
                'borrowerName' => $this->borrow->borrower->name,
                'otp' => $this->otp,
                'borrowId' => $this->borrow->id,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
