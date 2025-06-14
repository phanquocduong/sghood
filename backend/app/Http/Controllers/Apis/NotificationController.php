<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Apis\NotificationService;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function getAllNotificationByUser($userId)
    {
        $notifications = $this->notificationService->getUserNotifications($userId);

        if ($notifications->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Không có thông báo nào cho người dùng này.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $notifications
        ]);
    }

    public function getByNotificationId($id)
    {
        $notification = $this->notificationService->getNotificationById($id);

        if (!$notification) {
            return response()->json([
                'status' => false,
                'message' => 'Thông báo không tồn tại.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $notification
        ]);
    }

}
