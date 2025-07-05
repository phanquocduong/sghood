<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SchedulePendingEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $schedule;

    public function __construct($schedule)
    {
        $this->schedule = $schedule;
    }

    public function build()
    {
        return $this->subject('Lịch xem nhà trọ mới chờ duyệt')
                    ->view('emails.schedule_pending');
    }
}
