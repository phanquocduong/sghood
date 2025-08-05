<?php
namespace App\Services;

use App\Models\ContractExtension;
use App\Jobs\SendContractExtensionApprovedNotification;
use App\Jobs\SendContractExtensionRejectedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ContractExtensionService
{
    public function getAllContractExtensions(string $querySearch = '', string $status = '', string $sort = 'desc'): array
    {
        try {
            DB::enableQueryLog();
            $query = ContractExtension::with(['contract', 'contract.user', 'contract.room']);

            // Apply search filter by room name only
            if (!empty($querySearch)) {
            $query->whereHas('contract', function ($q) use ($querySearch) {
            // If query is exactly "hd" or "HD", show all contracts
            if (strtolower($querySearch) === 'hd') {
                // No additional filtering - show all contracts
                return;
            }

            // If query starts with HD or hd, extract the numeric part
            $numericQuery = $querySearch;
            if (preg_match('/^hd(\d+)$/i', $querySearch, $matches)) {
                $numericQuery = $matches[1];
            }

            $q->where('id', 'like', '%' . $querySearch . '%')
              ->orWhere('id', 'like', '%' . $numericQuery . '%');
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

            // Gửi thông báo khi trạng thái thay đổi bằng Jobs
            if ($status === 'Hoạt động' && $oldStatus !== 'Hoạt động') {
                SendContractExtensionApprovedNotification::dispatch($contractExtension);

                Log::info('Contract extension approval notification job dispatched', [
                    'contract_extension_id' => $contractExtension->id,
                    'user_id' => $contractExtension->contract->user_id,
                ]);
            } elseif ($status === 'Từ chối' && $oldStatus !== 'Từ chối') {
                SendContractExtensionRejectedNotification::dispatch($contractExtension, $rejectionReason);

                Log::info('Contract extension rejection notification job dispatched', [
                    'contract_extension_id' => $contractExtension->id,
                    'user_id' => $contractExtension->contract->user_id,
                    'rejection_reason' => $rejectionReason ?? 'Không có lý do cụ thể'
                ]);
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
