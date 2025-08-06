<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReplyCommentRequest;
use App\Models\Blog;
use App\Models\CommentBlog;
use App\Services\CommentService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    protected $commentService;
    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }
    public function index(Request $request, $blogId)
    {
        $blog = Blog::findOrFail($blogId);

        $searchQuery = (string) $request->input('querySearch', '');
        $status      = (string) $request->input('status', null);
        $sortOption  = (string) $request->input('sort_by', 'created_at_desc');
        $perPage     = (int) $request->input('perPage', 10);

        $comments = $this->commentService->getAllComments(
            $searchQuery,
            $status,
            $sortOption,
            $perPage,
            $blogId
        );

        return view('comments.index', [
            'searchQuery' => $searchQuery,
            'status'      => $status,
            'sortOption'  => $sortOption,
            'perPage'     => $perPage,
            'comments'    => $comments,
            'blog'        => $blog,
        ]);
    }


    public function reply(ReplyCommentRequest $request)
    {
        $this->commentService->reply($request->validated());

        return redirect()->back()->with('success', 'Trả lời bình luận thành công!');
    }
    public function toggleVisibility($blogId, $id)
    {
        $comment = CommentBlog::where('id', $id)->where('blog_id', $blogId)->firstOrFail();

        $comment->is_hidden = !$comment->is_hidden;
        $comment->save();

        return response()->json([
            'success' => true,
            'is_hidden' => $comment->is_hidden
        ]);
    }
}
