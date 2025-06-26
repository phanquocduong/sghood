<?php
namespace App\Services\Apis;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MessageService
{
    public function sendMessage(array $data)
    {
        $data['sender_id'] = Auth::id();

        \Log::info('Sender ID:', ['id' => $data['sender_id']]); // thêm dòng này

        return Message::create($data);
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
        $adminId = 1;

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
                'message' => 'Chào bạn! Bạn cần hỗ trợ gì từ chúng tôi?',
                'read' => false,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            \Log::info('Tin nhắn chào mừng đã được tạo', ['message_id' => $firstMessage->id]);

        } catch (\Exception $e) {
            \Log::error('Lỗi khi tạo tin nhắn chào mừng', [
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
