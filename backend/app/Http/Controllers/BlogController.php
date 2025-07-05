<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use App\Services\BlogService;
use App\Http\Requests\BlogStoreRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    protected $blogService;
    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;
    }
    public function index(Request $request)
    {
        $searchQuery =  (string) $request->input('querySearch', '');
        $status = (string)  $request->input('status', null);
        $sortOption = (string) $request->input('sort_by', 'created_at_desc');
        $perPage = (string) $request->input('perPage', 10);
        $blogs = $this->blogService->getAllBlogs($searchQuery, $status, $sortOption, $perPage = 10);
        $data = [
            'searchQuery'=>$searchQuery,
            'status'=>$status,
            'sortOption'=>$sortOption,
            'perPage'=>$perPage,
            'blogs'=>$blogs
        ];
        return view('blogs.index', $data);
    }
    public function create()
    {
        return view('blogs.create');
    }
    public function edit(int $id)
    {
        $blog = $this->blogService->getBlogById($id);
        return view('blogs.edit', compact('blog'));
    }
    public function delete(int $id)
    {
        $result = $this->blogService->deleteBlog($id);
        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        return redirect()->route('blogs.index')->with('success', 'Xóa bài viết thành công!');
    }

    public function trash(Request $request)
    {
        $searchQuery =  (string) $request->input('querySearch', '');
        $status = (string)  $request->input('status', null);
        $sortOption = (string) $request->input('sort_by', 'created_at_desc');
        $perPage = (string) $request->input('perPage', 10);
        $blogs = $this->blogService->getDeleteBlogs($searchQuery, $status, $sortOption, $perPage = 10);
        $data = [
            'searchQuery'=>$searchQuery,
            'status'=>$status,
            'sortOption'=>$sortOption,
            'perPage'=>$perPage,
            'blogs'=>$blogs
        ];
        return view('blogs.trash', $data);
    }
    public function store(BlogStoreRequest $request)
    {
        try {
            $validatedData = $request->validated(); // Lấy dữ liệu đã được xác thực từ request
            // $validatedData['author_id'] = Auth::id();
            $data['title'] = $validatedData['title'];
            $data['content'] = $validatedData['content'];
            $data['author_id'] = $validatedData['author_id']; // Lấy ID của người dùng đang đăng nhập
            $data['status'] = $validatedData['status'] ?? 'draft'; // Mặc định là 'draft' nếu không có giá trị
            $data['thumbnail'] = $request->file('thumbnail'); // Lấy tệp hình ảnh từ request

            $blog = $this->blogService->createBlog($data);

            if ($blog) {
                return redirect()->route('blogs.index')->with('success', 'Tạo bài viết thành công!');
            }

            return redirect()->back()->with('error', 'Tạo bài viết thất bại.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }
    public function update(BlogStoreRequest $request, $id)
    {
        try {
            $validatedData = $request->validated(); // Lấy dữ liệu đã được xác thực từ request
            // $validatedData['author_id'] = Auth::id();
            $data['title'] = $validatedData['title'];
            $data['content'] = $validatedData['content'];
            $data['author_id'] = $validatedData['author_id']; // Lấy ID của người dùng đang đăng nhập
            $data['status'] = $validatedData['status'] ?? 'draft'; // Mặc định là 'draft' nếu không có giá trị
            $data['thumbnail'] = $request->file('thumbnail'); // Lấy tệp hình ảnh từ request

            $blog = $this->blogService->updateBlog($id, $data);
            if ($blog) {
                return redirect()->route('blogs.index')->with('success', 'Sửa bài viết thành công!');
            }

            return redirect()->back()->with('error', 'Sửa bài viết thất bại.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage());
        }
    }
    public function restore(int $id)
    {
        $result = $this->blogService->restoreBlog($id);
        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        return redirect()->route('blogs.trash')->with('success', 'Khôi phục bài viết thành công!');
    }

    public function Forcedelete(int $id) {
        $result = $this->blogService->ForcedeleteBlog($id);
        if(isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        return redirect()->route('blogs.trash')->with('success', 'Xóa bài viết thành công!');
    }
}
