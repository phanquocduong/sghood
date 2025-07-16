<?php
namespace App\Mail;

use App\Models\ContractExtension;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContractExtensionRejectedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $contractExtension;

    public function __construct(ContractExtension $contractExtension)
    {
        $this->contractExtension = $contractExtension;
    }

    public function build()
    {
        return $this->subject('Gia hạn hợp đồng đã bị từ chối')
                    ->view('emails.contract_extension_rejected')
                    ->with([
                        'contractExtension' => $this->contractExtension,
                    ]);
    }
}
