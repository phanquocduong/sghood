<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject('Chủ đề: ' . $this->data['subject'] . ' - Khách hàng liên hệ từ website')
                    ->view('emails.contact')
                    ->with('data', $this->data);
    }

}
