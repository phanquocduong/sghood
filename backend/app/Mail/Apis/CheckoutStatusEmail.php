<?php

namespace App\Mail\Apis;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CheckoutStatusEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $checkout;
    public $action;

    /**
     * Create a new message instance.
     *
     * @param \App\Models\Checkout $checkout
     * @param string $action
     * @return void
     */
    public function __construct($checkout, $action)
    {
        $this->checkout = $checkout;
        $this->action = $action;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = match ($this->action) {
            'confirm' => "Kết quả kiểm kê trả phòng #{$this->checkout->id} đã được người dùng đồng ý",
            'reject' => "Kết quả kiểm kê trả phòng #{$this->checkout->id} bị người dùng từ chối",
            'cancel' => "Yêu cầu trả phòng #{$this->checkout->id} bị hủy",
            default => "Yêu cầu trả phòng #{$this->checkout->id} đã cập nhật trạng thái mới"
        };

        return $this->subject($subject)
                    ->view('emails.apis.checkout_status')
                    ->with([
                        'checkout' => $this->checkout,
                        'action' => $this->action
                    ]);
    }
}
