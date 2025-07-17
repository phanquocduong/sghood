<?php

namespace App\Services\Apis;

use App\Models\Contract;
use App\Models\ContractExtension;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ContractExtensionService
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    private function generateExtensionContent(Contract $contract, Carbon $newEndDate): string
    {
        return '
            <div class="contract-document">
                <p><strong>Hợp đồng số: </strong>' . $contract->id . '</p>
                <p><strong>Ngày gia hạn: </strong>' . now()->format('d/m/Y') . '</p>
                <p><strong>Ngày kết thúc mới: </strong><span class="end-date">' . $newEndDate->format('d/m/Y') . '</span></p>
                <p><strong>Giá thuê mới: </strong>' . number_format($contract->room->price, 0, ',', '.') . ' VND</p>
                <p><em>Các điều khoản khác của hợp đồng gốc vẫn giữ nguyên hiệu lực.</em></p>
            </div>
        ';
    }

    public function extendContract(int $id, int $months): array
    {
        try {
            $contract = Contract::where('id', $id)
                ->where('user_id', Auth::id())
                ->where('status', 'Hoạt động')
                ->first();

            if (!$contract) {
                return [
                    'error' => 'Không tìm thấy hợp đồng hoặc bạn không có quyền gia hạn',
                    'status' => 404,
                ];
            }

            $endDate = Carbon::parse($contract->end_date);
            $today = Carbon::today();
            $diffInDays = $endDate->diffInDays($today);

            if ($diffInDays > 15) {
                return [
                    'error' => 'Hợp đồng chưa đến thời điểm có thể gia hạn (cần trong vòng 15 ngày trước khi hết hạn)',
                    'status' => 400,
                ];
            }

            if ($months < 1) {
                return [
                    'error' => 'Thời gian gia hạn phải ít nhất là 1 tháng',
                    'status' => 400,
                ];
            }

            // Gia hạn hợp đồng thêm số tháng được truyền vào
            $newEndDate = $endDate->addMonths($months);

            // Tạo phụ lục hợp đồng
            $extensionContent = $this->generateExtensionContent($contract, $newEndDate);
            $extension = ContractExtension::create([
                'contract_id' => $contract->id,
                'new_end_date' => $newEndDate,
                'new_rental_price' => $contract->room->price,
                'content' => $extensionContent,
                'status' => 'Chờ duyệt',
            ]);

            // Gửi thông báo với ngữ cảnh "Gia hạn"
            $this->notificationService->notifyContractForAdmins($contract, 'Gia hạn');

            return [
                'data' => $contract,
                'extension_id' => $extension->id,
                'status' => 200,
            ];
        } catch (\Throwable $e) {
            Log::error('Lỗi gia hạn hợp đồng', [
                'contract_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function getExtensions(array $filters)
    {
        $query = ContractExtension::query()
            ->with('contract') // Tải quan hệ contract
            ->whereHas('contract', fn($q) => $q->where('user_id', Auth::id())); // Lọc theo user_id của contract

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
