<?php

namespace App\Mail\Apis;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RepairRequestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $repairRequest;
    public $type;
    public $title;

    public function __construct($repairRequest, $type, $title)
    {
        $this->repairRequest = $repairRequest;
        $this->type = $type;
        $this->title = $title;
    }

    public function build()
    {
        return $this->subject($this->title)
                    ->view('emails.apis.repair_request_notification')
                    ->with(['type' => $this->type, 'title' => $this->title]);
    }
}
