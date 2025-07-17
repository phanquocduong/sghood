<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Cho phép người dùng gửi bình luận
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:comment_blogs,id',
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Nội dung bình luận không được để trống.',
            'content.string' => 'Nội dung bình luận phải là chuỗi.',
            'parent_id.exists' => 'Bình luận cha không tồn tại.'
        ];
    }
}
