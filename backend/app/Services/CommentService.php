<?php

namespace App\Services;

use App\Models\CommentBlog;
use Illuminate\Support\Facades\Auth;

class CommentService
{
    public function getAllComments(
        $querySearch = null,
        $status = null,
        $sortOption = 'created_at_desc',
        $perPage = 10,
        $blogId = null
    ) {
        $query = CommentBlog::with('user');

        // Lọc theo blog
        if ($blogId) {
            $query->where('blog_id', $blogId);
        }

        // Lọc theo từ khóa nội dung hoặc tên user
        if ($querySearch) {
            $query->where(function ($q) use ($querySearch) {
                $q->where('content', 'like', "%{$querySearch}%")
                    ->orWhereHas(
                        'user',
                        fn($q2) =>
                        $q2->where('name', 'like', "%{$querySearch}%")
                    );
            });
        }

        // Lọc theo trạng thái ẩn/hiện
        if ($status) {
            if ($status === 'hidden') {
                $query->where('is_hidden', 1); // Đã ẩn
            } elseif ($status === 'visible') {
                $query->where('is_hidden', 0); // Hiển thị
            }
        }

        // Sắp xếp
        if ($sortOption) {
            if (str_ends_with($sortOption, '_asc')) {
                $column = substr($sortOption, 0, -4);
                $direction = 'asc';
            } elseif (str_ends_with($sortOption, '_desc')) {
                $column = substr($sortOption, 0, -5);
                $direction = 'desc';
            } else {
                $column = 'created_at';
                $direction = 'desc';
            }

            if (in_array($column, ['created_at', 'updated_at', 'content']) && in_array($direction, ['asc', 'desc'])) {
                $query->orderBy($column, $direction);
            }
        }

        return $query->paginate($perPage);
    }

    public function reply(array $data): CommentBlog
    {
        return CommentBlog::create([
            'blog_id'   => $data['blog_id'],
            'user_id'   => Auth::id(),
            'content'   => $data['content'],
            'parent_id' => $data['parent_id'],
        ]);
    }
}
