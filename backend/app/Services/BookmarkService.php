<?php
namespace App\Services;

use App\Models\Bookmarks;

class BookmarkService
{
    public function getUserBookmarks($userId)
    {
        return Bookmarks::where('user_id', $userId)
                       ->with('motel')
                       ->get();
    }

    public function createBookmark(array $data)
    {
        return Bookmarks::create($data);
    }

    public function deleteBookmark(Bookmarks $bookmark)
    {
        $bookmark->delete();
        return true;
    }
}
