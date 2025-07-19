<?php

namespace App\Mail\Apis;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ScheduleEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $schedule;
    public $type;
    public $title;

    public function __construct($schedule, $type, $title)
    {
        $this->schedule = $schedule;
        $this->type = $type;
        $this->title = $title;
    }

    public function build()
    {
        return $this->subject($this->title)
                    ->view('emails.apis.schedule_notification')
                    ->with(['type' => $this->type, 'title' => $this->title]);
    }
}
