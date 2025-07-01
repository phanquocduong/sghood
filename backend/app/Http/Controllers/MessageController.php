<?php
namespace App\Http\Controllers;
use App\Models\User;
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
        $users = User::where('role', '!=', 'Quản trị viên')
            ->whereHas('sentMessages', function ($q) {
                $q->whereIn('receiver_id', function ($subQuery) {
                    $subQuery->select('id')->from('users')->where('role', 'Quản trị viên');
                });
            })->get();

        $selectedUserId = $request->get('user_id');
        $selectedUser = null;
        $messages = collect();

        if ($selectedUserId) {
            $selectedUser = User::find($selectedUserId);
            $messages = $this->messageService->getMessagesWithUser($selectedUserId, auth()->id());
        }

        return view('messages.index', compact('users', 'messages', 'selectedUser', 'selectedUserId'));
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


}

