<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\MessageService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    protected $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string',
        ]);

        $message = $this->messageService->sendMessage($request->only('receiver_id', 'message'));

        return response()->json([
            'status' => true,
            'message' => 'Gửi thành công',
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
}
