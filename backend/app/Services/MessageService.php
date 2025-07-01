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
        $message = Message::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => $text,
        ]);

        try {
            $firebase = (new \Kreait\Firebase\Factory)
                ->withServiceAccount(storage_path('firebase/firebase_credentials.json'))
                ->withDatabaseUri('https://tro-viet-default-rtdb.firebaseio.com') // ✅ đúng URI
                ->createDatabase();


            $chatPath = $senderId < $receiverId
                ? "chats/{$senderId}_{$receiverId}"
                : "chats/{$receiverId}_{$senderId}";

            $firebase->getReference($chatPath)->push([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'message' => $text,
                'timestamp' => now()->timestamp,
            ]);
        } catch (\Throwable $e) {
            Log::error('Firebase error: ' . $e->getMessage());
        }

        return $message;
    }
    private function getChatKey($senderId, $receiverId): string
    {
        return $senderId < $receiverId
            ? "{$senderId}_{$receiverId}"
            : "{$receiverId}_{$senderId}";
    }



}
