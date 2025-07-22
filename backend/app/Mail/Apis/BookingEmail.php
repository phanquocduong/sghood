<?php

namespace App\Mail\Apis;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $type;
    public $title;

    public function __construct($booking, $type, $title)
    {
        $this->booking = $booking;
        $this->type = $type;
        $this->title = $title;
    }

    public function build()
    {
        return $this->subject($this->title)
                    ->view('emails.apis.booking_notification')
                    ->with(['type' => $this->type, 'title' => $this->title]);
    }
}
