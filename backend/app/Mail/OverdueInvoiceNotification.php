<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OverdueInvoiceNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $overdueDays;

    public function __construct(Invoice $invoice, int $overdueDays)
    {
        $this->invoice = $invoice;
        $this->overdueDays = $overdueDays;
    }

    public function build()
    {
        return $this->subject('🚨 Thông báo hóa đơn quá hạn thanh toán - Hóa đơn #' . $this->invoice->id)
                    ->view('emails.overdue-invoice')
                    ->with([
                        'invoice' => $this->invoice,
                        'overdueDays' => $this->overdueDays,
                        'user' => $this->invoice->contract->user,
                        'room' => $this->invoice->contract->room,
                        'motel' => $this->invoice->contract->room->motel,
                    ]);
    }
}