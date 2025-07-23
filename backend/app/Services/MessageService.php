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
        $firestore = (new Factory)->createFirestore();
        $db = $firestore->database();

        $messagesRef = $db->collection('messages');
        $query = $messagesRef
            ->where('sender_id', 'in', [$authId, $userId])
            ->where('receiver_id', 'in', [$authId, $userId])
            ->orderBy('created_at');

        $documents = $query->documents();

        $messages = [];
        foreach ($documents as $doc) {
            if ($doc->exists()) {
                $messages[] = $doc->data();
            }
        }

        return collect($messages);
    }

    public function sendMessage(int $senderId, int $receiverId, string $text)
    {
        $firestore = (new Factory)->createFirestore();
        $db = $firestore->database();

        $messageData = [
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => $text,
            'is_read' => false,
            'created_at' => now()->toDateTimeString(),
        ];

        $db->collection('messages')->add($messageData);

        return $messageData;
    }

    public function getTotalUnreadFor(int $userId): int
    {
        $firestore = (new Factory)->createFirestore();
        $db = $firestore->database();

        $messagesRef = $db->collection('messages');
        $query = $messagesRef
            ->where('receiver_id', '=', $userId)
            ->where('is_read', '=', false);

        $documents = $query->documents();

        return $documents->size(); // Đếm số document
    }



    public function getUserListWithUnread($authId)
    {
        $firestore = (new Factory)->createFirestore();
        $db = $firestore->database();

        $messagesRef = $db->collection('messages');
        $query = $messagesRef->where('receiver_id', '=', $authId);

        $documents = $query->documents();

        $userIds = [];
        $unreadCounts = [];

        foreach ($documents as $doc) {
            if ($doc->exists()) {
                $data = $doc->data();
                $senderId = $data['sender_id'];
                $userIds[$senderId] = true;

                if (empty($data['is_read']) || $data['is_read'] == false || $data['is_read'] == 0) {
                    if (!isset($unreadCounts[$senderId])) $unreadCounts[$senderId] = 0;
                    $unreadCounts[$senderId]++;
                }
            }
        }

        // Lấy thông tin user từ MySQL
        $users = User::whereIn('id', array_keys($userIds))
            ->where('role', '!=', 'Quản trị viên')
            ->get();

        // Gắn số tin chưa đọc
        foreach ($users as $user) {
            $user->unread_count = $unreadCounts[$user->id] ?? 0;
        }

        return $users;
    }

    // public function markAsRead(int $senderId, int $receiverId)
    // {
    //     return Message::where('sender_id', $senderId)
    //         ->where('receiver_id', $receiverId)
    //         ->where('is_read', 0)
    //         ->update(['is_read' => 1]);
    // }

    public function markAsReadFirestore(int $senderId, int $receiverId)
    {
        $firestore = (new Factory)->createFirestore();
        $db = $firestore->database();

        $messagesRef = $db->collection('messages');
        $query = $messagesRef
            ->where('sender_id', '=', $senderId)
            ->where('receiver_id', '=', $receiverId)
            ->where('is_read', '=', false);

        $documents = $query->documents();

        foreach ($documents as $doc) {
            if ($doc->exists()) {
                $doc->reference()->update([
                    ['path' => 'is_read', 'value' => true]
                ]);
            }
        }
    }

    public function getLatestUnreadForHeader()
    {
        $firestore = (new Factory)->createFirestore();
        $db = $firestore->database();

        $messagesRef = $db->collection('messages');

        $unreadQuery = $messagesRef->where('is_read', '=', false);
        $unreadDocs = $unreadQuery->documents();
        $unreadCount = $unreadDocs->size();

        $latestQuery = $messagesRef->orderBy('created_at', 'DESC')->limit(3);
        $latestDocs = $latestQuery->documents();

        $latest = [];
        foreach ($latestDocs as $doc) {
            if ($doc->exists()) {
                $data = $doc->data();
                $data['url'] = route('messages.index');
                $latest[] = $data;
            }
        }

        return [
            'unread_count' => $unreadCount,
            'latest' => $latest,
        ];
    }

    public static function getAllMessages()
    {
        $firestore = (new Factory)->createFirestore();
        $db = $firestore->database();

        $messagesRef = $db->collection('messages');

        // 🔷 chỉ lấy những message có is_read == false
        $query = $messagesRef
            ->where('is_read', '=', false)
            ->orderBy('createdAt', 'desc')   // 👈 Thêm sort
            ->limit(3);                      // 👈 Giới hạn 3


        $documents = $query->documents();

        $messages = collect();

        foreach ($documents as $doc) {
            if ($doc->exists()) {
                $data = $doc->data();
                $data['id'] = $doc->id();
                // Lấy tên user nếu cần
                $user = User::find($data['sender_id']);
                $data['sender_name'] = $user?->name ?? 'Unknown';

                $messages[] = (object) $data;
            }
        }

        return [
            'data' => $messages,
            'error' => null,
        ];
    }
}
