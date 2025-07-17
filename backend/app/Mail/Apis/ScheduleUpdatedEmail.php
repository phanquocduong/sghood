<?php

namespace App\Mail\Apis;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ScheduleUpdatedEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $schedule;

    public function __construct($schedule)
    {
        $this->schedule = $schedule;
    }

    public function build()
    {
        return $this->subject('Lịch xem nhà trọ đã được cập nhật')
                    ->view('emails.apis.schedule_updated');
    }
}

