<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use Google\Cloud\Core\Timestamp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Firestore;

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

    public function getLatestUnreadForHeader(): array
    {
        $adminId = Auth::id(); // ID admin hiện tại

        $firestore = (new Factory)->createFirestore();
        $db = $firestore->database();

        $messagesRef = $db->collection('messages');

        // Lọc: gửi đến admin, chưa đọc, từ user
        $query = $messagesRef
            ->where('receiver_id', '=', $adminId)
            ->where('is_read', '=', false)
            ->where('sender_role', '=', 'user') // 👈 cần lưu role khi gửi vào Firestore
            ->orderBy('createdAt', 'DESC')
            ->limit(3);

        $docs = $query->documents();

        $latest = [];
        foreach ($docs as $doc) {
            $data = $doc->data();
            $latest[] = [
                'message'     => $data['text'] ?? '[Không có nội dung]',
                'created_at'  => \Carbon\Carbon::parse($data['createdAt'])->diffForHumans(),
                'is_read'     => $data['is_read'] ?? false,
                'url'         => route('messages.index'), // 👈 hoặc link tới tin nhắn cụ thể
            ];
        }

        return [
            'unread_count' => $docs->size(),
            'latest' => $latest,
        ];
    }


    public static function getUnreadMessagesDashboard()
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

                // Gán sender_name
                $user = User::find($data['sender_id'] ?? null);
                $data['sender_name'] = $user?->name ?? 'Unknown';

                // Gán message nếu thiếu
                $data['message'] = $data['text'] ?? '(Không có nội dung)';

                // ✅ Chuyển trường createdAt từ Firestore thành created_at (chuẩn Laravel)
                $data['created_at'] = $data['createdAt'] ?? now();

                $messages[] = (object) $data;
            }
        }



        return [
            'data' => $messages,
            'error' => null,
        ];
    }
}
