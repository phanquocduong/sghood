<?php

namespace App\Services;

use App\Models\Bookmark;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookmarkService
{
    public function getBookmarks(string $userId, string $motelId, string $sortOption, int $perPage): array {
        try {
            $query = Bookmark::with(['user', 'motel']);

            if ($userId !== '') {
                $query->where('user_id', $userId);
            }

            if ($motelId !== '') {
                $query->where('motel_id', $motelId);
            }

            $this->applySorting($query, $sortOption);

            $bookmarks = $query->paginate($perPage);

            return ['data' => $bookmarks];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi lấy danh sách bookmark', 'status' => 500];
        }
    }

    private function applySorting($query, string $sortOption): void {
        $sort = $this->handleSortOption($sortOption);
        $query->orderBy($sort['field'], $sort['order']);
    }

    private function handleSortOption(string $sortOption): array
    {
        switch ($sortOption) {
            case 'created_at_asc':
                return ['field' => 'created_at', 'order' => 'asc'];
            case 'created_at_desc':
                return ['field' => 'created_at', 'order' => 'desc'];
            default:
                return ['field' => 'created_at', 'order' => 'desc'];
        }
    }

    public function getBookmark(int $id): array {
        try {
            $bookmark = Bookmark::find($id);
            if (!$bookmark) {
                return ['error' => 'Bookmark không tìm thấy', 'status' => 404];
            }
            return ['data' => $bookmark->load(['user', 'motel'])];
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi lấy bookmark', 'status' => 500];
        }
    }

    public function createBookmark(array $data): array {
        DB::beginTransaction();
        try {
            $bookmark = Bookmark::create($data);
            DB::commit();

            return ['data' => $bookmark->load(['user', 'motel'])];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi tạo bookmark', 'status' => 500];
        }
    }

    public function deleteBookmark(int $id): array {
        DB::beginTransaction();
        try {
            $bookmark = Bookmark::find($id);
            if (!$bookmark) {
                return ['error' => 'Bookmark không tìm thấy', 'status' => 404];
            }
            $bookmark->delete();

            DB::commit();
            return ['success' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return ['error' => 'Đã xảy ra lỗi khi xóa bookmark', 'status' => 500];
        }
    }
}
