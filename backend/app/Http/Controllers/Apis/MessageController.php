<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\Apis\SendMessageRequest;
use App\Http\Requests\Apis\StartChatRequest;
use App\Services\Apis\MessageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        $imageUrl = $request->image; // Lấy URL hình ảnh từ request
        if ($request->has('image') && !empty($imageUrl) && !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            $imageUrl = null;
            Log::warning('Invalid image URL skipped:', ['image' => $imageUrl]);
        }

        // Gọi MessageService với cả $imageUrl
        $message = $this->messageService->sendMessage($senderId, $receiverId, $messageText, $imageUrl);

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

    public function startChat(StartChatRequest $request)
    {
        $userId = Auth::id(); // người dùng hiện tại

        $adminId = null;

        if ($request->has('receiver_id')) {
            $requestedId = $request->receiver_id;

            // Nếu receiver là chính user thì bỏ qua
            if ($requestedId != $userId) {
                $adminId = $requestedId;
            }
        }

        // Gọi service
        $message = $this->messageService->startChatAdmin($adminId, $userId);

        if (!$message) {
            return response()->json([
                'status' => false,
                'message' => 'Không thể bắt đầu cuộc trò chuyện, vui lòng thử lại sau.'
            ], 500);
        }

        // adminId có thể được gán lại trong service
        return response()->json([
            'status' => true,
            'message' => 'Bắt đầu cuộc trò chuyện thành công',
            'data' => $message,
            'admin_id' => $message['sender_id'] == $userId
                ? $message['receiver_id']
                : $message['sender_id']
        ]);
    }
}
