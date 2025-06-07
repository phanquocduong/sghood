<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingRejected extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $booking;
    public $rejectionReason;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, string $rejectionReason = '')
    {
        $this->booking = $booking;
        $this->rejectionReason = $rejectionReason;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thông báo từ chối đặt phòng - ' . ($this->booking->room->name ?? 'Phòng'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-rejected',
            with: [
                'booking' => $this->booking,
                'rejectionReason' => $this->rejectionReason,
                'userName' => $this->booking->user->name ?? 'Khách hàng',
                'roomName' => $this->booking->room->name ?? 'Phòng',
                'startDate' => $this->booking->start_date ? \Carbon\Carbon::parse($this->booking->start_date)->format('d/m/Y') : '',
                'endDate' => $this->booking->end_date ? \Carbon\Carbon::parse($this->booking->end_date)->format('d/m/Y') : '',
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
