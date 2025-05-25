<?php

namespace App\Services;

use App\Models\Bookmark;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Log;

class BookmarkService
{
    public function getAllBookmarks()
    {
        try {
            return Bookmark::all();
        } catch (Exception $e) {
            Log::error("Lỗi khi lấy tất cả bookmarks: " . $e->getMessage());
            throw $e;
        }
    }
    public function getUserBookmarks($userId)
    {
        try {
            return Bookmark::where('user_id', $userId)->get();
        } catch (Exception $e) {
            Log::error("Lỗi khi lấy bookmarks cho user_id {$userId}: " . $e->getMessage());
            throw $e;
        }
    }

    public function createBookmark(array $data)
    {
        try {
            $bookmarkData = [
                'user_id' => $data['user_id'],
                'motel_id' => $data['motel_id'],
            ];

            return Bookmark::create($bookmarkData);
        } catch (Exception $e) {
            Log::error("Lỗi khi tạo bookmark: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteBookmark(Bookmark $bookmark)
    {
        try {
            $bookmark->delete();
            return true;
        } catch (Exception $e) {
            Log::error("Lỗi khi xóa bookmark ID {$bookmark->id}: " . $e->getMessage());
            throw $e;
        }
    }
}
