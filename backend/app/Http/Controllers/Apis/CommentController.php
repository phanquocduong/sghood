<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Http\Requests\EditCommentRequest;
use App\Http\Requests\ReactCommentRequest;
use App\Http\Requests\SendCommentRequest;
use App\Models\Blog;
use App\Models\CommentBlog;
use App\Services\Apis\CommentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class CommentController extends Controller
{
    protected $commentService;
    public function __construct(CommentService $commentService)
    {
        $this->commentService =  $commentService;
    }

    public function getAllCommentBlog($blogId)
    {
        $comments = $this->commentService->getCommentsByBlog($blogId);
        return response()->json(['success' => true, 'data' => $comments]);
    }

    public function sendComment(SendCommentRequest $request, Blog $blog)
    {
        // Gán tạm user_id cố định để test
        $userId = Auth::id(); // ví dụ: admin có id=1

        $this->commentService->createComment(
            $blog->id,
            $userId,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã gửi bình luận'
        ], 201);
    }

    public function editComment(EditCommentRequest $request, $commentId)
    {
        $this->commentService->updateComment(
            $commentId,
            $request->input('content')
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật bình luận'
        ], 200);
    }

    public function deleteComment($commentId)
    {
        $this->commentService->deleteComment($commentId);
        return response()->json(['success' => true, 'message' => 'Đã xoá bình luận']);
    }

    public function react(ReactCommentRequest $request, $commentId)
    {
        $comment = $this->commentService->reactToComment(
            $commentId,
            $request->input('type')
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã cập nhật phản hồi',
            'data' => $comment
        ]);
    }
}
