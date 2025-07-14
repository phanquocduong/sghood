<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MessageService;
use Illuminate\Http\Request;
use App\Http\Requests\SendMessageRequest;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Firestore;

class MessageController extends Controller
{
    protected $messageService;
    protected $firestore;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
        $serviceAccountPath = storage_path('firebase\firebase-adminsdk.json');
        if (!file_exists($serviceAccountPath)) {
            throw new \Exception("File service account not found at: $serviceAccountPath. Please download it from Firebase Console and place it in the config/firebase/ directory.");
        }
        if (!is_readable($serviceAccountPath)) {
            throw new \Exception("File service account at $serviceAccountPath is not readable. Check file permissions.");
        }
        $factory = (new Factory)
            ->withServiceAccount($serviceAccountPath);
        $this->firestore = $factory->createFirestore();
    }

    public function index(Request $request)
    {
        $authId = Auth::id();

        // Lấy danh sách người dùng với số tin nhắn chưa đọc (có thể giữ logic SQL hoặc chuyển sang Firestore)
        $users = $this->messageService->getUserListWithUnread($authId);

        $selectedUserId = $request->get('user_id');
        $selectedUser = null;
        $messages = collect();

        if ($selectedUserId) {
            // Đánh dấu tin nhắn đã đọc (có thể dùng Firestore)
            $this->messageService->markAsReadFirestore($selectedUserId, $authId);

            $selectedUser = User::find($selectedUserId);
            // Lấy tin nhắn từ Firestore
            $messages = $this->getMessagesFromFirestore($selectedUserId, $authId);
        }

        $totalUnread = $this->messageService->getTotalUnreadFor($authId);

        return view('messages.index', compact(
            'users',
            'messages',
            'selectedUser',
            'selectedUserId',
            'totalUnread'
        ));
    }

    public function sendMessage(SendMessageRequest $request)
    {
        $adminId = Auth::id();
        $receiverId = $request->receiver_id;
        $messageText = $request->message;

        $message = $this->messageService->sendMessage($adminId, $receiverId, $messageText);

        return response()->json([
            'status' => true,
            'message' => 'Gửi tin nhắn thành công',
            'data' => $message,
        ]);
    }

    public function showChat(Request $request)
    {
        $authId = Auth::id();
        $selectedUserId = $request->get('user_id');
        $selectedUser = null;
        $messages = collect();

        if ($selectedUserId) {
            $this->messageService->markAsReadFirestore($selectedUserId, $authId);
            $selectedUser = User::find($selectedUserId);
            // Lấy tin nhắn từ Firestore
            $messages = $this->getMessagesFromFirestore($selectedUserId, $authId);
        }

        return view('messages.partials.chat_box', compact('selectedUser', 'messages', 'selectedUserId'))->render();
    }

    public function markAsRead(Request $request)
    {
        $authId = Auth::id();
        $userId = $request->input('user_id');

        $this->messageService->markAsReadFirestore($userId, $authId);

        return response()->json(['status' => true]);
    }

    // Phương thức mới để lấy tin nhắn từ Firestore
    protected function getMessagesFromFirestore($selectedUserId, $authId)
    {
        $chatId = [$authId, $selectedUserId];
        sort($chatId);
        $chatId = implode('_', $chatId);

        $messages = $this->firestore->database()->collection('messages')
            ->where('chatId', '=', $chatId)
            ->orderBy('createdAt', 'asc')
            ->documents()
            ->rows();

        $messagesArray = [];
        foreach ($messages as $message) {
            $data = $message->data();
            $messagesArray[] = [
                'sender_id' => $data['sender_id'],
                'message' => $data['text'] ?? '',
                'type' => $data['type'] ?? 'text',
                'content' => $data['content'] ?? '', // imageUrl nếu type là 'image'
                'createdAt' => $data['createdAt'] ?? null,
                'is_read' => $data['is_read'] ?? false,
            ];
        }

        return collect($messagesArray);
    }
}
