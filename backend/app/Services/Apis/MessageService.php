<?php

namespace App\Services\Apis;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
<<<<<<< HEAD
=======
use Illuminate\Support\Facades\Log;
>>>>>>> 46c71a99232ed9963c2be3989a8e454ec6dc2858

class MessageService
{
    public function sendMessage(array $data)
    {
    $data['sender_id'] = Auth::id();

<<<<<<< HEAD
    \Log::info('Sender ID:', ['id' => $data['sender_id']]); // thêm dòng này
=======
    Log::info('Sender ID:', ['id' => $data['sender_id']]); // thêm dòng này
>>>>>>> 46c71a99232ed9963c2be3989a8e454ec6dc2858

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
<<<<<<< HEAD
        $adminId = 1;
=======
        $adminId = auth()->id();
>>>>>>> 46c71a99232ed9963c2be3989a8e454ec6dc2858

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
<<<<<<< HEAD
            Message::create([
=======
          return Message::create([
>>>>>>> 46c71a99232ed9963c2be3989a8e454ec6dc2858
                'sender_id' => $adminId,
                'receiver_id' => $userId,
                'message' => 'Chào bạn! Bạn cần hỗ trợ gì từ chúng tôi?'
            ]);
        }
<<<<<<< HEAD
    }
=======
        //  đã có message, trả về message đầu tiên
        return Message::where(function($query) use ($adminId, $userId) {
            $query->where('sender_id', $adminId)
                  ->where('receiver_id', $userId);
        })->orWhere(function($query) use ($adminId, $userId) {
            $query->where('sender_id', $userId)
                  ->where('receiver_id', $adminId);
        })->orderBy('created_at', 'asc')->first();
         }
>>>>>>> 46c71a99232ed9963c2be3989a8e454ec6dc2858
}
