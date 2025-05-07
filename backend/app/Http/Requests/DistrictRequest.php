<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DistrictRequest extends FormRequest
{
    // Cho phép gửi request (đặt true là đủ)
    public function authorize(): bool
    {
        return true;
    }

    // Quy tắc validate
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'image' => 'required|string|max:255',
        ];
    }

    // Tùy chỉnh thông báo lỗi
    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên quận/huyện.',
            'name.max' => 'Tên không được vượt quá 100 ký tự.',
            'image.required' => 'Vui lòng nhập đường dẫn hình ảnh.',
            'image.max' => 'Đường dẫn hình ảnh không được vượt quá 255 ký tự.',
        ];
    }
}
