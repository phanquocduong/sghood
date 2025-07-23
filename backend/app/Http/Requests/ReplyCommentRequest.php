<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReplyCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Cho phép tất cả user (tuỳ bạn kiểm tra thêm quyền ở đây nếu cần)
        return true;
    }

    public function rules(): array
    {
        return [
            'parent_id' => 'required|exists:comment_blogs,id',
            'content'   => 'required|string|max:1000',
            'blog_id'   => 'required|exists:blogs,id',
        ];
    }

    public function messages(): array
    {
        return [
            'parent_id.required' => 'Thiếu ID bình luận cha.',
            'parent_id.exists'   => 'Bình luận cha không tồn tại.',
            'content.required'   => 'Bạn chưa nhập nội dung trả lời.',
            'blog_id.required'   => 'Thiếu ID bài viết.',
            'blog_id.exists'     => 'Bài viết không tồn tại.',
        ];
    }
}
