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

}
