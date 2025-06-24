<?php
namespace App\Http\Controllers;
use App\Models\Message;
use App\Models\User;
use App\Http\Requests\SendMessageRequest;
use App\Services\MessageService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    protected $messageService;
    public function __construct(MessageService $messageService)
    {
        $this->messageService = $messageService;
    }
    public function index(Request $request)
    {
        $users = User::where('role', 'Người đăng ký')->get();
        $selectedUserId = $request->get('user_id');
        $selectedUser = null;
        $messages = collect();
        if ($selectedUserId) {
            $selectedUser = User::find($selectedUserId);
            $messages = Message::where(function($q) use ($selectedUserId) {
                    $q->where('sender_id', auth()->id())
                    ->where('receiver_id', $selectedUserId);
                })->orWhere(function($q) use ($selectedUserId) {
                    $q->where('sender_id', $selectedUserId)
                    ->where('receiver_id', auth()->id());
                })
                ->orderBy('created_at')
                ->get();
        }

        return view('messages.index', compact('users', 'messages', 'selectedUser', 'selectedUserId'));
    }

    public function sendMessage(SendMessageRequest $request)
    {
        Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'message' => $request->message
        ]);

        return redirect()->route('messages.index', ['user_id' => $request->receiver_id]);
    }
}
