<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $oldStatus;
    public $newStatus;

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice, string $oldStatus, string $newStatus)
    {
        $this->invoice = $invoice;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Cập nhật trạng thái hóa đơn - ' . $this->invoice->code,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice-status-updated',
            with: [
                'invoice' => $this->invoice,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
                'user' => $this->invoice->contract->user,
                'room' => $this->invoice->contract->room,
                'motel' => $this->invoice->contract->room->motel
            ]
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
