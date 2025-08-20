<?php

namespace App\Mail\Apis;

use App\Models\Contract;
use App\Models\ContractTenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContractTenantEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $contract;
    public $tenant;
    public $type;
    public $title;

    public function __construct(Contract $contract, ContractTenant $tenant, string $type, string $title)
    {
        $this->contract = $contract;
        $this->tenant = $tenant;
        $this->type = $type;
        $this->title = $title;
    }

    public function build()
    {
        return $this->subject($this->title)
                    ->view('emails.apis.contract_tenant_notification')
                    ->with(['type' => $this->type, 'title' => $this->title]);
    }
}
