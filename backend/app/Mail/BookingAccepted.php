<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingAccepted extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $contractUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, string $contractUrl = null)
    {
        $this->booking = $booking;
        $this->contractUrl = $contractUrl ?: url('/contract/preview/' . $booking->id);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Yêu cầu đặt phòng đã được chấp nhận - SGHood',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            html: 'emails.booking-accepted',
            with: [
                'booking' => $this->booking,
                // 'rejectionReason' => $this->rejectionReason,
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
