<?php
namespace App\Services;

use App\Models\Contract;
use Illuminate\Support\Facades\Log;

class ContractService
{
    public function getAllContracts(string $querySearch = '', string $status = '', int $perPage = 10): array
    {
        try {
            $query = Contract::with(['user', 'room', 'booking']);

            // Apply search filter
            if ($querySearch) {
                $query->where(function ($q) use ($querySearch) {
                    $q->where('content', 'like', "%$querySearch%")
                      ->orWhere('file', 'like', "%$querySearch%")
                      ->orWhereHas('user', function($userQuery) use ($querySearch) {
                          $userQuery->where('name', 'like', "%$querySearch%");
                      })
                      ->orWhereHas('room', function($roomQuery) use ($querySearch) {
                          $roomQuery->where('name', 'like', "%$querySearch%");
                      });
                });
            }

            if ($status) {
                $query->where('status', $status);
            }

            $contracts = $query->orderBy('created_at', 'desc')->paginate($perPage);
            return ['data' => $contracts];
        } catch (\Throwable $e) {
            Log::error('Error getting contracts: ' . $e->getMessage(), [
                'query_search' => $querySearch,
                'status' => $status,
                'per_page' => $perPage
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

            return ['data' => $contract];
        } catch (\Throwable $e) {
            Log::error('Error updating contract status: ' . $e->getMessage(), [
                'contract_id' => $id,
                'status' => $status
            ]);
            return ['error' => 'Đã xảy ra lỗi khi cập nhật trạng thái hợp đồng', 'status' => 500];
        }
    }
}
