<?php
namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\Apis\SendMessageRequest;
use App\Http\Requests\Apis\StartChatRequest;
use App\Services\Apis\MessageService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    protected $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    public function sendMessage(SendMessageRequest $request)
    {
        $data = $request->validated();
        $message = $this->messageService->sendMessage($data);

        return response()->json([
            'status' => true,
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
        $userId = $request->receiver_id;
        $adminId = $request->admin_id ?? 1;

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
