<?php

namespace App\Services\Apis;

use App\Mail\ContractNotificationForAdmins;
use App\Models\Contract;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Kreait\Firebase\Messaging\CloudMessage;

class NotificationService
{
    public function notifyContractForAdmins(Contract $contract, string $oldStatus): void
    {
        try {
            $admins = User::where('role', 'Quản trị viên')->get();

            if ($admins->isEmpty()) {
                Log::warning('Không tìm thấy admin với role Quản trị viên');
                return;
            }

            $title = match ($oldStatus) {
                'Chờ ký' => "Hợp đồng #{$contract->id} đã được ký",
                'Chờ chỉnh sửa' => "Hợp đồng #{$contract->id} đã được chỉnh sửa và gửi lại để duyệt",
                'Chờ thanh toán tiền cọc' => "Hợp đồng #{$contract->id} đã được kích hoạt",
                default => "Hợp đồng mới #{$contract->id} đang chờ duyệt",
            };

            $body = match ($oldStatus) {
                'Chờ ký' => "Hợp đồng #{$contract->id} từ người dùng {$contract->user->name} đã được ký và đang chờ thanh toán tiền cọc.",
                'Chờ chỉnh sửa' => "Hợp đồng #{$contract->id} từ người dùng {$contract->user->name} đã được chỉnh sửa và gửi lại để duyệt.",
                'Chờ thanh toán tiền cọc' => "Hợp đồng #{$contract->id} từ người dùng {$contract->user->name} đã thanh toán tiền cọc và đã được kích hoạt.",
                default => "Hợp đồng #{$contract->id} từ người dùng {$contract->user->name} đã được gửi để duyệt.",
            };

            Mail::to($admins->pluck('email'))->send(new ContractNotificationForAdmins($contract, $oldStatus));

            $messaging = app('firebase.messaging');

            foreach ($admins as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => $title,
                    'content' => $body,
                ]);

                if ($admin->fcm_token) {
                    $baseUrl = config('app.url');
                    $message = CloudMessage::fromArray([
                        'token' => $admin->fcm_token,
                        'notification' => ['title' => $title, 'body' => $body],
                        'data' => [
                            'link' => "$baseUrl/contracts/$contract->id"
                        ],
                    ]);

                    $messaging->send($message);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Lỗi gửi thông báo cho admin', [
                'contract_id' => $contract->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function getUserNotifications($userId, $sortOrder = 'desc', $status = '', $perPage = 10)
    {
        $query = Notification::where('user_id', $userId);

        if (!empty($status)) {
            $query->where('status', $status);
        }

        return $query->orderBy('created_at', $sortOrder)->paginate($perPage);
    }

    public function markNotificationAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);

        if ($notification) {
            $notification->status = 'Đã đọc';
            $notification->save();
            return true;
        }

        return false;
    }

    public function getNotificationById($notificationId)
    {
        return Notification::where('id', $notificationId)
            ->first();
    }
}
