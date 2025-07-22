<?php

namespace App\Mail\Apis;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContractEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $contract;
    public $type;
    public $title;

    public function __construct(Contract $contract, string $type, string $title)
    {
        $this->contract = $contract;
        $this->type = $type;
        $this->title = $title;
    }

    public function build()
    {
        return $this->subject($this->title)
                    ->view('emails.apis.contract_notification')
                    ->with(['type' => $this->type, 'title' => $this->title]);
    }
}
