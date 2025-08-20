<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Message;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class SendMessageNotificationToAdmin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;
    protected $admin;

    public function __construct(Message $message, User $admin)
    {
        $this->message = $message;
        $this->admin = $admin;
    }

    public function handle(): void
    {
        try {
            // Tạo thông báo trong database
            $notificationData = [
                'user_id' => $this->admin->id,
                'title' => 'Tin nhắn mới từ người dùng',
                'content' => 'Bạn vừa nhận được tin nhắn mới từ ' . $this->message->user->name . ': "' . $this->message->content . '"',
                'status' => 'Chưa đọc',
            ];
            Notification::create($notificationData);

            // Gửi thông báo đẩy FCM
            if ($this->admin->fcm_token) {
                $messaging = app('firebase.messaging');
                $fcmMessage = CloudMessage::withTarget('token', $this->admin->fcm_token)
                    ->withNotification(FirebaseNotification::create(
                        $notificationData['title'],
                        $notificationData['content']
                    ))
                    ->withData(['url' => 'https://sghood.com.vn/admin/messages']);

                $messaging->send($fcmMessage);

                Log::info('FCM sent to admin', [
                    'admin_id' => $this->admin->id,
                    'message_id' => $this->message->id,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error in SendMessageNotificationToAdmin job', [
                'admin_id' => $this->admin->id,
                'message_id' => $this->message->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
