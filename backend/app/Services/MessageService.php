<?php
namespace App\Services;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;

class MessageService
{
    public function getMessagesWithUser(int $userId, int $authId)
    {
        return Message::where(function ($q) use ($userId, $authId) {
                $q->where('sender_id', $authId)
                  ->where('receiver_id', $userId);
            })
            ->orWhere(function ($q) use ($userId, $authId) {
                $q->where('sender_id', $userId)
                  ->where('receiver_id', $authId);
            })
            ->orderBy('created_at')
            ->get();
    }

    public function sendMessage(int $senderId, int $receiverId, string $text): Message
    {
        // 1. Lưu vào DB như cũ
        $message = Message::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => $text,
        ]);
        return $message;
    }

    public function getTotalUnreadFor($userId)
    {
        return Message::where('receiver_id', $userId)
            ->where('is_read', 0)
            ->count();
    }

    public function getUserListWithUnread($authId)
    {
        return User::where('role', '!=', 'Quản trị viên')
            ->whereHas('sentMessages', function ($q) {
                $q->whereIn('receiver_id', function ($subQuery) {
                    $subQuery->select('id')->from('users')->where('role', 'Quản trị viên');
                });
            })
            ->withCount([
                'sentMessages as unread_count' => function ($q) use ($authId) {
                    $q->where('receiver_id', $authId)
                    ->where('is_read', 0);
                }
            ])->get();
    }

    public function markAsRead(int $senderId, int $receiverId)
    {
        return Message::where('sender_id', $senderId)
            ->where('receiver_id', $receiverId)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);
    }


}
