<?php

namespace App\Http\Requests\Apis;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Lớp xác thực dữ liệu cho yêu cầu tạo mới yêu cầu sửa chữa.
 */
class StoreRepairRequest extends FormRequest
{
    /**
     * Kiểm tra quyền truy cập của người dùng.
     *
     * @return bool True nếu người dùng đã đăng nhập
     */
    public function authorize(): bool
    {
        return Auth::check(); // Yêu cầu người dùng phải đăng nhập
    }

    /**
     * Các quy tắc xác thực dữ liệu đầu vào.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string> Quy tắc xác thực
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255', // Tiêu đề là bắt buộc, tối đa 255 ký tự
            'description' => 'required|string|max:1000', // Mô tả là bắt buộc, tối đa 1000 ký tự
            'images' => ['nullable', 'array', 'max:5'], // Ảnh là tùy chọn, tối đa 5 ảnh
            'images.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:2048'], // Mỗi ảnh phải là file hợp lệ, định dạng jpg/jpeg/png/webp, tối đa 2MB
        ];
    }

    /**
     * Tùy chỉnh thông báo lỗi cho các quy tắc xác thực.
     *
     * @return array Thông báo lỗi tùy chỉnh
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
