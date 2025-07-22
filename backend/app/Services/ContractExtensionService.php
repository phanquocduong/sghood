<?php
namespace App\Services;

use App\Models\ContractExtension;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContractExtensionApprovedNotification;
use App\Mail\ContractExtensionRejectedNotification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;
use App\Models\Notification;
use Illuminate\Support\Facades\App;

class ContractExtensionService
{
    public function getAllContractExtensions(string $querySearch = '', string $status = '', string $sort = 'desc'): array
    {
        try {
            DB::enableQueryLog();
            $query = ContractExtension::with(['contract', 'contract.user', 'contract.room']);

            // Apply search filter by room name only
            if ($querySearch) {
                $querySearch = trim($querySearch);
                $query->whereHas('contract.room', function ($roomQuery) use ($querySearch) {
                    $roomQuery->where('name', 'like', "%{$querySearch}%");
                });
            }

            // Apply status filter
            if ($status) {
                $query->where('status', $status);
            }

            // Apply sort by created_at
            $sortDirection = in_array($sort, ['asc', 'desc']) ? $sort : 'desc';
            $contractExtensions = $query->orderBy('created_at', $sortDirection)->paginate(15);

            Log::info('SQL Query for contract extensions', DB::getQueryLog());
            return ['data' => $contractExtensions];
        } catch (\Throwable $e) {
            Log::error('Error getting contract extensions: ' . $e->getMessage(), [
                'query_search' => $querySearch,
                'status' => $status,
                'sort' => $sort
            ]);
            return ['error' => 'Đã xảy ra lỗi khi lấy danh sách gia hạn hợp đồng', 'status' => 500];
        }
    }

    public function getContractExtensionById($id): array
    {
        try {
            $contractExtension = ContractExtension::with(['contract', 'contract.user', 'contract.room'])->find($id);

            if (!$contractExtension) {
                return ['error' => 'Không tìm thấy gia hạn hợp đồng', 'status' => 404];
            }

            return ['data' => $contractExtension];
        } catch (\Throwable $e) {
            Log::error('Error getting contract extension by ID: ' . $e->getMessage(), [
                'contract_extension_id' => $id
            ]);
            return ['error' => 'Đã xảy ra lỗi khi lấy thông tin gia hạn hợp đồng', 'status' => 500];
        }
    }

    public function updateContractExtensionStatus($id, string $status, ?string $rejectionReason = null): array
    {
        try {
            $contractExtension = ContractExtension::with(['contract', 'contract.user', 'contract.room'])->find($id);

            if (!$contractExtension) {
                return ['error' => 'Không tìm thấy gia hạn hợp đồng', 'status' => 404];
            }

            $oldStatus = $contractExtension->status;

            Log::info('Updating contract extension status', [
                'contract_extension_id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $status,
                'rejection_reason' => $rejectionReason
            ]);

            $data = ['status' => $status];
            if ($status === 'Từ chối' && $rejectionReason) {
                $data['rejection_reason'] = $rejectionReason;
            } elseif ($status === 'Từ chối' && !$rejectionReason) {
                return ['error' => 'Lý do từ chối là bắt buộc', 'status' => 422];
            } elseif ($status !== 'Từ chối') {
                $data['rejection_reason'] = null;
            }

            $contractExtension->update($data);
            $contractExtension->refresh();

            // Cập nhật thông tin hợp đồng nếu trạng thái là "Hoạt động"
            if ($status === 'Hoạt động' && $oldStatus !== 'Hoạt động') {
                $contract = $contractExtension->contract;
                $contract->update([
                    'end_date' => $contractExtension->new_end_date,
                    'rental_price' => $contractExtension->new_rental_price,
                ]);
                Log::info('Hợp đồng đã được cập nhật với thông tin gia hạn', [
                    'contract_id' => $contract->id,
                    'new_end_date' => $contractExtension->new_end_date,
                    'new_rental_price' => $contractExtension->new_rental_price
                ]);
            }

            // Gửi email và thông báo khi trạng thái thay đổi
            if ($status === 'Hoạt động' && $oldStatus !== 'Hoạt động') {
                $this->sendContractExtensionApprovedEmail($contractExtension);
                $this->createNotification(
                    $contractExtension->contract->user_id,
                    'Gia hạn hợp đồng đã được phê duyệt',
                    'Yêu cầu gia hạn hợp đồng của bạn đã được phê duyệt.'
                );
            } elseif ($status === 'Từ chối' && $oldStatus !== 'Từ chối') {
                $this->sendContractExtensionRejectedEmail($contractExtension);
                $this->createNotification(
                    $contractExtension->contract->user_id,
                    'Gia hạn hợp đồng bị từ chối',
                    'Yêu cầu gia hạn hợp đồng của bạn đã bị từ chối. Lý do: ' . ($rejectionReason ?? 'Không có lý do cụ thể')
                );
            }

            return ['data' => $contractExtension];
        } catch (\Throwable $e) {
            Log::error('Error updating contract extension status: ' . $e->getMessage(), [
                'contract_extension_id' => $id,
                'status' => $status,
                'rejection_reason' => $rejectionReason
            ]);
            return ['error' => 'Đã xảy ra lỗi khi cập nhật trạng thái gia hạn hợp đồng', 'status' => 500];
        }
    }

    private function sendContractExtensionApprovedEmail(ContractExtension $contractExtension): void
    {
        try {
            if (!$contractExtension->contract->user || !$contractExtension->contract->user->email) {
                Log::warning('Không thể gửi email phê duyệt gia hạn - không tìm thấy người dùng hoặc email', [
                    'contract_extension_id' => $contractExtension->id
                ]);
                return;
            }

            Mail::to($contractExtension->contract->user->email, $contractExtension->contract->user->name)
                ->send(new ContractExtensionApprovedNotification($contractExtension));

            Log::info('Email phê duyệt gia hạn hợp đồng đã được gửi thành công', [
                'contract_extension_id' => $contractExtension->id,
                'user_email' => $contractExtension->contract->user->email
            ]);
        } catch (\Throwable $e) {
            Log::error('Lỗi khi gửi email phê duyệt gia hạn hợp đồng: ' . $e->getMessage(), [
                'contract_extension_id' => $contractExtension->id,
                'user_email' => $contractExtension->contract->user->email ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function sendContractExtensionRejectedEmail(ContractExtension $contractExtension): void
    {
        try {
            if (!$contractExtension->contract->user || !$contractExtension->contract->user->email) {
                Log::warning('Không thể gửi email từ chối gia hạn - không tìm thấy người dùng hoặc email', [
                    'contract_extension_id' => $contractExtension->id
                ]);
                return;
            }

            Mail::to($contractExtension->contract->user->email, $contractExtension->contract->user->name)
                ->send(new ContractExtensionRejectedNotification($contractExtension));

            Log::info('Email từ chối gia hạn hợp đồng đã được gửi thành công', [
                'contract_extension_id' => $contractExtension->id,
                'user_email' => $contractExtension->contract->user->email
            ]);
        } catch (\Throwable $e) {
            Log::error('Lỗi khi gửi email từ chối gia hạn hợp đồng: ' . $e->getMessage(), [
                'contract_extension_id' => $contractExtension->id,
                'user_email' => $contractExtension->contract->user->email ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function createNotification($userId, $title, $content): void
    {
        try {
            $notificationData = [
                'user_id' => $userId,
                'title' => $title,
                'content' => $content,
                'status' => 'Chưa đọc'
            ];

            $notification = Notification::create($notificationData);
            Log::info('Notification created', [
                'notification_id' => $notification->id,
                'user_id' => $userId
            ]);

            // Gửi FCM nếu có token
            $user = \App\Models\User::find($userId);
            if ($user && $user->fcm_token) {
                $messaging = app('firebase.messaging');
                $fcmMessage = CloudMessage::withTarget('token', $user->fcm_token)
                    ->withNotification(FirebaseNotification::create($title, $content));

                try {
                    $messaging->send($fcmMessage);
                    Log::info('FCM sent to user', ['user_id' => $user->id]);
                } catch (\Exception $e) {
                    Log::error('FCM send error', ['error' => $e->getMessage()]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error creating notification: ' . $e->getMessage(), [
                'user_id' => $userId,
                'title' => $title
            ]);
        }
    }


    protected $contractExtensions;

    public function __construct(ContractExtension $contractExtensions)
    {
        $this->contractExtensions = $contractExtensions;
    }

    public function getAllExtensions()
    {
        return $this->contractExtensions->all();
    }

    public function getExtensionById($id)
    {
        return $this->contractExtensions->find($id);
    }

    public function getPendingApprovals()
    {
        return $this->contractExtensions->where('status', 'chờ duyệt')->get();
    }
}
