<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractExpiryNotificationForMeterReading extends Mailable
{
    use Queueable, SerializesModels;

    public $emailData;

    /**
     * Create a new message instance.
     */
    public function __construct(array $emailData)
    {
        $this->emailData = $emailData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🏠 Hợp đồng sắp hết hạn - Cần nhập chỉ số điện nước',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contract-expiry-for-meter-reading',
            with: [
                'contractId' => $this->emailData['contract_id'],
                'roomNumber' => $this->emailData['room_number'],
                'motelName' => $this->emailData['motel_name'],
                'tenantName' => $this->emailData['tenant_name'],
                'endDate' => $this->emailData['end_date'],
                'daysRemaining' => $this->emailData['days_remaining'],
                'adminName' => $this->emailData['admin_name'],
                'actionUrl' => $this->emailData['action_url'],
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