<?php

namespace App\Http\Requests\Apis;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Lớp xác thực dữ liệu cho yêu cầu đổi mật khẩu.
 */
class ChangePasswordRequest extends FormRequest
{
    /**
     * Kiểm tra quyền truy cập của người dùng.
     *
     * @return bool True nếu người dùng được phép thực hiện yêu cầu
     */
    public function authorize(): bool
    {
        return true; // Cho phép tất cả người dùng đã đăng nhập thực hiện
    }

    /**
     * Các quy tắc xác thực dữ liệu đầu vào.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string> Quy tắc xác thực
     */
    public function rules(): array
    {
        return [
            'current_password' => 'required|string', // Mật khẩu hiện tại là bắt buộc
            'new_password' => 'required|string|min:8|confirmed', // Mật khẩu mới phải có ít nhất 8 ký tự và được xác nhận
        ];
    }

    /**
     * Tùy chỉnh thông báo lỗi cho các quy tắc xác thực.
     *
     * @return array Thông báo lỗi tùy chỉnh
     */
    public function messages()
    {
        return [
            'current_password.required' => 'Mật khẩu hiện tại là bắt buộc.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'new_password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ];
    }
}
