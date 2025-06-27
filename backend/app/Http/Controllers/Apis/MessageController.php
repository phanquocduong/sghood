<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\Apis\SendMessageRequest;
use App\Http\Requests\Apis\StartChatRequest;
use App\Services\Apis\MessageService;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    protected $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }


    public function sendMessage(SendMessageRequest $request)
    {
        $senderId = Auth::id(); // Lấy ID người dùng đã đăng nhập
        $receiverId = $request->receiver_id; // ID người nhận từ request
        $messageText = $request->message;

        $message = $this->messageService->sendMessage($senderId, $receiverId, $messageText);

        if (!$message) {
            return response()->json([
                'status' => false,
                'message' => 'Không thể gửi tin nhắn'
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Tin nhắn đã được gửi',
            'data' => $message
        ]);
    }

    public function getChatHistory($userId)
    {
        $messages = $this->messageService->getChatHistory($userId);

        return response()->json([
            'status' => true,
            'data' => $messages
        ]);
    }

    public function getAdminConversations()
    {
        $users = $this->messageService->getUsersChattedWithAdmin();

        return response()->json([
            'status' => true,
            'data' => $users
        ]);
    }
    public function startChat(StartChatRequest $request)
    {
        // $userId = $request->seeder_id;
        $userId = Auth::id(); // Lấy ID người dùng đã đăng nhập
        if ($request->has('receiver_id')) {
            $adminId = $request->receiver_id;
        } else {
        // Lấy 1 admin ngẫu nhiên từ bảng users (giả sử role = 'Quản trị viên')
        $admin = User::where('role', 'Quản trị viên')->inRandomOrder()->first();

        if (!$admin) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy admin để bắt đầu cuộc trò chuyện.'
            ], 404);
        }

        $adminId = $admin->id;
        }

        $message = $this->messageService->startChatAdmin($adminId, $userId);

        if (!$message) {
            return response()->json([
                'status' => false,
                'message' => 'Không thể bắt đầu cuộc trò chuyện, vui lòng thử lại sau.'
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'Bắt đầu cuộc trò chuyện thành công',
            'data' => $message,
            'admin_id' => $adminId
        ]);
    }
}
