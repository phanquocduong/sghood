<?php

namespace App\Services\Apis;

use App\Models\Notification;

class NotificationService
{
    public function getUserNotifications($userId, $sortOrder = 'desc', $status = '', $perPage = 10)
    {
        $query = Notification::where('user_id', $userId);

        if (!empty($status)) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', $sortOrder)->paginate($perPage);
    }

    public function getNotificationById($notificationId)
    {
        return Notification::where('id', $notificationId)
            ->first();
    }
}