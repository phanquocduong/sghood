<?php
namespace App\Http\Controllers\Apis;

use App\Http\Controllers\Controller;
use App\Services\Apis\BlogService;

class BlogController extends Controller
{
    protected $blogService;
    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;
    }
    public function index()
    {
        $blog = $this->blogService->getAll();
        return response()->json([
            'success'=> true,
            'data'=> $blog
        ]);
    }
    public function showBlog($id)
    {
        $blog = $this->blogService->getBlogById($id);
        if (!$blog) {
            return response()->json([
                'success' => false,
                'message' => 'Blog không tồn tại'
            ], 404);
        }
        return response()->json([
            'success'=> true,
            'data'=> $blog
        ]);
    }
}
