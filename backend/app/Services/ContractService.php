<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Invoice;
use App\Models\Config;
use App\Jobs\SendContractRevisionNotification;
use App\Jobs\SendContractSignNotification;
use App\Jobs\SendContractConfirmNotification;
use App\Jobs\SendContractEarlyTerminationNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ContractService
{
    public function getAllContracts(string $querySearch = '', string $status = '', string $sort = 'desc'): array
    {
        try {
            DB::enableQueryLog();
            $query = Contract::with(['user', 'room', 'booking']);

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

            $sortDirection = in_array($sort, ['asc', 'desc']) ? $sort : 'desc';
            $contracts = $query->orderBy('created_at', $sortDirection)->paginate(15);
            Log::info('SQL Query', DB::getQueryLog());
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

    public function getContractsEndingSoon(): array
    {
        try {
            $today = Carbon::now()->startOfDay();
            $oneMonthFromNow = $today->copy()->addMonth()->endOfDay();

            Log::info('Getting contracts ending soon', [
                'from_date' => $today->toDateString(),
                'to_date' => $oneMonthFromNow->toDateString()
            ]);

            $contracts = Contract::with(['user', 'room', 'booking'])
                ->where('status', 'Hoạt động')
                ->whereDate('end_date', '>=', $today)
                ->whereDate('end_date', '<=', $oneMonthFromNow)
                ->orderBy('end_date', 'asc')
                ->get();

            if ($contracts->isEmpty()) {
                return ['message' => 'Không có hợp đồng nào sắp hết hạn trong vòng 1 tháng tới', 'status' => 200, 'data' => []];
            }

            return ['data' => $contracts];
        } catch (\Throwable $e) {
            Log::error('Error getting contracts ending soon: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return ['error' => 'Đã xảy ra lỗi khi lấy danh sách hợp đồng sắp hết hạn', 'status' => 500];
        }
    }

    public function signedContracts(): array
    {
        try {
            $today = Carbon::today();
            $contracts = Contract::with(['user', 'room', 'booking'])
                ->whereDate('signed_at', $today)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($contracts->isEmpty()) {
                return ['message' => 'Không có hợp đồng nào được ký hôm nay', 'status' => 200, 'data' => []];
            }

            return ['data' => $contracts];
        } catch (\Throwable $e) {
            Log::error('Error getting signed contracts: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return ['error' => 'Đã xảy ra lỗi khi lấy danh sách hợp đồng đã ký', 'status' => 500];
        }
    }

    public function getContractById($id): array
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

            if ($status === 'Kết thúc' && $oldStatus !== 'Kết thúc') {
                if ($contract->user) {
                    // Update user role to "Người đăng ký"
                    $contract->user->update(['role' => 'Người đăng ký']);

                    // Remove identity docume
                    $contract->room->update(['status' => 'Sửa chữa']);
                    if ($contract->user->identity_document) {
                        $imagePaths = explode('|', $contract->user->identity_document);
                        foreach ($imagePaths as $index => $imagePath) {
                            // Delete the file from storage
                            if (Storage::disk('public')->exists($imagePath)) {
                                Storage::disk('public')->delete($imagePath);
                                Log::info('Deleted identity document file', [
                                    'user_id' => $contract->user->id,
                                    'file_path' => $imagePath
                                ]);
                            }
                        }
                        // Clear the identity_document field in the user model
                        $contract->user->update(['identity_document' => null]);
                        Log::info('User identity document cleared', [
                            'user_id' => $contract->user->id,
                            'contract_id' => $contract->id
                        ]);
                    }

                    Log::info('User status updated to "Người đăng ký"', [
                        'user_id' => $contract->user->id,
                        'contract_id' => $contract->id
                    ]);
                }
            }

            // Gửi thông báo khi trạng thái chuyển thành "Chờ ký" bằng Job
            if ($status === 'Chờ ký' && $oldStatus !== 'Chờ ký') {
                SendContractSignNotification::dispatch($contract);

                Log::info('Contract sign notification job dispatched', [
                    'contract_id' => $contract->id,
                    'user_id' => $contract->user_id,
                ]);
            }

            // Gửi thông báo khi trạng thái chuyển thành "Hoạt động" bằng Job
            if ($status === 'Hoạt động' && $oldStatus !== 'Hoạt động') {
                SendContractConfirmNotification::dispatch($contract);

                Log::info('Contract confirmation notification job dispatched', [
                    'contract_id' => $contract->id,
                    'user_id' => $contract->user_id,
                ]);
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

            $pdfPath = $contract->file;

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

    public function getTenantsByContractStatus()
    {
        $allTenants = Contract::with(['user', 'room'])
            ->whereIn('status', ['Hoạt động', 'Kết thúc'])
            ->get();

        $today = Carbon::today();
        $currentTenants = [];
        $expiringTenants = [];
        $expiredTenants = [];

        foreach ($allTenants as $tenant) {
            $endDate = Carbon::parse($tenant->end_date);

            if ($tenant->status === 'Kết thúc' || $endDate->lt($today)) {
                $expiredTenants[] = $tenant;
                continue;
            }

            $daysLeft = $today->diffInDays($endDate, false);

            if ($daysLeft > 7) {
                $currentTenants[] = $tenant;
            } elseif ($daysLeft >= 0 && $daysLeft <= 7) {
                $expiringTenants[] = $tenant;
            }
        }

        return [
            'current' => $currentTenants,
            'expiring' => $expiringTenants,
            'expired' => $expiredTenants,
        ];
    }

    public function sendRevisionEmail($contractId, string $revisionReason): array
    {
        try {
            $contract = Contract::with(['user', 'room', 'booking'])->findOrFail($contractId);

            if (!$contract->user || !$contract->user->email) {
                Log::error('User or email not found for contract', [
                    'contract_id' => $contractId
                ]);
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin người dùng hoặc email.',
                    'status' => 404
                ];
            }

            $contract->update(['status' => 'Chờ xác nhận']);

            SendContractRevisionNotification::dispatch($contract, $revisionReason);

            Log::info('Contract revision notification job dispatched', [
                'contract_id' => $contract->id,
                'user_id' => $contract->user_id,
                'revision_reason' => $revisionReason
            ]);

            return [
                'success' => true,
                'message' => 'Yêu cầu chỉnh sửa đã được gửi thành công!'
            ];
        } catch (\Throwable $e) {
            Log::error('Error dispatching contract revision notification job: ' . $e->getMessage(), [
                'contract_id' => $contractId,
                'revision_reason' => $revisionReason,
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi gửi yêu cầu chỉnh sửa.',
                'status' => 500
            ];
        }
    }

    public function terminateContractEarly($contractId, string $terminationReason = null): array
    {
        try {
            $contract = Contract::with(['user', 'room', 'booking'])->findOrFail($contractId);

            if (!$contract->user || !$contract->user->email) {
                Log::error('User or email not found for contract early termination', [
                    'contract_id' => $contractId
                ]);
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin người dùng hoặc email.',
                    'status' => 404
                ];
            }

            // Kiểm tra hóa đơn quá hạn
            if (!$this->checkOverdueInvoices($contractId)) {
                Log::warning('Attempt to terminate contract early without overdue invoices', [
                    'contract_id' => $contractId
                ]);
                return [
                    'success' => false,
                    'message' => 'Hợp đồng không thể kết thúc sớm.',
                    'status' => 400
                ];
            }

            // Cập nhật trạng thái hợp đồng thành "Kết thúc sớm"
            $contract->update(['status' => 'Kết thúc sớm']);

            // Xử lý khi kết thúc hợp đồng sớm
            if ($contract->user) {
                // Update user role to "Người đăng ký"
                $contract->user->update(['role' => 'Người đăng ký']);

                // Update room status
                if ($contract->room) {
                    $contract->room->update(['status' => 'Sửa chữa']);
                }

                // Remove identity documents from user and storage
                if ($contract->user->identity_document) {
                    $imagePaths = explode('|', $contract->user->identity_document);
                    foreach ($imagePaths as $index => $imagePath) {
                        // Delete the file from storage
                        if (Storage::disk('public')->exists($imagePath)) {
                            Storage::disk('public')->delete($imagePath);
                            Log::info('Deleted identity document file', [
                                'user_id' => $contract->user->id,
                                'file_path' => $imagePath
                            ]);
                        }
                    }
                    // Clear the identity_document field in the user model
                    $contract->user->update(['identity_document' => null]);
                    Log::info('User identity document cleared', [
                        'user_id' => $contract->user->id,
                        'contract_id' => $contract->id
                    ]);
                }

                Log::info('User status updated to "Người đăng ký"', [
                    'user_id' => $contract->user->id,
                    'contract_id' => $contract->id
                ]);
            }

            // Gửi thông báo kết thúc hợp đồng sớm bằng Job
            SendContractEarlyTerminationNotification::dispatch($contract, $terminationReason);

            Log::info('Contract early termination notification job dispatched', [
                'contract_id' => $contract->id,
                'user_id' => $contract->user_id,
                'termination_reason' => $terminationReason
            ]);

            return [
                'success' => true,
                'message' => 'Hợp đồng đã được kết thúc sớm và email thông báo đã được gửi thành công!'
            ];
        } catch (\Throwable $e) {
            Log::error('Error terminating contract early: ' . $e->getMessage(), [
                'contract_id' => $contractId,
                'termination_reason' => $terminationReason,
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi kết thúc hợp đồng sớm.',
                'status' => 500
            ];
        }
    }

    public function updateContractContent(Contract $contract, array $data): string
    {
        // Get the current content
        $newContent = $contract->content ?? '';

        // If no data is provided, return the original content
        if (empty($data)) {
            return $newContent;
        }

        // Use DOMDocument to parse and update the HTML content
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true); // Suppress warnings for malformed HTML
        if (!$dom->loadHTML('<?xml encoding="UTF-8">' . $newContent, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
            Log::warning('Failed to load HTML content', ['contract_id' => $contract->id, 'content' => $newContent]);
            return $newContent; // Return original if parsing fails
        }

        // Find all input elements
        $inputs = $dom->getElementsByTagName('input');
        foreach ($inputs as $input) {
            $name = $input->getAttribute('name');
            if (isset($data[$name]) && $data[$name] !== null) {
                // Preserve existing attributes and update only the value
                $newValue = htmlspecialchars($data[$name], ENT_QUOTES, 'UTF-8');
                $input->setAttribute('value', $newValue);

                // Ensure class and style attributes are not lost
                $class = $input->getAttribute('class');
                $style = $input->getAttribute('style');
                if ($class) $input->setAttribute('class', $class);
                if ($style) $input->setAttribute('style', $style);
            }
        }

        // Get the updated HTML content
        $updatedHtml = $dom->saveHTML();

        // Remove the DOCTYPE, XML declaration, and extra HTML/body tags
        $newContent = preg_replace('/^<!DOCTYPE.*?(?=>)>|<html>|<body>|<\/body>|<\/html>/i', '', $updatedHtml);
        $newContent = trim($newContent);

        // Log before and after for debugging
        Log::info('Contract content updated', [
            'contract_id' => $contract->id,
            'original_content' => $contract->content,
            'new_content' => $newContent
        ]);

        // Update the contract in the database
        $contract->content = $newContent;
        $contract->save();

        return $newContent;
    }
    public function checkOverdueInvoices($contractId): bool
    {
        try {
            $today = Carbon::today();
            // Giả định có model Config để lấy ngày quá hạn
            $overdueDays = (int) Config::getValue('date_end_contract');
            $overdueDate = $today->subDays($overdueDays);
            // dd($overdueDays);
            // Giả định có model Invoice liên kết với Contract
            $overdueInvoices = Invoice::where('contract_id', $contractId)
                ->where('status', 'Chưa trả')
                ->whereDate('created_at', '<=', $overdueDate)
                ->exists();

            return $overdueInvoices;
        } catch (\Throwable $e) {
            Log::error('Error checking overdue invoices: ' . $e->getMessage(), [
                'contract_id' => $contractId
            ]);
            return false;
        }
    }


}
