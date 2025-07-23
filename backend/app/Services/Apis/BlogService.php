<?php

namespace App\Services\Apis;

use App\Models\Blog;

class BlogService
{
    public function getAll(array $params = [])
    {
        $query = Blog::query();

        // Tìm kiếm
        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Lọc theo danh mục
        if (!empty($params['category'])) {
            $query->where('category', $params['category']);
        }

        $perPage = $params['per_page'] ?? 6;


        return $query->latest()->paginate($perPage);
    }

    public function getBlogBySlug($slug)
    {
        return Blog::select('thumbnail', 'id', 'title', 'slug', 'content', 'created_at')
            ->where('slug', $slug)
            ->first();
    }

    public function getRelatedPosts(int $id, int $limit = 5)
    {
        $currentPost = Blog::findOrFail($id);

        return Blog::where('id', '<>', $id)
            ->where('category', $currentPost->category) // nếu $currentPost->category là Enum
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getPopularPosts(int $limit = 5)
    {
        return Blog::orderByDesc('views')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function increaseView(int $id)
    {
        Blog::where('id', $id)->increment('views');
    }
}
