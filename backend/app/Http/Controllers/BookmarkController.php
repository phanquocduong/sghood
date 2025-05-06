<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookmarkRequest;
use App\Http\Requests\UpdateBookmarkRequest;
use App\Models\Bookmark;
use App\Models\Bookmarks;
use App\Services\BookmarkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    protected $bookmarkService;

    public function __construct(BookmarkService $bookmarkService)
    {
        $this->bookmarkService = $bookmarkService;
    }

    public function index(Request $request)
    {
        $userId = Auth::id() ?? $request->user_id;
        $bookmarks = $this->bookmarkService->getUserBookmarks($userId);

        return response()->json($bookmarks);
    }

    public function store(StoreBookmarkRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id() ?? $request->user_id;

        $bookmark = $this->bookmarkService->createBookmark($data);

        return response()->json($bookmark, 201);
    }

    public function destroy($id)
    {
        $bookmark = Bookmarks::findOrFail($id);

        if ($bookmark->user_id !== (Auth::id() ?? request()->user_id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->bookmarkService->deleteBookmark($bookmark);

        return response()->json(['message' => 'Bookmark deleted successfully']);
    }
}


