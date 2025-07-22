<?php

namespace App\Services;

use App\Models\CommentBlog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;



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

        if ($blogId) {
            $query->where('blog_id', $blogId);
        }

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

        if ($status) {
            $query->where('status', $status);
        }

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
