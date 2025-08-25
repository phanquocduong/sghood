<?php

namespace App\Http\Requests\Apis;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có được phép thực hiện yêu cầu này không.
     */
    public function authorize(): bool
    {
        return true; // Cho phép tất cả người dùng thực hiện yêu cầu đăng ký
    }

    /**
     * Các quy tắc xác thực cho yêu cầu đăng ký.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => 'required|string|unique:users,phone', // Số điện thoại là bắt buộc và phải duy nhất
            'name' => 'required|string|min:2|max:255', // Tên là bắt buộc, tối thiểu 2 ký tự, tối đa 255 ký tự
            'email' => 'required|email|unique:users,email|max:255', // Email là bắt buộc, định dạng hợp lệ, duy nhất
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{8,}$/' // Mật khẩu yêu cầu ít nhất 8 ký tự, chứa chữ hoa, chữ thường, số và ký tự đặc biệt
        ];
    }

    /**
     * Tùy chỉnh thông báo lỗi cho các quy tắc xác thực.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.string' => 'Số điện thoại phải là một chuỗi ký tự.',
            'phone.unique' => 'Số điện thoại này đã được sử dụng.',

            'name.required' => 'Vui lòng nhập họ và tên.',
            'name.string' => 'Họ và tên phải là một chuỗi ký tự.',
            'name.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
            'name.max' => 'Họ và tên không được vượt quá 255 ký tự.',

            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'email.unique' => 'Địa chỉ email này đã được sử dụng.',
            'email.max' => 'Địa chỉ email không được vượt quá 255 ký tự.',

            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.string' => 'Mật khẩu phải là một chuỗi ký tự.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.'
        ];
    }
}
