<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Invoice;

class InvoiceCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $room;
    public $meterReading;
    public $contract;

    /**
     * Create a new message instance.
     *
     * @param Invoice $invoice
     * @param mixed $room
     * @param mixed $meterReading
     * @param mixed $contract
     * @return void
     */
    public function __construct(Invoice $invoice, $room, $meterReading, $contract)
    {
        $this->invoice = $invoice;
        $this->room = $room;
        $this->meterReading = $meterReading;
        $this->contract = $contract;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Hóa đơn mới đã được tạo - #' . $this->invoice->code)
                    ->view('emails.invoice-created');
    }
}
