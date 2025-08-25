<?php

namespace App\Http\Requests\Apis;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có được phép thực hiện yêu cầu này không.
     */
    public function authorize(): bool
    {
        return true; // Cho phép tất cả người dùng thực hiện yêu cầu đặt lại mật khẩu
    }

    /**
     * Các quy tắc xác thực cho yêu cầu đặt lại mật khẩu.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => 'required|string', // Số điện thoại là bắt buộc
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/', // Mật khẩu yêu cầu ít nhất 8 ký tự, chứa chữ hoa, chữ thường, số và ký tự đặc biệt
            'password_confirmation' => 'required|string' // Xác nhận mật khẩu là bắt buộc
        ];
    }

    /**
     * Tùy chỉnh thông báo lỗi cho các quy tắc xác thực.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'phone.required' => 'Vui lòng nhập số điện thoại',
            'password.required' => 'Vui lòng nhập mật khẩu',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp',
            'password_confirmation.required' => 'Xác nhận mật khẩu là bắt buộc.'
        ];
    }
}
