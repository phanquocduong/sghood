<?php

namespace App\Services\Apis;

use App\Models\ContractExtension;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ContractExtensionService
{
    public function getExtensions(array $filters)
    {
        $query = ContractExtension::query()
            ->with('contract') // Tải quan hệ contract
            ->whereHas('contract', fn($q) => $q->where('user_id', Auth::id())); // Lọc theo user_id của contrac

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->orderBy('created_at', $this->getSortOrder($filters['sort'] ?? 'default'));

        $extensions = $query->get()->map(function ($extension) {
            return [
                'id' => $extension->id,
                'contract_id' => $extension->contract_id,
                'new_end_date' => $extension->new_end_date ? $extension->new_end_date->toIso8601String() : null,
                'new_rental_price' => $extension->new_rental_price,
                'content' => $extension->content,
                'status' => $extension->status,
                'rejection_reason' => $extension->rejection_reason,
            ];
        });

        return $extensions;
    }

    protected function getSortOrder($sort)
    {
        return match ($sort) {
            'oldest' => 'asc',
            'latest', 'default' => 'desc',
            default => 'desc',
        };
    }

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
