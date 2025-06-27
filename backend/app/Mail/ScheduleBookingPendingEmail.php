<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ScheduleBookingPendingEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $item;
    public $type;

    public function __construct($item, string $type)
    {
        $this->item = $item;
        $this->type = $type;
    }

    public function build()
    {
        $subject = $this->type === 'schedule' ? 'Lịch xem phòng mới chờ duyệt' : 'Đặt phòng mới chờ duyệt';
        return $this->subject($subject)
                    ->view('emails.schedule-booking-pending');
    }
}
