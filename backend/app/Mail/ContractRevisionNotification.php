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

class ContractRevisionNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $contract;
    public $revisionReason;

    public function __construct(Contract $contract, $revisionReason)
    {
        $this->contract = $contract;
        $this->revisionReason = $revisionReason;

        Log::info('ContractRevisionNotification constructed', [
            'contract_id' => $contract->id,
            'revision_reason' => $revisionReason,
            'revision_reason_length' => strlen($revisionReason ?? ''),
            'revision_reason_type' => gettype($revisionReason)
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Yêu cầu chỉnh sửa thông tin hợp đồng - SGHood',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contract-revision-notification',
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
                'revisionReason' => $this->revisionReason ?? 'Không có lý do nào được cung cấp.',
                'contract' => $this->contract
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
