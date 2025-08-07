<?php

namespace App\Mail;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ContractEarlyTerminationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $contract;
    public $terminationReason;

    public function __construct(Contract $contract, $terminationReason = null)
    {
        $this->contract = $contract;
        $this->terminationReason = $terminationReason;

        Log::info('ContractEarlyTerminationNotification constructed', [
            'contract_id' => $contract->id,
            'termination_reason' => $terminationReason,
            'termination_reason_length' => strlen($terminationReason ?? ''),
            'termination_reason_type' => gettype($terminationReason)
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thông báo kết thúc hợp đồng sớm - SGHood',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contract-early-termination-notification',
            with: [
                'userName' => $this->contract->user->name,
                'contractId' => $this->contract->id,
                'roomName' => $this->contract->room ? $this->contract->room->name : 'N/A',
                'startDate' => $this->contract->booking && $this->contract->booking->start_date
                    ? \Carbon\Carbon::parse($this->contract->booking->start_date)->format('d/m/Y')
                    : 'N/A',
                'endDate' => $this->contract->end_date
                    ? \Carbon\Carbon::parse($this->contract->end_date)->format('d/m/Y')
                    : 'N/A',
                'terminationDate' => now()->format('d/m/Y'),
                'terminationReason' => $this->terminationReason,
                'rentalPrice' => $this->contract->rental_price
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
