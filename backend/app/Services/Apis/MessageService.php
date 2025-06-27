<?php
namespace App\Services\Apis;

use App\Models\Message;
use App\Models\User;
use App\Models\UserAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MessageService
{
    public function sendMessage(int $senderId, int $receiverId, string $messageText)
    {
        Log::info('Sender ID:', ['id' => $senderId]);

        // Kiểm tra xem người gửi có phải là User (không phải admin)
        $sender = User::find($senderId);
        $receiver = User::find($receiverId);

        // Nếu sender là user, chưa từng có admin gán thì gán admin ngẫu nhiên
        if ($sender && $sender->role !== 'Quản trị viên') {
            $userAdmin = UserAdmin::where('user_id', $senderId)->first();

            if (!$userAdmin) {
                // Gán admin ngẫu nhiên
                $admin = User::where('role', 'Quản trị viên')->inRandomOrder()->first();
                if (!$admin) {
                    Log::error('Không tìm thấy admin để gán.');
                    return null;
                }

                // Lưu vào bảng user_admins
                $userAdmin = UserAdmin::create([
                    'user_id' => $senderId,
                    'admin_id' => $admin->id,
                ]);

                // Gán lại receiverId là admin đó (bỏ qua receiver ban đầu nếu khác)
                $receiverId = $admin->id;
            } else {
                // Nếu đã có admin thì ép receiverId về admin đã gán
                $receiverId = $userAdmin->admin_id;
            }
        }

        // Kiểm tra đã từng chat chưa
        $exists = Message::where(function ($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $senderId)
                ->where('receiver_id', $receiverId);
        })->orWhere(function ($query) use ($senderId, $receiverId) {
            $query->where('sender_id', $receiverId)
                ->where('receiver_id', $senderId);
        })->exists();

        try {
            // Gửi tin nhắn chính
            $userMessage = Message::create([
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'message' => $messageText,
                'read' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Nếu là lần đầu (chưa tồn tại đoạn chat), gửi auto-reply từ admin
            if (!$exists && $sender->role !== 'Quản trị viên') {
                Message::create([
                    'sender_id' => $receiverId, // admin
                    'receiver_id' => $senderId, // user
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
        $adminId = 2; // Giả sử ID của admin là 2, bạn có thể thay đổi theo nhu cầu

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
    public function startChatAdmin($adminId, $userId)
    {
        $query = Message::where(function ($query) use ($adminId, $userId) {
            $query->where('sender_id', $adminId)
                ->where('receiver_id', $userId);
        })->orWhere(function ($query) use ($adminId, $userId) {
            $query->where('sender_id', $userId)
                ->where('receiver_id', $adminId);
        });

        // Kiểm tra tin nhắn đã tồn tại
        $firstMessage = $query->orderBy('created_at', 'asc')->first();

        if (!$firstMessage) {
            try {
                // Tạo tin nhắn mới với đầy đủ thông tin
                $firstMessage = Message::create([
                    'sender_id' => $adminId,
                    'receiver_id' => $userId,
                    'message' => 'Chào bạn! Tôi có thể giúp gì cho bạn ?',
                    'read' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                Log::info('Tin nhắn chào mừng đã được tạo', ['message_id' => $firstMessage->id]);

            } catch (\Exception $e) {
                Log::error('Lỗi khi tạo tin nhắn chào mừng', [
                    'error' => $e->getMessage(),
                    'admin_id' => $adminId,
                    'user_id' => $userId
                ]);

                // Trả về null nếu có lỗi
                return null;
            }
        }

        return $firstMessage;
    }

}
