<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GeneralNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $message;
    public $recipientName;

    /**
     * Create a new message instance.
     */
    public function __construct(string $subject, string $message, string $recipientName = 'Admin')
    {
        $this->subject = $subject;
        $this->message = $message;
        $this->recipientName = $recipientName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.general-notification',
            with: [
                'subject' => $this->subject,
                'message' => $this->message,
                'recipientName' => $this->recipientName,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}