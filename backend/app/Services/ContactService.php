<?php
namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMail;

class ContactService
{
    /**
     * Gửi thông tin liên hệ về email.
     *
     * @param array $data
     * @return void
     */
    public function sendContactEmail(array $data)
    {
        Mail::to('sghoodvn@gmail.com')->send(new ContactMail($data));
    }
}
