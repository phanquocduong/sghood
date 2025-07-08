<?php
namespace App\Services\Apis;
use App\Models\Blog;

class BlogService
{
    public function getAll()
    {
        return Blog::latest()->get();
    }
    public function getBlogBySlug($slug)
    {
        return Blog::where('slug', $slug)->first();
    }
}
