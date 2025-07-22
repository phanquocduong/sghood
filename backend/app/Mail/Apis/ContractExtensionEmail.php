<?php

namespace App\Mail\Apis;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContractExtensionEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $contractExtension;
    public $type;
    public $title;

    public function __construct($contractExtension, $type, $title)
    {
        $this->contractExtension = $contractExtension;
        $this->type = $type;
        $this->title = $title;
    }

    public function build()
    {
        return $this->subject($this->title)
                    ->view('emails.apis.contract_extension_notification')
                    ->with(['type' => $this->type, 'title' => $this->title]);
    }
}
