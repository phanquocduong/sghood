<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Factory;

class MessageService
{
    /** 🔹 Hàm khởi tạo Firestore, tái sử dụng */
    private function firestore()
    {
        $serviceAccount = storage_path('firebase/firebase-adminsdk.json');

        if (!file_exists($serviceAccount)) {
            throw new \Exception("Firebase service account not found: {$serviceAccount}");
        }

        return (new Factory)
            ->withServiceAccount($serviceAccount)
            ->createFirestore()
            ->database();
    }

    public function getMessagesWithUser(int $userId, int $authId)
    {
        $db = $this->firestore();
        $messagesRef = $db->collection('messages');

        $query = $messagesRef
            ->where('sender_id', 'in', [$authId, $userId])
            ->where('receiver_id', 'in', [$authId, $userId])
            ->orderBy('created_at');

        return collect(
            array_map(fn($doc) => $doc->data(),
                iterator_to_array($query->documents())
            )
        );
    }

    public function sendMessage(int $senderId, int $receiverId, string $text)
    {
        $db = $this->firestore();

        $messageData = [
            'sender_id'   => $senderId,
            'receiver_id' => $receiverId,
            'message'     => $text,
            'is_read'     => false,
            'created_at'  => now()->toDateTimeString(),
        ];

        $db->collection('messages')->add($messageData);

        return $messageData;
    }

    public function getTotalUnreadFor(int $userId): int
    {
        $db = $this->firestore();
        $query = $db->collection('messages')
            ->where('receiver_id', '=', $userId)
            ->where('is_read', '=', false);

        return $query->documents()->size();
    }

    public function getUserListWithUnread($authId)
    {
        $db = $this->firestore();
        $documents = $db->collection('messages')
            ->where('receiver_id', '=', $authId)
            ->documents();

        $userIds = [];
        $unreadCounts = [];

        foreach ($documents as $doc) {
            if ($doc->exists()) {
                $data = $doc->data();
                $senderId = $data['sender_id'];
                $userIds[$senderId] = true;

                if (empty($data['is_read'])) {
                    $unreadCounts[$senderId] = ($unreadCounts[$senderId] ?? 0) + 1;
                }
            }
        }

        $users = User::whereIn('id', array_keys($userIds))
            ->where('role', '!=', 'Quản trị viên')
            ->get();

        foreach ($users as $user) {
            $user->unread_count = $unreadCounts[$user->id] ?? 0;
        }

        return $users;
    }

    public function markAsReadFirestore(int $senderId, int $receiverId)
    {
        $db = $this->firestore();
        $query = $db->collection('messages')
            ->where('sender_id', '=', $senderId)
            ->where('receiver_id', '=', $receiverId)
            ->where('is_read', '=', false);

        foreach ($query->documents() as $doc) {
            if ($doc->exists()) {
                $doc->reference()->update([
                    ['path' => 'is_read', 'value' => true]
                ]);
            }
        }
    }

    public function getLatestUnreadForHeader(): array
    {
        $adminId = Auth::id();
        $db = $this->firestore();
        $messagesRef = $db->collection('messages');

        $docs = $messagesRef
            ->where('receiver_id', '=', $adminId)
            ->where('is_read', '=', false)
            ->orderBy('createdAt', 'DESC')
            ->limit(3)
            ->documents();

        $latest = [];
        foreach ($docs as $doc) {
            $data = $doc->data();
            $latest[] = [
                'message'    => $data['text'] ?? '[Không có nội dung]',
                'created_at' => isset($data['createdAt'])
                                ? \Carbon\Carbon::parse($data['createdAt'])->diffForHumans()
                                : 'Không xác định',
                'is_read'    => $data['is_read'] ?? false,
                'url'        => route('messages.index'),
            ];
        }

        $unreadCount = $messagesRef
            ->where('receiver_id', '=', $adminId)
            ->where('is_read', '=', false)
            ->documents()
            ->size();

        return [
            'unread_count' => $unreadCount,
            'latest'       => $latest,
        ];
    }

    public static function getUnreadMessagesDashboard()
    {
        $serviceAccount = storage_path('firebase/firebase-adminsdk.json');
        if (!file_exists($serviceAccount)) {
            return [
                'data'  => [],
                'error' => "Firebase service account not found: {$serviceAccount}"
            ];
        }

        $db = (new Factory)
            ->withServiceAccount($serviceAccount)
            ->createFirestore()
            ->database();

        $query = $db->collection('messages')
            ->where('is_read', '=', false)
            ->orderBy('createdAt', 'desc')
            ->limit(3);

        $messages = collect();
        foreach ($query->documents() as $doc) {
            if ($doc->exists()) {
                $data = $doc->data();
                $data['id'] = $doc->id();
                $user = User::find($data['sender_id'] ?? null);

                $data['sender_name'] = $user?->name ?? 'Unknown';
                $data['message'] = $data['text'] ?? '(Không có nội dung)';
                $data['created_at'] = $data['createdAt'] ?? now();

                $messages[] = (object) $data;
            }
        }

        return ['data' => $messages, 'error' => null];
    }
}
