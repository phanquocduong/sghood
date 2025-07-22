<?php

namespace App\Mail\Apis;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CheckoutEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $checkout;
    public $type;
    public $title;

    public function __construct($checkout, $type, $title)
    {
        $this->checkout = $checkout;
        $this->type = $type;
        $this->title = $title;
    }

    public function build()
    {
        return $this->subject($this->title)
                    ->view('emails.apis.checkout_notification')
                    ->with(['type' => $this->type, 'title' => $this->title]);
    }
}
