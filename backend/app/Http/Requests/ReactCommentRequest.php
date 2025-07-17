<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReactCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Nếu muốn kiểm tra user đăng nhập, có thể đổi thành: return auth()->check();
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:like,dislike'
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Bạn phải chọn hành động like hoặc dislike.',
            'type.in' => 'Hành động không hợp lệ. Chỉ chấp nhận like hoặc dislike.'
        ];
    }
}
