<?php

namespace App\Services\Apis;

use App\Models\Notification;

class NotificationService
{
    public function getUserNotifications($userId, $sortOrder = 'desc')
    {
        return Notification::where('user_id', $userId)
            ->orderBy('created_at', $sortOrder)
            ->get();
    }

    public function getNotificationById($notificationId)
    {
        return Notification::where('id', $notificationId)
            ->first();
    }
}