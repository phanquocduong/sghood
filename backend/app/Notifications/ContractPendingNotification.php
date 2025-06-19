<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class ContractPendingNotification extends Notification
{
    use Queueable;

    protected $contract;

    public function __construct($contract)
    {
        $this->contract = $contract;
    }

    public function via($notifiable)
    {
        return ['firebase'];
    }

    public function toFirebase($notifiable)
    {
        $title = 'Hợp đồng mới đang chờ duyệt';
        $body = "Hợp đồng #{$this->contract->id} từ người dùng {$this->contract->user_id} đã được tạo.";

        return CloudMessage::new()
            ->withNotification(
                FirebaseNotification::create($title, $body)
            )
            ->withData([
                'contract_id' => (string) $this->contract->id,
                'status' => $this->contract->status,
            ]);
    }
}
