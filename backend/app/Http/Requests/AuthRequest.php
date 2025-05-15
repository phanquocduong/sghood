<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends FormRequest
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
            'type' => 'nullable|string|in:user,admin'
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
            'type.in' => 'Loại đăng nhập phải là user hoặc admin'
        ];
    }
}
