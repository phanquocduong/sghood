<?php

namespace App\Services\Apis;

use App\Models\Notification;

class NotificationService
{
    public function getUserNotifications($userId)
    {
        return Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getNotificationById($notificationId)
    {
        return Notification::where('id', $notificationId)
            ->first();
    }
}
