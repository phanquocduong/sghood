<?php
namespace App\Services\Apis;

use App\Models\Message;
use App\Models\User;
use App\Models\UserAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MessageService
{
    public function sendMessage(int $senderId, ?int $receiverId, string $messageText)
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

        // Kiểm tra xem đã từng chat giữa 2 người chưa
        $exists = Message::where(function ($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $senderId)->where('receiver_id', $receiverId);
        })->orWhere(function ($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $receiverId)->where('receiver_id', $senderId);
        })->exists();

        try {
            // Tạo tin nhắn chính
            $userMessage = Message::create([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'message' => $messageText,
                'read' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // ✅ Đẩy lên Firebase
            try {
                $chatPath = $senderId < $receiverId
                    ? "chats/{$senderId}_{$receiverId}"
                    : "chats/{$receiverId}_{$senderId}";

                $firebase = (new \Kreait\Firebase\Factory)
                    ->withServiceAccount(storage_path('firebase/firebase_credentials.json'))
                    ->withDatabaseUri('https://tro-viet-default-rtdb.firebaseio.com')
                    ->createDatabase();

                $firebase->getReference($chatPath)->push([
                    'sender_id' => $senderId,
                    'receiver_id' => $receiverId,
                    'message' => $messageText,
                    'timestamp' => now()->timestamp,
                ]);
            } catch (\Throwable $e) {
                Log::error('Lỗi khi đẩy Firebase: ' . $e->getMessage());
            }

            // Auto-reply nếu lần đầu
            if ($sender->role !== 'Quản trị viên' && !$exists) {
                Message::create([
                    'sender_id' => $receiverId,
                    'receiver_id' => $senderId,
                    'message' => 'Cảm ơn bạn đã nhắn tin, admin sẽ phản hồi sớm nhất có thể.',
                    'read' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            return $userMessage;

        } catch (\Exception $e) {
            Log::error('Lỗi khi gửi tin nhắn', [
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
        // Nếu không có admin truyền vào, tự gán hoặc lấy admin đã gán
        if (!$adminId) {
            $adminId = $this->assignOrGetAdminForUser($userId);
            if (!$adminId) {
                return null; // Không có admin khả dụng
            }
        }

        // Tránh gán chính user làm admin
        if ($adminId == $userId) {
            Log::error('Admin ID trùng với user ID, không thể tạo cuộc trò chuyện chính mình.', [
                'admin_id' => $adminId,
                'user_id' => $userId,
            ]);
            return null;
        }

        // Kiểm tra đã có đoạn chat chưa
        $query = Message::where(function ($query) use ($adminId, $userId) {
            $query->where('sender_id', $adminId)->where('receiver_id', $userId);
        })->orWhere(function ($query) use ($adminId, $userId) {
            $query->where('sender_id', $userId)->where('receiver_id', $adminId);
        });

        $firstMessage = $query->orderBy('created_at', 'asc')->first();

        if (!$firstMessage) {
            try {
                $firstMessage = Message::create([
                    'sender_id' => $adminId,
                    'receiver_id' => $userId,
                    'message' => 'Chào bạn! Tôi có thể giúp gì cho bạn ?',
                    'read' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                Log::info('Đã tạo tin nhắn chào mừng', ['message_id' => $firstMessage->id]);

            } catch (\Exception $e) {
                Log::error('Lỗi khi tạo tin nhắn chào mừng', [
                    'error' => $e->getMessage(),
                    'admin_id' => $adminId,
                    'user_id' => $userId
                ]);

                return null;
            }
        }

        return $firstMessage;
    }

    private function assignOrGetAdminForUser(int $userId): ?int
    {
        $userAdmin = UserAdmin::where('user_id', $userId)->first();

        if ($userAdmin) {
            return $userAdmin->admin_id;
        }

        $admin = $this->getRandomAdmin($userId); // 💡 exclude chính user

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
