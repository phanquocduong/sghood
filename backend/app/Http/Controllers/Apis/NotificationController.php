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

    public function getAllNotificationByUser(Request $request, $userId)
    {
        $sortOrder = $request->query('sort', 'desc') === 'asc' ? 'asc' : 'desc';
        $status = $request->query('status', '');
        $perPage = $request->query('per_page',5);

        $notifications = $this->notificationService->getUserNotifications($userId, $sortOrder, $status, $perPage);

        if ($notifications->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Không có thông báo nào.'
            ], 200);
        }

        // Trả về dữ liệu đã phân trang
        return response()->json([
            'status' => true,
            'data' => [
                'current_page' => $notifications->currentPage(), // Trang hiện tại
                'data' => $notifications->items(), // Dữ liệu thông báo của trang hiện tại
                'first_page_url' => $notifications->url(1), // URL của trang đầu tiên
                'from' => $notifications->firstItem(), // Mục đầu tiên của trang hiện tại
                'last_page' => $notifications->lastPage(), // Tổng số trang
                'last_page_url' => $notifications->url($notifications->lastPage()), // URL của trang cuối cùng
                'next_page_url' => $notifications->nextPageUrl(), // URL của trang kế tiếp
                'path' => $notifications->path(), // Đường dẫn của trang hiện tại
                'per_page' => $notifications->perPage(), // Số mục trên mỗi trang
                'prev_page_url' => $notifications->previousPageUrl(), // URL của trang trước đó
                'to' => $notifications->lastItem(), // Mục cuối cùng của trang hiện tại
                'total' => $notifications->total(), // Tổng số thông báo
            ]
        ]);
    }
    public function markAsRead(Request $request, $id)
    {
        $result = $this->notificationService->markNotificationAsRead($id);

        if (!$result) {
            return response()->json([
                'status' => false,
                'message' => 'Thông báo không tồn tại hoặc đã được đánh dấu là đã đọc.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Thông báo đã được đánh dấu là đã đọc.'
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