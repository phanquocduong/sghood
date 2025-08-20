<?php

namespace App\Mail;

use App\Models\ContractTenant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContractTenantStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public $contractTenant;
    public $status;
    public $rejectionReason;
    public $roomName;

    /**
     * Create a new message instance.
     */
    public function __construct(ContractTenant $contractTenant, string $status, ?string $rejectionReason, string $roomName)
    {
        $this->contractTenant = $contractTenant;
        $this->status = $status;
        $this->rejectionReason = $rejectionReason;
        $this->roomName = $roomName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = match ($this->status) {
            'Chờ duyệt' => 'Yêu cầu người ở chung đang chờ duyệt',
            'Đã duyệt' => 'Yêu cầu người ở chung đã được duyệt',
            'Từ chối' => 'Yêu cầu người ở chung bị từ chối',
            'Đang ở' => 'Người ở chung đã chuyển sang trạng thái Đang ở',
            'Đã rời đi' => 'Người ở chung đã rời đi',
            default => 'Cập nhật trạng thái người ở chung',
        };

        return $this->subject($subject)
                    ->view('emails.contract_tenant_status_changed');
    }
}
