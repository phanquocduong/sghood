<?php

namespace App\Services\Apis;

use App\Jobs\Apis\SendContractExtensionNotification;
use App\Models\Config;
use App\Models\Contract;
use App\Models\ContractExtension;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Dịch vụ xử lý logic nghiệp vụ liên quan đến gia hạn hợp đồng.
 */
class ContractExtensionService
{
    /**
     * Khởi tạo dịch vụ với NotificationService.
     *
     * @param NotificationService $notificationService Dịch vụ xử lý thông báo
     */
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * Tạo nội dung HTML cho yêu cầu gia hạn hợp đồng.
     *
     * @param Contract $contract Mô hình hợp đồng
     * @param Carbon $newEndDate Ngày kết thúc mới
     * @return string Nội dung HTML của phụ lục gia hạn
     */
    private function generateExtensionContent(Contract $contract, Carbon $newEndDate): string
    {
        // Tạo nội dung HTML với thông tin hợp đồng và ngày kết thúc mới
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

    /**
     * Gia hạn hợp đồng theo số tháng được chỉ định.
     *
     * @param int $id ID của hợp đồng
     * @param int $months Số tháng gia hạn
     * @return array Kết quả với dữ liệu hoặc lỗi
     */
    public function extendContract(int $id, int $months): array
    {
        try {
            // Tìm hợp đồng đang hoạt động của người dùng hiện tại
            $contract = Contract::query()
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->where('status', 'Hoạt động')
                ->first();

            // Kiểm tra xem hợp đồng có tồn tại không
            if (!$contract) {
                return [
                    'error' => 'Không tìm thấy hợp đồng hoặc bạn không có quyền gia hạn',
                    'status' => 404,
                ];
            }

            // Lấy ngày kết thúc hiện tại và ngày hiện tại
            $endDate = Carbon::parse($contract->end_date);
            $today = Carbon::today();

            // Lấy giá trị cấu hình thời gian cho phép gia hạn
            $isNearExpiration = Config::getValue('is_near_expiration', 15);

            // Kiểm tra điều kiện thời gian gia hạn nếu is_near_expiration không phải -1
            if ($isNearExpiration !== -1) {
                $diffInDays = $endDate->diffInDays($today);
                if ($diffInDays > $isNearExpiration) {
                    return [
                        'error' => "Hợp đồng chưa đến thời điểm có thể gia hạn (cần trong vòng {$isNearExpiration} ngày trước khi hết hạn)",
                        'status' => 400,
                    ];
                }
            }

            // Kiểm tra số tháng gia hạn
            if ($months < 1) {
                return [
                    'error' => 'Thời gian gia hạn phải ít nhất là 1 tháng',
                    'status' => 400,
                ];
            }

            // Tính ngày kết thúc mới
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

            // Gửi thông báo yêu cầu gia hạn đến quản trị viên
            SendContractExtensionNotification::dispatch(
                $extension,
                'pending',
                'Yêu cầu gia hạn hợp đồng #' . $extension->id,
                "Người dùng {$contract->user->name} đã yêu cầu gia hạn hợp đồng #{$contract->id} cho nhà trọ {$contract->room->motel->name} đến ngày {$newEndDate->format('d/m/Y')}."
            );

            // Trả về dữ liệu hợp đồng và ID yêu cầu gia hạn
            return [
                'data' => $contract,
                'extension_id' => $extension->id,
            ];
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi gia hạn hợp đồng:' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Lấy danh sách các yêu cầu gia hạn hợp đồng của người dùng hiện tại.
     *
     * @return \Illuminate\Support\Collection Danh sách yêu cầu gia hạn đã định dạng
     */
    public function getExtensions()
    {
        // Tạo query lấy danh sách yêu cầu gia hạn của người dùng hiện tại
        $query = ContractExtension::query()
            ->with('contract') // Nạp thông tin hợp đồng liên quan
            ->whereHas('contract', fn($q) => $q->where('user_id', Auth::id()));

        // Sắp xếp theo thời gian tạo giảm dần
        $query->orderBy('created_at', 'desc');

        // Định dạng dữ liệu trả về
        $extensions = $query->get()->map(function ($extension) {
            return [
                'id' => $extension->id,
                'contract_id' => $extension->contract_id, // ID hợp đồng
                'new_end_date' => $extension->new_end_date ? $extension->new_end_date->toIso8601String() : null, // Ngày kết thúc mới
                'new_rental_price' => $extension->new_rental_price, // Giá thuê mới
                'content' => $extension->content, // Nội dung phụ lục
                'status' => $extension->status, // Trạng thái phụ lục
                'rejection_reason' => $extension->rejection_reason, // Lý do từ chối (nếu có)
            ];
        });

        return $extensions;
    }

    /**
     * Hủy yêu cầu gia hạn hợp đồng.
     *
     * @param int $id ID của yêu cầu gia hạn
     * @return array Kết quả với dữ liệu hoặc lỗi
     */
    public function cancelContractExtension(int $id): array
    {
        try {
            // Tìm yêu cầu gia hạn
            $contractExtension = ContractExtension::findOrFail($id);

            // Kiểm tra quyền người dùng
            if ($contractExtension->contract->user_id !== Auth::id()) {
                return [
                    'error' => 'Bạn không có quyền hủy gia hạn này',
                    'status' => 403,
                ];
            }

            // Kiểm tra trạng thái yêu cầu gia hạn
            if ($contractExtension->status !== 'Chờ duyệt') {
                return [
                    'error' => 'Gia hạn/phụ lục hợp đồng không ở trạng thái có thể hủy',
                    'status' => 400,
                ];
            }

            // Cập nhật trạng thái yêu cầu gia hạn thành "Huỷ bỏ"
            $contractExtension->update(['status' => 'Huỷ bỏ']);

            // Gửi thông báo hủy yêu cầu gia hạn đến quản trị viên
            SendContractExtensionNotification::dispatch(
                $contractExtension,
                'canceled',
                'Gia hạn hợp đồng #' . $contractExtension->id . ' đã bị hủy',
                "Người dùng {$contractExtension->contract->user->name} đã hủy yêu cầu gia hạn hợp đồng #{$contractExtension->contract_id} cho nhà trọ {$contractExtension->contract->room->motel->name}."
            );

            // Trả về dữ liệu yêu cầu gia hạn đã cập nhật
            return [
                'data' => $contractExtension,
                'status' => 200,
            ];
        } catch (\Throwable $e) {
            // Ghi log lỗi nếu có ngoại lệ xảy ra
            Log::error('Lỗi hủy gia hạn:' . $e->getMessage());
            throw $e;
        }
    }
}
