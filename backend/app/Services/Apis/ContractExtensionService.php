<?php

namespace App\Services\Apis;

use App\Models\ContractExtension;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ContractExtensionService
{
    public function rejectContractExtension(int $id): array
    {
        try {
            $contractExtension = ContractExtension::findOrFail($id);

            // Kiểm tra quyền người dùng
            if ($contractExtension->contract->user_id !== Auth::id()) {
                return [
                    'error' => 'Bạn không có quyền hủy gia hạn này',
                    'status' => 403,
                ];
            }

            if ($contractExtension->status !== 'Chờ duyệt') {
                return [
                    'error' => 'Gia hạn/phụ lục hợp đồng không ở trạng thái có thể hủy',
                    'status' => 400,
                ];
            }

            $contractExtension->update(['status' => 'Huỷ bỏ']);

            return [
                'data' => $contractExtension,
                'status' => 200,
            ];
        } catch (\Throwable $e) {
            Log::error('Lỗi hủy gia hạn', [
                'contract_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
