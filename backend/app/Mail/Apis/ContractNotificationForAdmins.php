<?php

namespace App\Mail\Apis;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContractNotificationForAdmins extends Mailable
{
    use Queueable, SerializesModels;

    public $contract;
    public $title;
    public $body;

    /**
     * Create a new message instance.
     *
     * @param Contract $contract
     */
    public function __construct(Contract $contract, string $context)
    {
        $this->contract = $contract;
        $this->title = $this->generateTitle($context);
        $this->body = $this->generateBody($context);
    }

    /**
     * Generate email title based on contract status.
     */
    private function generateTitle(string $context): string
    {
        return match ($context) {
            'Chờ ký' => "Hợp đồng #{$this->contract->id} đã được ký",
            'Chờ chỉnh sửa' => "Hợp đồng #{$this->contract->id} đã được chỉnh sửa và gửi lại để duyệt",
            'Chờ thanh toán tiền cọc' => "Hợp đồng #{$this->contract->id} đã được kích hoạt",
            'Gia hạn' => "Yêu cầu gia hạn hợp đồng #{$this->contract->id}",
            'Trả phòng' => "Yêu cầu trả phòng hợp đồng #{$this->contract->id}",
            default => "Hợp đồng mới #{$this->contract->id} đang chờ duyệt",
        };
    }

    /**
     * Generate email body based on contract context.
     */
    private function generateBody(string $context): string
    {
        return match ($context) {
            'Chờ ký' => "Hợp đồng #{$this->contract->id} từ người dùng {$this->contract->user->name} đã được ký và đang chờ thanh toán tiền cọc.",
            'Chờ chỉnh sửa' => "Hợp đồng #{$this->contract->id} từ người dùng {$this->contract->user->name} đã được chỉnh sửa và gửi lại để duyệt.",
            'Chờ thanh toán tiền cọc' => "Hợp đồng #{$this->contract->id} từ người dùng {$this->contract->user->name} đã thanh toán tiền cọc và đã được kích hoạt.",
            'Gia hạn' => "Hợp đồng #{$this->contract->id} từ người dùng {$this->contract->user->name} đã gửi yêu cầu gia hạn, đang chờ duyệt.",
            'Trả phòng' => "Hợp đồng #{$this->contract->id} từ người dùng {$this->contract->user->name} đã gửi yêu cầu trả phòng và kiểm kê, đang chờ xử lý.",
            default => "Hợp đồng #{$this->contract->id} từ người dùng {$this->contract->user->name} đã được gửi để duyệt.",
        };
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->title)
                    ->view('emails.apis.contract-notification-for-admins')
                    ->with([
                        'contract' => $this->contract,
                        'title' => $this->title,
                        'body' => $this->body,
                    ]);
    }
}
