<?php

namespace App\Mail;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContractExpiryNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $contract;

    public function __construct(Contract $contract)
    {
        $this->contract = $contract;
    }

    public function build()
    {
        return $this->view('emails.contract-expiry')
                    ->subject("🏠 Thông báo: Hợp đồng #{$this->contract->id} sắp hết hạn")
                    ->with(['contract' => $this->contract]);
    }
}