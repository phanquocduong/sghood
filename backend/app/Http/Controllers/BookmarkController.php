<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\Bookmarks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id() ?? $request->user_id;
        $bookmarks = Bookmarks::where('user_id', $userId)->with('motel')->get();

        return response()->json($bookmarks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'motel_id' => 'required|exists:motels,id',
        ]);

        $bookmark = Bookmarks::create([
            'user_id' => Auth::id() ?? $request->user_id,
            'motel_id' => $validated['motel_id'],
        ]);

        return response()->json($bookmark, 201);
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'motel_id' => 'required|exists:motels,id',
        ]);

        $bookmark = Bookmarks::findOrFail($id);

        // Kiểm tra quyền (nếu có Auth)
        if ($bookmark->user_id !== (Auth::id() ?? $request->user_id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $bookmark->motel_id = $validated['motel_id'];
        $bookmark->save();

        return response()->json(['message' => 'Bookmark updated successfully', 'bookmark' => $bookmark]);
    }


    public function destroy($id)
    {
        $bookmark = Bookmarks::findOrFail($id);

        if ($bookmark->user_id !== (Auth::id() ?? request()->user_id)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $bookmark->delete();

        return response()->json(['message' => 'Bookmark deleted successfully']);
    }
}

