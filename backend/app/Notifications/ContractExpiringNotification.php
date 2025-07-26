<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Contract;

class ContractExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;

    public function __construct(Contract $contract)
    {
        $this->contract = $contract;
    }

    // Gửi qua mail + custom channel (FCM)
    public function via($notifiable)
    {
        return ['mail', 'fcm'];
    }

    // Nội dung mail
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Hợp đồng #{$this->contract->id} sắp hết hạn")
            ->line("Hợp đồng của bạn sẽ hết hạn vào ngày {$this->contract->end_date}.")
            ->line('Vui lòng gia hạn hoặc liên hệ để biết thêm chi tiết.');
    }

    // Nội dung gửi tới FCM
    public function toFcm($notifiable)
    {
        return [
            'title' => "Hợp đồng sắp hết hạn",
            'body' => "Hợp đồng #{$this->contract->id} sẽ hết hạn ngày {$this->contract->end_date}.",
            'data' => [
                'contract_id' => $this->contract->id,
                'type' => 'contract_expiry',
            ],
        ];
    }
}
