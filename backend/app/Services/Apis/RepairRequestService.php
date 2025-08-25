<?php

namespace App\Services\Apis;

use App\Jobs\Apis\SendRepairRequestNotification;
use App\Models\Contract;
use App\Models\RepairRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

/**
 * Dịch vụ xử lý logic nghiệp vụ liên quan đến yêu cầu sửa chữa.
 */
class RepairRequestService
{
    /**
     * Lấy danh sách tất cả yêu cầu sửa chữa của người dùng.
     *
     * @param int $userId ID của người dùng
     * @return Collection Danh sách yêu cầu sửa chữa
     */
    public function getUserRepairRequests(int $userId): Collection
    {
        // Truy vấn các yêu cầu sửa chữa của người dùng dựa trên hợp đồng
        return RepairRequest::whereHas('contract', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with('contract')->get(); // Lấy thông tin hợp đồng liên quan
    }

    /**
     * Tạo mới một yêu cầu sửa chữa.
     *
     * @param int $userId ID của người dùng
     * @param array $data Dữ liệu đã xác thực từ yêu cầu
     * @return RepairRequest Mô hình yêu cầu sửa chữa vừa tạo
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Nếu không tìm thấy hợp đồng
     */
    public function createRepairRequest(int $userId, array $data): RepairRequest
    {
        // Sử dụng giao dịch để đảm bảo tính toàn vẹn dữ liệu
        return DB::transaction(function () use ($userId, $data) {
            // Tìm hợp đồng đang hoạt động của người dùng
            $contract = Contract::where('user_id', $userId)
                ->where('status', 'Hoạt động')
                ->firstOrFail();

            // Xử lý upload ảnh
            $imagePaths = [];
            if (isset($data['images']) && is_array($data['images'])) {
                foreach ($data['images'] as $image) {
                    if ($image->isValid()) {
                        // Chuyển đổi ảnh sang định dạng WebP
                        $imageManager = new ImageManager(new Driver());
                        $imageWebp = $imageManager->read($image)->toWebp(quality: 85);

                        // Tạo tên file duy nhất
                        $fileName = uniqid('repair_') . '.webp';
                        $path = 'images/repair_requests/' . $fileName;

                        // Lưu ảnh vào storage
                        Storage::disk('public')->put($path, $imageWebp->toString());

                        // Thêm URL công khai vào mảng
                        $imagePaths[] = Storage::url($path);
                    }
                }
            }

            // Chuyển mảng đường dẫn ảnh thành chuỗi ngăn cách bằng |
            $imagesString = !empty($imagePaths) ? implode('|', $imagePaths) : null;

            // Tạo yêu cầu sửa chữa
            $repairRequest = RepairRequest::create([
                'contract_id' => $contract->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'images' => $imagesString,
                'status' => 'Chờ xác nhận',
            ]);

            // Gửi thông báo đến quản trị viên
            SendRepairRequestNotification::dispatch(
                $repairRequest,
                'pending',
                'Yêu cầu sửa chữa mới #' . $repairRequest->id,
                "Người dùng {$repairRequest->contract->user->name} đã tạo yêu cầu sửa chữa cho phòng {$repairRequest->contract->room->name} tại {$repairRequest->contract->room->motel->name}."
            );

            return $repairRequest;
        });
    }

    /**
     * Hủy một yêu cầu sửa chữa.
     *
     * @param int $userId ID của người dùng
     * @param int $repairRequestId ID của yêu cầu sửa chữa
     * @return RepairRequest Mô hình yêu cầu sửa chữa đã hủy
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException Nếu không tìm thấy yêu cầu
     * @throws \Exception Nếu trạng thái không cho phép hủy
     */
    public function cancelRepairRequest(int $userId, int $repairRequestId): RepairRequest
    {
        // Sử dụng giao dịch để đảm bảo tính toàn vẹn dữ liệu
        return DB::transaction(function () use ($userId, $repairRequestId) {
            // Tìm yêu cầu sửa chữa và kiểm tra quyền sở hữu
            $repairRequest = RepairRequest::where('id', $repairRequestId)
                ->whereHas('contract', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })->firstOrFail();

            // Kiểm tra trạng thái hiện tại của yêu cầu
            if ($repairRequest->status === 'Huỷ bỏ') {
                throw new \Exception('Yêu cầu sửa chữa đã được hủy trước đó');
            }

            if ($repairRequest->status === 'Đang thực hiện') {
                throw new \Exception('Không thể hủy yêu cầu sửa chữa khi đang thực hiện');
            }

            if ($repairRequest->status === 'Hoàn thành') {
                throw new \Exception('Không thể hủy yêu cầu sửa chữa đã hoàn thành');
            }

            // Cập nhật trạng thái thành 'Huỷ bỏ'
            $repairRequest->update(['status' => 'Huỷ bỏ']);

            // Gửi thông báo đến quản trị viên
            SendRepairRequestNotification::dispatch(
                $repairRequest,
                'canceled',
                'Yêu cầu sửa chữa #' . $repairRequest->id . ' đã bị hủy',
                "Người dùng {$repairRequest->contract->user->name} đã hủy yêu cầu sửa chữa cho phòng {$repairRequest->contract->room->name} tại {$repairRequest->contract->room->motel->name}."
            );

            return $repairRequest;
        });
    }
}
