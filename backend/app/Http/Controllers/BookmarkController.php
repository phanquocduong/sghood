<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookmarkRequest;
use App\Models\Bookmark;
use App\Services\BookmarkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;
use Illuminate\Support\Facades\Log;

class BookmarkController extends Controller
{
    protected $bookmarkService;

    public function __construct(BookmarkService $bookmarkService)
    {
        $this->bookmarkService = $bookmarkService;
    }

public function index(Request $request)
{
    // $userId = Auth::id() ?? $request->user_id;
    $bookmarks = $this->bookmarkService->getAllBookmarks();
    return response()->json($bookmarks);
}



    public function store(StoreBookmarkRequest $request)
    {
        $bookmark = rescue(function () use ($request) {
            return $this->bookmarkService->createBookmark($request->all());
        }, function ($e) {
            Log::error($e);
            return response()->json(['message' => 'Tạo bookmark thất bại'], 500);
        });

        return response()->json($bookmark, 201);
    }


    public function destroy($id)
    {
        $userId = Auth::id() ?? request()->user_id;
        $bookmark = Bookmark::findOrFail($id);

        $this->bookmarkService->deleteBookmark($bookmark);

        return response()->json(['message' => 'Xóa bookmark thành công'], 200);
    }

}
