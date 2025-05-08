<?php
namespace App\Services;

use App\Models\Bookmark;

class BookmarkService
{
   // BookmarkService.php
public function getUserBookmarks($userId)
{
    return Bookmark::all();
}

    public function createBookmark(array $data)
{
    // Chỉ lấy các trường được phép (bỏ updated_at và created_at)
    $bookmarkData = [
        'user_id' => $data['user_id'],
        'motel_id' => $data['motel_id'],
    ];

    return Bookmark::create($bookmarkData);
}


    public function deleteBookmark(Bookmark $bookmark)
    {
        $bookmark->delete();
        return true;
    }
}
