<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Message;
use Illuminate\Support\Facades\Hash;

class MessageTestSeeder extends Seeder
{
    public function run(): void
{
    // Xoá toàn bộ tin nhắn cũ để đảm bảo count đúng
    \App\Models\Message::truncate();

    // Tạo user và admin
    $user = \App\Models\User::firstOrCreate([
        'email' => 'user@test.com'
    ], [
        'name' => 'User Test',
        'phone' => '0000000000',
        'password' => bcrypt('password'),
        'role' => 'Người đăng ký',
        'status' => 'Hoạt động',
    ]);

    $admin = \App\Models\User::firstOrCreate([
        'email' => 'admin@test.com'
    ], [
        'name' => 'Admin Test',
        'phone' => '1111111111',
        'password' => bcrypt('password'),
        'role' => 'Quản trị viên',
        'status' => 'Hoạt động',
    ]);

    // Tạo 1 tin nhắn đầu tiên
    \App\Models\Message::create([
        'sender_id' => $user->id,
        'receiver_id' => $admin->id,
        'message' => 'Xin chào, tôi cần hỗ trợ!',
    ]);

    // Kiểm tra lại số lượng tin nhắn
    $count = \App\Models\Message::where(function ($q) use ($user, $admin) {
        $q->where('sender_id', $user->id)->where('receiver_id', $admin->id);
    })->orWhere(function ($q) use ($user, $admin) {
        $q->where('sender_id', $admin->id)->where('receiver_id', $user->id);
    })->count();

    \Log::info("🔍 Count tin nhắn giữa user và admin: $count");

    if ($count === 1) {
        \App\Models\Message::create([
            'sender_id' => $admin->id,
            'receiver_id' => $user->id,
            'message' => 'Chào bạn! Admin sẽ phản hồi trong thời gian sớm nhất.',
        ]);
        \Log::info("✅ Đã gửi auto-reply từ admin.");
    }
}

}
