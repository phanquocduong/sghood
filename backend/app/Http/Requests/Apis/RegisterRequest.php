<?php

namespace App\Http\Requests\Apis;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => 'required|string|unique:users,phone',
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|confirmed'
        ];
    }

    /**
     * Get custom error messages for validation rules.
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
