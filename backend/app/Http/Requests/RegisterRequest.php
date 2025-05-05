<?php

namespace App\Http\Requests;

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
            'id_token' => 'required|string',
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255|unique:users,email',
            'birthdate' => 'required|date',
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
            'id_token.required' => 'Vui lòng cung cấp token xác thực.',
            'id_token.string' => 'Token xác thực phải là một chuỗi ký tự.',
            'name.required' => 'Vui lòng nhập họ và tên.',
            'name.string' => 'Họ và tên phải là một chuỗi ký tự.',
            'name.max' => 'Họ và tên không được vượt quá 100 ký tự.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'email.max' => 'Địa chỉ email không được vượt quá 255 ký tự.',
            'email.unique' => 'Địa chỉ email này đã được sử dụng.',
            'birthdate.required' => 'Vui lòng nhập ngày sinh.',
            'birthdate.date' => 'Ngày sinh không hợp lệ.',
        ];
    }
}
