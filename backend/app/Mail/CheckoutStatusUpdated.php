<?php

namespace App\Mail;

use App\Models\Checkout;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CheckoutStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $checkout;
    public $userName;
    public $roomName;
    public $checkOutDate;

    public function __construct(Checkout $checkout, $userName, $roomName, $checkOutDate)
    {
        $this->checkout = $checkout;
        $this->userName = $userName;
        $this->roomName = $roomName;
        $this->checkOutDate = $checkOutDate;
    }

    public function build()
    {
        return $this->subject('Thông báo trạng thái kiểm kê')
                    ->view('emails.checkout_status_updated')
                    ->with([
                        'userName' => $this->userName,
                        'roomName' => $this->roomName,
                        'checkOutDate' => $this->checkOutDate,
                        'checkout' => $this->checkout,
                    ]);
    }
}
