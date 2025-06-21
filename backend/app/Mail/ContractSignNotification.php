<?php

namespace App\Mail;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractSignNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $contract;

    /**
     * Create a new message instance.
     */
    public function __construct(Contract $contract)
    {
        $this->contract = $contract;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Yêu cầu ký hợp đồng - SGHood',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contract-sign-notification',
            with: [
                'userName' => $this->contract->user->name,
                'contractId' => $this->contract->id,
                'roomName' => $this->contract->room ? $this->contract->room->name : 'N/A',
                'startDate' => $this->contract->booking && $this->contract->booking->start_date
                    ? \Carbon\Carbon::parse($this->contract->booking->start_date)->format('d/m/Y')
                    : 'N/A',
                'endDate' => $this->contract->booking && $this->contract->booking->end_date
                    ? \Carbon\Carbon::parse($this->contract->booking->end_date)->format('d/m/Y')
                    : 'N/A',
                'createdAt' => $this->contract->created_at ? $this->contract->created_at->format('d/m/Y H:i') : 'N/A',
                'contract' => $this->contract
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
