<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookmarkRequest;
use App\Models\Bookmark;
use App\Services\BookmarkService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class BookmarkController extends Controller
{
    protected $bookmarkService;

    public function __construct(BookmarkService $bookmarkService)
    {
        $this->bookmarkService = $bookmarkService;
    }

public function index(Request $request)
{
    try {
        $userId = Auth::id() ?? $request->user_id;
        $bookmarks = $this->bookmarkService->getUserBookmarks($userId);
        return response()->json($bookmarks);
    } catch (Exception $e) {
        // Xử lý lỗi nếu có
        return response()->json([
            'message' => 'Xem bookmark thất bại',
            'error' => $e->getMessage()
        ], 500);
    }
}



    public function store(StoreBookmarkRequest $request)
    {
        try {
            $data = $request->validated();
            $data['user_id'] = Auth::id() ?? $request->user_id;
            $bookmark = $this->bookmarkService->createBookmark($data);

            return response()->json($bookmark, 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Tạo bookmark thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            $userId = Auth::id() ?? request()->user_id;
            $bookmark = Bookmark::findOrFail($id);
            $this->bookmarkService->deleteBookmark($bookmark);

            return response()->json(['message' => 'Xóa bookmark thành công'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Bookmark không tồn tại'], 404);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Xóa bookmark thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
