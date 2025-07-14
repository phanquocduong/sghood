<?php

namespace App\Services\Apis;

use App\Models\Message;
use App\Models\User;
use App\Models\UserAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\FirebaseException;

class MessageService
{
    protected $firestore;
    protected $storage;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(storage_path('firebase/firebase_credentials.json'));

        $this->firestore = $factory->createFirestore()->database();
        $this->storage = $factory->createStorage();
    }

    public function sendMessage(int $senderId, ?int $receiverId, string $messageText, ?string $imageUrl = null)
    {
        Log::info('Sender ID:', ['id' => $senderId]);

        $sender = User::find($senderId);
        if (!$sender) {
            Log::error('Sender không tồn tại.');
            return null;
        }

        // Nếu sender là user, xử lý gán admin nếu cần
        if ($sender->role !== 'Quản trị viên') {
            $userAdmin = UserAdmin::firstOrCreate(
                ['user_id' => $senderId],
                ['admin_id' => $this->getRandomAdmin()?->id]
            );

            if (!$userAdmin->admin_id) {
                Log::error('Không tìm thấy admin để gán.');
                return null;
            }

            $receiverId = $userAdmin->admin_id;
        }

        if (!$receiverId || $senderId === $receiverId) {
            Log::error('Receiver không hợp lệ.', ['receiver_id' => $receiverId]);
            return null;
        }

        // Đẩy lên Firestore
        try {
            $chatPath = $senderId < $receiverId
                ? "chats/{$senderId}_{$receiverId}"
                : "chats/{$receiverId}_{$senderId}";

            $messageData = [
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'message' => $messageText,
                'imageUrl' => $imageUrl ?? '', // Lưu URL hình ảnh
                'is_read' => false,
                'created_at' => now()->toDateTimeString(),
            ];

            $docRef = $this->firestore->collection('messages')->add($messageData);

            Log::info('Tin nhắn đã được gửi lên Firestore', [
                'document_id' => $docRef->id(),
                'data' => $messageData
            ]);

            return [
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'message' => $messageText,
                'imageUrl' => $imageUrl,
                'is_read' => false
            ];
        } catch (FirebaseException $e) {
            Log::error('Lỗi khi gửi tin nhắn lên Firestore', [
                'error' => $e->getMessage(),
                'sender_id' => $senderId,
                'receiver_id' => $receiverId
            ]);
            return null;
        }
    }

    private function getRandomAdmin(?int $excludeUserId = null)
    {
        $query = User::where('role', 'Quản trị viên');

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        return $query->inRandomOrder()->first();
    }

    public function getChatHistory($userId)
    {
        $authId = Auth::id();

        return Message::where(function ($query) use ($authId, $userId) {
            $query->where('sender_id', $authId)->where('receiver_id', $userId);
        })->orWhere(function ($query) use ($authId, $userId) {
            $query->where('sender_id', $userId)->where('receiver_id', $authId);
        })->orderBy('created_at', 'asc')->get();
    }

    public function getUsersChattedWithAdmin()
    {
        $adminId = Auth::id();
        $userIds = Message::where('sender_id', $adminId)
            ->orWhere('receiver_id', $adminId)
            ->pluck('sender_id')
            ->merge(
                Message::where('sender_id', $adminId)
                    ->orWhere('receiver_id', $adminId)
                    ->pluck('receiver_id')
            )
            ->unique()
            ->filter(fn($id) => $id != $adminId)
            ->values();

        return User::whereIn('id', $userIds)->get();
    }

    public function startChatAdmin(?int $adminId, int $userId)
    {
        if (!$adminId) {
            $adminId = $this->assignOrGetAdminForUser($userId);
            if (!$adminId) {
                return null;
            }
        }

        if ($adminId == $userId) {
            Log::error('Admin ID trùng với user ID, không thể tạo cuộc trò chuyện chính mình.', [
                'admin_id' => $adminId,
                'user_id' => $userId,
            ]);
            return null;
        }

        // Kiểm tra đã có đoạn chat chưa
        $messagesRef = $this->firestore->collection('messages');
        $query = $messagesRef
            ->where('sender_id', 'in', [$adminId, $userId])
            ->where('receiver_id', 'in', [$adminId, $userId])
            ->orderBy('created_at', 'ASC')
            ->limit(1);

        $documents = $query->documents();

        if ($documents->isEmpty()) {
            try {
                $firstMessageData = [
                    'sender_id' => $adminId,
                    'receiver_id' => $userId,
                    'message' => 'Chào bạn! Tôi có thể giúp gì cho bạn ?',
                    'imageUrl' => '', // Không có hình ảnh
                    'is_read' => false,
                    'created_at' => now()->toDateTimeString(),
                ];

                $docRef = $messagesRef->add($firstMessageData);

                Log::info('Đã tạo tin nhắn chào mừng (Firestore)', [
                    'document_id' => $docRef->id()
                ]);

                return $firstMessageData;
            } catch (FirebaseException $e) {
                Log::error('Lỗi khi tạo tin nhắn chào mừng (Firestore)', [
                    'error' => $e->getMessage(),
                    'admin_id' => $adminId,
                    'user_id' => $userId
                ]);
                return null;
            }
        } else {
            $firstDoc = $documents->rows()[0];
            return $firstDoc->data();
        }
    }

    private function assignOrGetAdminForUser(int $userId): ?int
    {
        $userAdmin = UserAdmin::where('user_id', $userId)->first();

        if ($userAdmin) {
            return $userAdmin->admin_id;
        }

        $admin = $this->getRandomAdmin($userId);

        if (!$admin || $admin->id === $userId) {
            Log::error('Không tìm thấy admin hợp lệ để gán cho user ID: ' . $userId);
            return null;
        }

        UserAdmin::create([
            'user_id' => $userId,
            'admin_id' => $admin->id,
        ]);

        return $admin->id;
    }
}
