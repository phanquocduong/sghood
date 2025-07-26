<?php

namespace App\Mail;

use App\Models\Checkout;
use App\Models\User;
use App\Models\Room;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckoutAutoConfirmedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $checkout;
    public $user;
    public $room;

    /**
     * Create a new message instance.
     */
    public function __construct(Checkout $checkout, User $user, Room $room)
    {
        $this->checkout = $checkout;
        $this->user = $user;
        $this->room = $room;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tự động xác nhận kiểm kê phòng - ' . $this->room->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.checkout-auto-confirmed',
            with: [
                'checkout' => $this->checkout,
                'user' => $this->user,
                'room' => $this->room,
                'motel' => $this->room->motel,
                'contract' => $this->checkout->contract,
                'depositAmount' => $this->checkout->contract->deposit_amount ?? 0,
                'deductionAmount' => $this->checkout->deduction_amount ?? 0,
                'finalRefundAmount' => $this->checkout->final_refunded_amount ?? 0
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
