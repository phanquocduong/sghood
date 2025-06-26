<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Contract;

class ContractPendingEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $contract;

    public function __construct(Contract $contract)
    {
        $this->contract = $contract;
    }

    public function build()
    {
        return $this->subject('Hợp đồng mới chờ duyệt')
                    ->view('emails.contract-pending');
    }
}
