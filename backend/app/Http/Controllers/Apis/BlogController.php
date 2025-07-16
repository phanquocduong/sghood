<?php

namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\BlogService;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    protected $blogService;
    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;
    }
    public function index(Request $request)
    {
        $blogs = $this->blogService->getAll($request->all());

        return response()->json([
            'success' => true,
            'data' => $blogs
        ]);
    }

    public function showBlog($slug)
    {
        $blog = $this->blogService->getBlogBySlug($slug);
        if (!$blog) {
            return response()->json([
                'success' => false,
                'message' => 'Blog không tồn tại',
                'chuoiblog' => $blog
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $blog
        ]);
    }
    public function related($id)
    {
        $related = $this->blogService->getRelatedPosts($id);
        return response()->json($related);
    }

    public function popular()
    {
        $popular = $this->blogService->getPopularPosts();
        return response()->json($popular);
    }

    public function increaseView($id)
    {
        $this->blogService->increaseView($id);
        return response()->json(['message' => 'View increased']);
    }
}
