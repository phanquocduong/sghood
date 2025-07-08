<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Message;
use App\Services\MessageService;
use Illuminate\Http\Request;
use App\Http\Requests\SendMessageRequest;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    protected $messageService;

    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;

    }

    public function index(Request $request)
    {
        $authId = auth()->id();

        $users = $this->messageService->getUserListWithUnread($authId);

        $selectedUserId = $request->get('user_id');
        $selectedUser = null;
        $messages = collect();

        if ($selectedUserId) {
            Message::where('sender_id', $selectedUserId)
                ->where('receiver_id', $authId)
                ->where('is_read', 0)
                ->update(['is_read' => 1]);

            $selectedUser = User::find($selectedUserId);
            $messages = $this->messageService->getMessagesWithUser($selectedUserId, $authId);
        }

        $totalUnread = $this->messageService->getTotalUnreadFor($authId);

        return view('messages.index', compact(
            'users', 'messages', 'selectedUser', 'selectedUserId', 'totalUnread'
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
        $selectedUserId = $request->get('user_id');
        $selectedUser = null;
        $messages = collect();

        if ($selectedUserId) {
            $selectedUser = User::find($selectedUserId);
            $messages = $this->messageService->getMessagesWithUser($selectedUserId, auth()->id());
        }
        return view('messages.partials.chat_box', compact('selectedUser', 'messages', 'selectedUserId'))->render();
    }

    public function markAsRead(Request $request)
    {
        $authId = auth()->id();
        $userId = $request->input('user_id');

        $this->messageService->markAsRead($userId, $authId);

        return response()->json(['status' => true]);
    }

}
