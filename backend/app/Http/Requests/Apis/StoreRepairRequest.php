<?php

namespace App\Http\Requests\Apis;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRepairRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
         return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * Get custom error messages
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề là bắt buộc',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự',
            'description.required' => 'Mô tả là bắt buộc',
            'description.max' => 'Mô tả không được vượt quá 1000 ký tự',
            'images.array' => 'Hình ảnh phải được gửi dưới dạng mảng',
            'images.max' => 'Tối đa chỉ được gửi 5 hình ảnh',
            'images.*.file' => 'Mỗi hình ảnh phải là một file hợp lệ',
            'images.*.mimes' => 'Hình ảnh phải có định dạng jpg, jpeg, png hoặc webp',
            'images.*.max' => 'Mỗi hình ảnh không được vượt quá 2MB',
        ];
    }
}
