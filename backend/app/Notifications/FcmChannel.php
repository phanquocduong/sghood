<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class FcmChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toFcm')) {
            return;
        }

        $fcmData = $notification->toFcm($notifiable);
        $fcmToken = $notifiable->fcm_token;

        if (!$fcmToken) return;

        Http::withToken(config('services.fcm.server_key'))
            ->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $fcmToken,
                'notification' => [
                    'title' => $fcmData['title'],
                    'body' => $fcmData['body'],
                ],
                'data' => $fcmData['data'] ?? [],
            ]);
    }
}
