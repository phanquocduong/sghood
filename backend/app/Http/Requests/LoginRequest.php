<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Lớp xác thực dữ liệu cho yêu cầu đăng nhập.
 */
class LoginRequest extends FormRequest
{
    /**
     * Kiểm tra quyền truy cập của người dùng.
     *
     * @return bool True nếu người dùng được phép thực hiện yêu cầu
     */
    public function authorize(): bool
    {
        return true; // Cho phép tất cả người dùng thực hiện yêu cầu đăng nhập
    }

    /**
     * Các quy tắc xác thực cho yêu cầu đăng nhập.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string> Quy tắc xác thực
     */
    public function rules(): array
    {
        return [
            'username' => 'required|string', // Tên đăng nhập (email hoặc số điện thoại) là bắt buộc và phải là chuỗi
            'password' => 'required|string' // Mật khẩu là bắt buộc và phải là chuỗi
        ];
    }

    /**
     * Tùy chỉnh thông báo lỗi cho các quy tắc xác thực.
     *
     * @return array<string, string> Thông báo lỗi tùy chỉnh
     */
    public function messages(): array
    {
        return [
            'username.required' => 'Tên đăng nhập là bắt buộc.',
            'username.string' => 'Tên đăng nhập phải là chuỗi ký tự.',
            'password.required' => 'Mật khẩu là bắt buộc.',
            'password.string' => 'Mật khẩu phải là chuỗi ký tự.'
        ];
    }
}
