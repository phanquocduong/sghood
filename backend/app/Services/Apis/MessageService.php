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

        return respone()->json([
            'status' =>true,
            'data'=>$messages
        ]);
    }

    public function getUsersChattedWithAdmin()
    {
        $adminId = auth()->id();

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
        $hasMessages = Message::where(function($query) use ($adminId, $userId) {
            $query->where('sender_id', $adminId)
                  ->where('receiver_id', $userId);
        })->orWhere(function($query) use ($adminId, $userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', $adminId);
        })->exists();

        if (!$hasMessages) {
          return Message::create([
                'sender_id' => $adminId,
                'receiver_id' => $userId,
                'message' => 'Chào bạn! Bạn cần hỗ trợ gì từ chúng tôi?'
            ]);
        }
        //  đã có message, trả về message đầu tiên
        return Message::where(function($query) use ($adminId, $userId) {
            $query->where('sender_id', $adminId)
                  ->where('receiver_id', $userId);
        })->orWhere(function($query) use ($adminId, $userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', $adminId);
        })->orderBy('created_at', 'asc')->first();
         }
}
