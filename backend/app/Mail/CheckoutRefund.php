<?php

namespace App\Mail;

use App\Models\Checkout;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CheckoutRefund extends Mailable
{
    use Queueable, SerializesModels;

    public $checkout;
    public $userName;
    public $roomName;
    public $checkOutDate;
    public $finalRefundedAmount;

    public function __construct(Checkout $checkout, $userName, $roomName, $checkOutDate, $finalRefundedAmount = 0)
    {
        $this->checkout = $checkout;
        $this->userName = $userName;
        $this->roomName = $roomName;
        $this->checkOutDate = $checkOutDate;
        $this->finalRefundedAmount = $finalRefundedAmount;
    }

    public function build()
    {
        return $this->subject('Thông báo hoàn tiền đặt phòng')
                    ->view('emails.checkout_refund')
                    ->with([
                        'userName' => $this->userName,
                        'roomName' => $this->roomName,
                        'checkOutDate' => $this->checkOutDate,
                        'finalRefundedAmount' => $this->finalRefundedAmount,
                        'checkout' => $this->checkout,
                    ]);
    }
}
