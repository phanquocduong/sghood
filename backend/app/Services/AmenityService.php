<?php

namespace App\Services;

use App\Models\Amenity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AmenityService
{
    // Tiêu chuẩn hóa phản hồi thành công
    protected function successResponse($data)
    {
        return ['data' => $data];
    }

    // Tiêu chuẩn hóa phản hồi lỗi
    protected function errorResponse($message, $status = 400)
    {
        return ['error' => $message, 'status' => $status];
    }

    // Lấy danh sách amenities với phân trang
    public function fetchAmenities(bool $onlyTrashed, string $querySearch, string $type, string $sortOption, int $perPage): array
    {
        try {
            $query = $onlyTrashed ? Amenity::onlyTrashed() : Amenity::query();

            $this->applyFilters($query, $querySearch, $type);
            $this->applySorting($query, $sortOption);

            $amenities = $query->paginate($perPage);

            return $this->successResponse($amenities);
        } catch (\Throwable $e) {
            Log::error('Error in fetchAmenities: ' . $e->getMessage());
            return $this->errorResponse('Đã xảy ra lỗi khi lấy danh sách tiện ích', 500);
        }
    }

    // Lấy danh sách amenities đang hoạt động
    public function getAvailableAmenities(string $querySearch, string $type, string $sortOption, int $perPage): array
    {
        return $this->fetchAmenities(false, $querySearch, $type, $sortOption, $perPage);
    }

    // Lấy danh sách amenities đã xóa
    public function getTrashedAmenities(string $querySearch, string $type, string $sortOption, int $perPage): array
    {
        return $this->fetchAmenities(true, $querySearch, $type, $sortOption, $perPage);
    }

    // Lấy danh sách tất cả amenities
    public function getAllAmenities(): array
    {
        try {
            $amenities = Amenity::orderBy('type', 'asc')
                                ->orderBy('order', 'asc')
                                ->get();
            return $this->successResponse($amenities);
        } catch (\Throwable $e) {
            Log::error('Error in getAllAmenities: ' . $e->getMessage());
            return $this->errorResponse('Đã xảy ra lỗi khi lấy danh sách tiện ích', 500);
        }
    }

    // Lấy thông tin chi tiết của một amenity
    public function getAmenity(int $id): array
    {
        try {
            $amenity = Amenity::find($id);
            if (!$amenity) {
                return $this->errorResponse('Tiện ích không tìm thấy', 404);
            }

            return $this->successResponse($amenity);
        } catch (\Throwable $e) {
            Log::error('Error in getAmenity: ' . $e->getMessage());
            return $this->errorResponse('Đã xảy ra lỗi khi lấy tiện ích', 500);
        }
    }

    // Tạo mới amenity
    public function createAmenity(array $data): array
    {
        DB::beginTransaction();
        try {
            // Tìm giá trị order lớn nhất cho type hiện tại
            $maxOrder = Amenity::where('type', $data['type'])
                ->whereNull('deleted_at')
                ->max('order') ?? 0;

            // Gán order mới là maxOrder + 1 (ít nhất là 1)
            $data['order'] = max(1, $maxOrder + 1);

            $amenity = Amenity::create($data);

            DB::commit();
            return $this->successResponse($amenity);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error in createAmenity: ' . $e->getMessage());
            return $this->errorResponse('Đã xảy ra lỗi khi tạo tiện ích', 500);
        }
    }

    // Cập nhật thông tin amenity
    public function updateAmenity(int $id, array $data): array
    {
        DB::beginTransaction();
        try {
            $amenity = Amenity::find($id);
            if (!$amenity) {
                return $this->errorResponse('Tiện ích không tìm thấy', 404);
            }

            $originalType = $amenity->type;
            $originalOrder = $amenity->order;
            $newType = $data['type'] ?? $amenity->type;
            $newOrder = $data['order'] ?? $amenity->order;

            // Nếu type thay đổi, tính toán lại order
            if ($newType !== $originalType) {
                $maxOrder = Amenity::where('type', $newType)
                    ->whereNull('deleted_at')
                    ->where('id', '!=', $id)
                    ->max('order') ?? 0;

                $data['order'] = max(1, $maxOrder + 1);
                Log::info('Type changed, New Order set to: ' . $data['order']);
                $this->reorderAmenities($originalType, $id);
            } elseif ($newOrder != $originalOrder) {
                // Nếu order thay đổi trong cùng type
                $this->handleOrderChange($id, $newType, $newOrder);
                $data['order'] = $newOrder;
            }

            $amenity->update($data);

            // Sắp xếp lại toàn bộ order để đảm bảo liên tục
            $this->reorderAmenities($newType);

            DB::commit();
            return $this->successResponse($amenity);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error updating amenity: ' . $e->getMessage());
            return $this->errorResponse('Đã xảy ra lỗi khi cập nhật tiện ích', 500);
        }
    }

    // Xóa amenity
    public function deleteAmenity(int $id): array
    {
        DB::beginTransaction();
        try {
            $amenity = Amenity::find($id);
            if (!$amenity) {
                return $this->errorResponse('Tiện ích không tìm thấy', 404);
            }

            $type = $amenity->type;
            $amenity->delete();

            // Sắp xếp lại order sau khi xóa
            $this->reorderAmenities($type, $id);

            DB::commit();
            return $this->successResponse($amenity);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error in deleteAmenity: ' . $e->getMessage());
            return $this->errorResponse('Đã xảy ra lỗi khi xóa tiện ích', 500);
        }
    }

    // Khôi phục amenity từ thùng rác
    public function restoreAmenity(int $id): array
    {
        DB::beginTransaction();
        try {
            $amenity = Amenity::onlyTrashed()->find($id);
            if (!$amenity) {
                return $this->errorResponse('Tiện ích không tìm thấy trong thùng rác', 404);
            }

            // Tính toán lại order khi khôi phục
            $maxOrder = Amenity::where('type', $amenity->type)
                ->whereNull('deleted_at')
                ->max('order') ?? 0;

            $amenity->order = max(1, $maxOrder + 1);
            $amenity->restore();

            DB::commit();
            return $this->successResponse($amenity);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error in restoreAmenity: ' . $e->getMessage());
            return $this->errorResponse('Đã xảy ra lỗi khi khôi phục tiện ích', 500);
        }
    }

    // Khôi phục amenity từ thùng rác
    public function forceDeleteAmenity(int $id): array
    {
        DB::beginTransaction();
        try {
            $amenity = Amenity::onlyTrashed()->find($id);
            if (!$amenity) {
                return $this->errorResponse('Tiện ích không tìm thấy trong thùng rác', 404);
            }
            $amenity->forceDelete();

            DB::commit();
            return $this->successResponse($amenity);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error in forceDeleteAmenity: ' . $e->getMessage());
            return $this->errorResponse('Đã xảy ra lỗi khi xóa vĩnh viễn tiện ích', 500);
        }
    }

    // Áp dụng bộ lọc cho truy vấn
    private function applyFilters($query, string $querySearch, string $type): void
    {
        if ($querySearch !== '') {
            $query->where('name', 'LIKE', '%' . $querySearch . '%');
        }

        if (!empty($type)) {
            $query->where('type', $type);
        }
    }

    // Áp dụng sắp xếp cho truy vấn
    private function applySorting($query, string $sortOption): void
    {
        $sort = $this->handleSortOption($sortOption);
        $query->orderBy($sort['field'], $sort['order']);
    }

    // Xử lý tùy chọn sắp xếp
    private function handleSortOption(string $sortOption): array
    {
        return match($sortOption) {
            'name_asc' => ['field' => 'name', 'order' => 'asc'],
            'name_desc' => ['field' => 'name', 'order' => 'desc'],
            'status_asc' => ['field' => 'status', 'order' => 'asc'],
            'status_desc' => ['field' => 'status', 'order' => 'desc'],
            'type_asc' => ['field' => 'type', 'order' => 'asc'],
            'type_desc' => ['field' => 'type', 'order' => 'desc'],
            'order_asc' => ['field' => 'order', 'order' => 'asc'],
            'order_desc' => ['field' => 'order', 'order' => 'desc'],
            'created_at_asc' => ['field' => 'created_at', 'order' => 'asc'],
            'created_at_desc' => ['field' => 'created_at', 'order' => 'desc'],
            default => ['field' => 'created_at', 'order' => 'desc']
        };
    }

    // Xử lý thay đổi order cho các amenities khác
    private function handleOrderChange(int $id, string $type, int $newOrder): void
    {
        $otherAmenities = Amenity::where('type', $type)
            ->where('id', '!=', $id)
            ->whereNull('deleted_at')
            ->orderBy('order', 'asc')
            ->get();

        Log::info('Other Amenities count: ' . $otherAmenities->count());

        // Cập nhật order cho các amenities khác
        foreach ($otherAmenities as $index => $amenity) {
            $currentOrder = $index + 1;
            if ($currentOrder >= $newOrder) {
                $currentOrder++;
            }

            if ($amenity->order != $currentOrder) {
                $amenity->update(['order' => $currentOrder]);
                Log::info("Updated Amenity ID: {$amenity->id} to Order: {$currentOrder}");
            }
        }
    }

    // Sắp xếp lại order cho amenities
    private function reorderAmenities(string $type, ?int $excludeId = null): void
    {
        $amenities = Amenity::where('type', $type)
            ->whereNull('deleted_at');

        if ($excludeId) {
            $amenities->where('id', '!=', $excludeId);
        }

        $amenities = $amenities->orderBy('order', 'asc')->get();

        $index = 1;
        foreach ($amenities as $amenity) {
            if ($amenity->order != $index) {
                $amenity->update(['order' => $index]);
                Log::info("Reordered Amenity ID: {$amenity->id} to Order: {$index}");
            }
            $index++;
        }
    }
}
