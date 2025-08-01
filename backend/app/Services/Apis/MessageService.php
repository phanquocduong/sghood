<?php

namespace App\Services\Apis;

use App\Models\Message;
use App\Models\User;
use App\Models\UserAdmin;
use Google\Cloud\Firestore\FieldValue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Firestore;

class MessageService
{
    protected $firestore;
    protected $storage;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(storage_path('firebase/firebase-adminsdk.json'));

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

        $firestore = app(Firestore::class);
        $collection = $firestore->database()->collection('messages');

        $query1 = $collection->where('sender_id', '=', $authId)
            ->where('receiver_id', '=', $userId);

        $query2 = $collection->where('sender_id', '=', $userId)
            ->where('receiver_id', '=', $authId);

        // Firestore không hỗ trợ `orWhere` trực tiếp.
        // Nên phải chạy 2 query rồi merge kết quả.
        $docs1 = $query1->documents();
        $docs2 = $query2->documents();

        $allMessages = [];

        foreach ($docs1 as $doc) {
            $allMessages[] = $doc->data();
        }

        foreach ($docs2 as $doc) {
            $allMessages[] = $doc->data();
        }

        // Sắp xếp theo created_at
        usort($allMessages, function ($a, $b) {
            return strtotime($a['created_at']) <=> strtotime($b['created_at']);
        });

        return $allMessages;
    }

    public function startChatAdmin(?int $adminId, int $userId)
    {
        if (!$adminId) {
            $adminId = $this->assignOrGetAdminForUser($userId);
            if (!$adminId) {
                Log::error('Không tìm thấy admin để bắt đầu chat', [
                    'user_id' => $userId,
                ]);
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
        $text = 'Chào mừng bạn đến với dịch vụ hỗ trợ của chúng tôi!';
        $type = 'text'; // Hoặc có thể là 'image', 'file', v.v.

        // Tạo chatId
        $ids = [$adminId, $userId];
        sort($ids);
        $chatId = $ids[0] . '_' . $ids[1];

        $messagesRef = $this->firestore->collection('messages');

        // Kiểm tra đã có chat chưa
        $query = $messagesRef
            ->where('chatId', '=', $chatId)
            ->orderBy('createdAt', 'ASC')
            ->limit(1);

        $documents = $query->documents();

        if ($documents->isEmpty()) {
            try {
                $firstMessageData = [
                    'chatId'      => $chatId,
                    'sender_id'   => $adminId,
                    'receiver_id' => $userId,
                    'text'        => $text,
                    'content'     => '', // nếu là file/image thì dùng trường này
                    'type'        => $type, // 'text', 'image', 'file', ...
                    'is_read'     => false,
                    'createdAt'   => FieldValue::serverTimestamp(),
                ];

                $docRef = $messagesRef->add($firstMessageData);

                Log::info('Đã tạo tin nhắn chào mừng (Firestore)', [
                    'document_id' => $docRef->id(),
                    'chat_id' => $chatId,
                    'user_id' => $userId,
                    'admin_id' => $adminId
                ]);

                return $firstMessageData;
            } catch (\Throwable $e) {
                Log::error('Lỗi khi tạo tin nhắn chào mừng (Firestore)', [
                    'error' => $e->getMessage(),
                    'admin_id' => $adminId,
                    'user_id' => $userId
                ]);
                return null;
            }
        }

        // Nếu đã có → trả về tin nhắn đầu tiên
        return $documents->rows()[0]->data();
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
