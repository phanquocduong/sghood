<?php
namespace App\Services;

use App\Models\Contract;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContractRevisionNotification;
use App\Mail\ContractSignNotification;
use App\Mail\ContractConfirmNotification;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class ContractService
{
    public function getAllContracts(string $querySearch = '', string $status = '', string $sort = 'desc'): array
    {
        try {
            DB::enableQueryLog();
            $query = Contract::with(['user', 'room', 'booking']);

            // Apply search filter
           if ($querySearch) {
                $querySearch = trim($querySearch);
                $query->where(function ($q) use ($querySearch) {
                    $q->orWhereHas('user', function ($userQuery) use ($querySearch) {
                        $userQuery->where('name', 'like', "%{$querySearch}%");
                    })
                    ->orWhereHas('room', function ($roomQuery) use ($querySearch) {
                        $roomQuery->where('name', 'like', "%{$querySearch}%");
                    });
                });
            }

            if ($status) {
                $query->where('status', $status);
            }

            // Apply sort by created_at
            $sortDirection = in_array($sort, ['asc', 'desc']) ? $sort : 'desc';
            $contracts = $query->orderBy('created_at', $sortDirection)->paginate(15);
            \Log::info('SQL Query', \DB::getQueryLog());
            return ['data' => $contracts];
        } catch (\Throwable $e) {
            Log::error('Error getting contracts: ' . $e->getMessage(), [
                'query_search' => $querySearch,
                'status' => $status,
                'sort' => $sort
            ]);
            return ['error' => 'Đã xảy ra lỗi khi lấy danh sách hợp đồng', 'status' => 500];
        }
    }

    public function getContractById(int $id): array
    {
        try {
            $contract = Contract::with(['user', 'room', 'booking'])->find($id);

            if (!$contract) {
                return ['error' => 'Không tìm thấy hợp đồng', 'status' => 404];
            }

            return ['data' => $contract];
        } catch (\Throwable $e) {
            Log::error('Error getting contract by ID: ' . $e->getMessage(), [
                'contract_id' => $id
            ]);
            return ['error' => 'Đã xảy ra lỗi khi lấy thông tin hợp đồng', 'status' => 500];
        }
    }

    public function updateContractStatus(int $id, string $status): array
    {
        try {
            $contract = Contract::with(['user', 'room', 'booking'])->find($id);

            if (!$contract) {
                return ['error' => 'Không tìm thấy hợp đồng', 'status' => 404];
            }

            $oldStatus = $contract->status;

            Log::info('Updating contract status', [
                'contract_id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $status
            ]);

            $contract->update(['status' => $status]);
            $contract->refresh();

            // Gửi email thông báo khi trạng thái chuyển thành "Chờ chỉnh sửa"
            if ($status === 'Chờ chỉnh sửa' && $oldStatus !== 'Chờ chỉnh sửa') {
                $this->sendContractRevisionEmail($contract);
                // Tạo thông báo cho người dùng
                $notificationdata = [
                    'user_id' => $contract->user_id,
                    'title' => 'Hợp đồng cần chỉnh sửa',
                    'content' => 'Hợp đồng của bạn cần chỉnh sửa. Vui lòng kiểm tra email để biết chi tiết.',
                    'status' => 'Chưa đọc'
                ];
                $notification = Notification::create($notificationdata);
                Log::info('Notification created for contract revision', [
                    'contract_id' => $contract->id,
                    'notification_id' => $notification->id
                ]);

                // gửi FCM token
                $user = User::find($notificationdata['user_id']);

                if ($user && $user->fcm_token) {
                    $messaging = app('firebase.messaging');

                    $fcmMessage = CloudMessage::withTarget('token', $user->fcm_token)
                        ->withNotification(FirebaseNotification::create(
                            $notificationdata['title'],
                            $notificationdata['content']
                        ));

                    try {
                        $messaging->send($fcmMessage);
                        Log::info('FCM sent to user', ['user_id' => $user->id]);
                    } catch (\Exception $e) {
                        Log::error('FCM send error', ['error' => $e->getMessage()]);
                    }
                }
            }

            // Gửi email thông báo khi trạng thái chuyển thành "Chờ ký"
            if ($status === 'Chờ ký' && $oldStatus !== 'Chờ ký') {
                $this->sendContractSignEmail($contract);
                // Tạo thông báo cho người dùng
                $notificationdata = [
                    'user_id' => $contract->user_id,
                    'title' => 'Hợp đồng cần ký',
                    'content' => 'Hợp đồng của bạn cần ký. Vui lòng kiểm tra email để biết chi tiết.',
                    'status' => 'Chưa đọc'
                ];
                $notification = Notification::create($notificationdata);
                Log::info('Notification created for contract sign', [
                    'contract_id' => $contract->id,
                    'notification_id' => $notification->id
                ]);

                // gửi FCM token
                $user = User::find($notificationdata['user_id']);

                if ($user && $user->fcm_token) {
                    $messaging = app('firebase.messaging');

                    $fcmMessage = CloudMessage::withTarget('token', $user->fcm_token)
                        ->withNotification(FirebaseNotification::create(
                            $notificationdata['title'],
                            $notificationdata['content']
                        ));

                    try {
                        $messaging->send($fcmMessage);
                        Log::info('FCM sent to user', ['user_id' => $user->id]);
                    } catch (\Exception $e) {
                        Log::error('FCM send error', ['error' => $e->getMessage()]);
                    }
                }
            }

            // Gửi email thông báo khi trạng thái chuyển thành "Hoạt động"
            if ($status === 'Hoạt động' && $oldStatus !== 'Hoạt động') {
                $this->sendContractConfirmEmail($contract);
                // Tạo thông báo cho người dùng
                $notificationdata = [
                    'user_id' => $contract->user_id,
                    'title' => 'Hợp đồng đã được xác nhận',
                    'content' => 'Hợp đồng của bạn đã được xác nhận và đang hoạt động.',
                    'status' => 'Chưa đọc'
                ];
                $notification = Notification::create($notificationdata);
                Log::info('Notification created for contract confirmation', [
                    'contract_id' => $contract->id,
                    'notification_id' => $notification->id
                ]);

                // gửi FCM token
                $user = User::find($notificationdata['user_id']);

                if ($user && $user->fcm_token) {
                    $messaging = app('firebase.messaging');

                    $fcmMessage = CloudMessage::withTarget('token', $user->fcm_token)
                        ->withNotification(FirebaseNotification::create(
                            $notificationdata['title'],
                            $notificationdata['content']
                        ));

                    try {
                        $messaging->send($fcmMessage);
                        Log::info('FCM sent to user', ['user_id' => $user->id]);
                    } catch (\Exception $e) {
                        Log::error('FCM send error', ['error' => $e->getMessage()]);
                    }
                }
            }

            return ['data' => $contract];
        } catch (\Throwable $e) {
            Log::error('Lỗi khi cập nhật trạng thái hợp đồng: ' . $e->getMessage(), [
                'contract_id' => $id,
                'status' => $status
            ]);
            return ['error' => 'Đã xảy ra lỗi khi cập nhật trạng thái hợp đồng', 'status' => 500];
        }
    }

    // Gửi email thông báo khi hợp đồng cần chỉnh sửa
    private function sendContractRevisionEmail(Contract $contract): void
    {
        try {
            if (!$contract->user || !$contract->user->email) {
                Log::warning('Không thể gửi email sửa đổi - không tìm thấy người dùng hoặc email', [
                    'contract_id' => $contract->id
                ]);
                return;
            }

            // Sử dụng Mailable class mới
            Mail::to($contract->user->email, $contract->user->name)
                ->send(new ContractRevisionNotification($contract));

            Log::info('Email sửa đổi hợp đồng đã được gửi thành công', [
                'contract_id' => $contract->id,
                'user_email' => $contract->user->email
            ]);

        } catch (\Throwable $e) {
            Log::error('Lỗi khi gửi email sửa đổi hợp đồng: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
                'user_email' => $contract->user->email ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    // Gửi email thông báo khi hợp đồng cần ký
    private function sendContractSignEmail(Contract $contract): void
    {
        try {
            if (!$contract->user || !$contract->user->email) {
                Log::warning('Không thể gửi email đăng nhập - không tìm thấy người dùng hoặc email', [
                    'contract_id' => $contract->id
                ]);
                return;
            }

            // Sử dụng Mailable class mới
            Mail::to($contract->user->email, $contract->user->name)
                ->send(new ContractSignNotification($contract));

            Log::info('Email ký hợp đồng đã được gửi thành công', [
                'contract_id' => $contract->id,
                'user_email' => $contract->user->email
            ]);

        } catch (\Throwable $e) {
            Log::error('Lỗi khi gửi email ký hợp đồng: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
                'user_email' => $contract->user->email ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    // Gửi email thông báo khi hợp đồng đã được xác nhận
    private function sendContractConfirmEmail(Contract $contract): void
    {
        try {
            if (!$contract->user || !$contract->user->email) {
                Log::warning('Không thể gửi email xác nhận - không tìm thấy người dùng hoặc email', [
                    'contract_id' => $contract->id
                ]);
                return;
            }

            // Sử dụng Mailable class mới
            Mail::to($contract->user->email, $contract->user->name)
                ->send(new ContractConfirmNotification($contract));

            Log::info('Email xác nhận hợp đồng đã được gửi thành công', [
                'contract_id' => $contract->id,
                'user_email' => $contract->user->email
            ]);

        } catch (\Throwable $e) {
            Log::error('Lỗi khi gửi email xác nhận hợp đồng: ' . $e->getMessage(), [
                'contract_id' => $contract->id,
                'user_email' => $contract->user->email ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    // Tải file PDF hợp đồng
    public function downloadContractPdf(int $id): array
    {
        try {
            $contract = Contract::find($id);

            if (!$contract) {
                return ['error' => 'Không tìm thấy hợp đồng', 'status' => 404];
            }

            if (!$contract->file) {
                return ['error' => 'File PDF chưa được tạo cho hợp đồng này', 'status' => 404];
            }

            // Đường dẫn file PDF trong thư mục private/pdf/contracts
            $pdfPath = $contract->file;

            // Kiểm tra file tồn tại trong storage/app
            if (!Storage::disk('local')->exists($pdfPath)) {
                return ['error' => 'File PDF không tồn tại trên hệ thống', 'status' => 404];
            }

            $filePath = Storage::disk('local')->path($pdfPath);

            return [
                'data' => [
                    'file_path' => $filePath,
                    'file_name' => "Hop_dong_cho_thue_{$contract->id}.pdf",
                    'mime_type' => 'application/pdf',
                    'storage_path' => $pdfPath,
                    'relative_path' => $contract->file
                ]
            ];

        } catch (\Throwable $e) {
            Log::error('Error downloading contract PDF: ' . $e->getMessage(), [
                'contract_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            return ['error' => 'Đã xảy ra lỗi khi tải file PDF'];
        }
    }

    // Lấy hình ảnh căn cước công dân từ hợp đồng
    public function getIdentityDocument(int $contractId, string $imagePath): array
    {
        try {
            $contract = Contract::with('user')->find($contractId);

            if (!$contract) {
                return ['error' => 'Không tìm thấy hợp đồng', 'status' => 404];
            }

            if (!$contract->user || !$contract->user->identity_document) {
                return ['error' => 'Không tìm thấy hình ảnh căn cước công dân', 'status' => 404];
            }

            $imagePaths = explode('|', $contract->user->identity_document);
            $fullImagePath = 'images/identity_document/' . $imagePath;

            if (!in_array($fullImagePath, $imagePaths)) {
                return ['error' => 'Hình ảnh không hợp lệ', 'status' => 404];
            }

            $encryptedContent = Storage::disk('public')->get($fullImagePath);
            $decryptedContent = decrypt($encryptedContent);

            return [
                'data' => [
                    'content' => $decryptedContent,
                    'mime_type' => 'image/webp'
                ]
            ];

        } catch (\Throwable $e) {
            Log::error('Error retrieving identity document: ' . $e->getMessage(), [
                'contract_id' => $contractId,
                'image_path' => $imagePath
            ]);
            return ['error' => 'Đã xảy ra lỗi khi lấy hình ảnh căn cước công dân', 'status' => 500];
        }
    }
}
