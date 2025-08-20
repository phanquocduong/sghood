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

    public function getCommentsByBlog($blogSlug)
    {
        $blog = Blog::where('slug', $blogSlug)->firstOrFail();

        $comments = CommentBlog::where('blog_id', $blog->id)
            ->whereNull('parent_id')
            ->with([
                'children.user:id,name,avatar',
                'user:id,name,avatar'
            ])
            ->select([
                'id',
                'content',
                'created_at',
                'updated_at',
                'likes_count',
                'dislikes_count',
                'parent_id',
                'blog_id',
                'user_id'
            ])
            ->latest()
            ->paginate(3);

        $data = $comments->getCollection()
            ->map(fn($comment) => $this->formatComment($comment))
            ->values(); // đảm bảo trả về array tuần tự

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'total' => $comments->total(),
            ]
        ]);
    }


    private function formatComment($comment)
    {
        return [
            'id' => $comment->id,
            'content' => $comment->content,
            'created_at' => $comment->created_at,
            'updated_at' => $comment->updated_at,
            'likes' => $comment->likes_count,
            'dislikes' => $comment->dislikes_count,
            'parent' => $comment->parent_id,
            'blog_id' => $comment->blog_id,
            'user' => [
                'id' => $comment->user->id,
                'name' => $comment->user->name,
                'avatar' => $comment->user->avatar,
            ],
            'children' => $comment->children->map(function ($child) {
                return $this->formatComment($child);
            }),
        ];
    }


    public function ReplayComment(SendCommentRequest $request, Blog $blog)

    {
        // Gán tạm user_id cố định để test
        $userId = Auth::id();

        $this->commentService->replayComment(
            $blog->id,
            $userId,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã gửi bình luận'
        ], 201);
    }

    public function SendComment(SendCommentRequest $request, Blog $blog)
    {
        // Gán tạm user_id cố định để test
        $userId = Auth::id(); // ví dụ: admin có id=1

        $this->commentService->sendComment(
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
