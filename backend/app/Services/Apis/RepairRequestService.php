<?php

namespace App\Services\Apis;

use App\Models\Contract;
use App\Models\RepairRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class RepairRequestService
{
    /**
     * Lấy tất cả repair requests của user
     */
    public function getUserRepairRequests(int $userId): Collection
    {
        return RepairRequest::whereHas('contract', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with('contract')->get();
    }

    /**
     * Tạo mới repair request
     */
    public function createRepairRequest(int $userId, array $data): RepairRequest
    {
        // Tìm contract đang hoạt động của user
        $contract = Contract::where('user_id', $userId)
            ->where('status', 'Hoạt động')
            ->firstOrFail();

        // Xử lý upload ảnh
        $imagePaths = [];
        if (isset($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $image) {
                if ($image->isValid()) {
                    // Chuyển đổi ảnh sang WebP
                    $imageManager = new ImageManager(new Driver());
                    $imageWebp = $imageManager->read($image)->toWebp(quality: 85);

                    // Tạo tên file duy nhất
                    $fileName = uniqid('repair_') . '.webp';
                    $path = 'images/repair_requests/' . $fileName;

                    // Lưu ảnh vào storage
                    Storage::disk('public')->put($path, $imageWebp->toString());

                    // Thêm đường dẫn công khai vào mảng
                    $imagePaths[] = Storage::url($path);
                }
            }
        }

        // Chuyển mảng đường dẫn thành chuỗi ngăn cách bằng |
        $imagesString = !empty($imagePaths) ? implode('|', $imagePaths) : null;

        return RepairRequest::create([
            'contract_id' => $contract->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'images' => $imagesString,
            'status' => 'Chờ xác nhận',
        ]);
    }

    /**
     * Hủy yêu cầu sửa chữa
     */
    public function cancelRepairRequest(int $userId, int $repairRequestId): RepairRequest
    {
        // Tìm repair request và kiểm tra xem nó thuộc về user
        $repairRequest = RepairRequest::where('id', $repairRequestId)
            ->whereHas('contract', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->firstOrFail();

        // Kiểm tra trạng thái hiện tại
        if ($repairRequest->status === 'Huỷ bỏ') {
            throw new \Exception('Yêu cầu sửa chữa đã được hủy trước đó');
        }

        if ($repairRequest->status === 'Đang thực hiện') {
            throw new \Exception('Không thể hủy yêu cầu sửa chữa khi đang thực hiện');
        }

        if ($repairRequest->status === 'Hoàn thành') {
            throw new \Exception('Không thể hủy yêu cầu sửa chữa đã hoàn thành');
        }

        // Cập nhật trạng thái
        $repairRequest->update(['status' => 'Huỷ bỏ']);

        return $repairRequest;
    }
}
