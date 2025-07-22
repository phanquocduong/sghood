<?php

namespace App\Services\Apis;

use App\Models\Blog;
use App\Models\CommentBlog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CommentService
{
    public function getCommentsByBlog($blogSlug)
    {
        $blog = Blog::where('slug', $blogSlug)->firstOrFail();

        return CommentBlog::where('blog_id', $blog->id)
            ->whereNull('parent_id')
            ->with([
                'children.user',
                'user'
            ])
            ->with([
                'children.user',
                'user'
            ])
            ->latest()
            ->get();
    }

   public function createComment($blogId, $userId, array $data)
    {
        $lastComment = CommentBlog::where('user_id', $userId)
            ->where('blog_id', $blogId)
            ->latest()
            ->first();

        if ($lastComment && $lastComment->created_at->diffInSeconds(now()) < 30) {
            throw ValidationException::withMessages([
                'spam' => 'Bạn đang bình luận quá nhanh. Vui lòng chờ 30 giây.'
            ]);
        }

        $lastComment = CommentBlog::where('user_id', $userId)
            ->where('blog_id', $blogId)
            ->latest()
            ->first();

        if ($lastComment && $lastComment->created_at->diffInSeconds(now()) < 30) {
            throw ValidationException::withMessages([
                'spam' => 'Bạn đang bình luận quá nhanh. Vui lòng chờ 30 giây.'
            ]);
        }

        return CommentBlog::create([
            'blog_id' => $blogId,
            'user_id' => $userId,
            'content' => $data['content'],
            'parent_id' => $data['parent_id'] ?? null,
        ]);
    }

    public function updateComment($commentId, $content)
    {
        $comment = CommentBlog::findOrFail($commentId);
        if ($comment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền chỉnh sửa bình luận này.'
            ], 403);
        }
        $comment->update(['content' => $content]);
    }

    public function deleteComment($commentId)
    {
        $comment = CommentBlog::findOrFail($commentId);
        $comment->delete();
    }

    public function reactToComment($commentId, $type)
    {
        $userId = Auth::id();

        if (!$userId) {
            throw ValidationException::withMessages([
                'user' => 'Bạn cần đăng nhập để thực hiện thao tác này.'
            ]);
        }
        $comment = CommentBlog::findOrFail($commentId);

        match ($type) {
            'like' => $comment->increment('likes_count'),
            'dislike' => $comment->increment('dislikes_count'),
            default => throw ValidationException::withMessages([
                'type' => "Loại phản hồi không hợp lệ: {$type}"
            ]),
        };

        return $comment->fresh();
    }
}
