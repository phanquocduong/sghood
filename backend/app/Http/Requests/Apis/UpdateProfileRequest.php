<?php

namespace App\Http\Requests\Apis;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:255',
            'gender' => 'sometimes|nullable|in:Nam,Nữ,Khác',
            'birthdate' => 'sometimes|nullable|date',
            'address' => 'sometimes|nullable|string|max:255',
            'avatar' => 'sometimes|nullable|image|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Họ tên là bắt buộc.',
            'gender.in' => 'Giới tính không hợp lệ.',
            'birthDate.date' => 'Ngày sinh không hợp lệ.',
            'avatar.image' => 'File phải là ảnh.',
            'avatar.max' => 'Kích thước ảnh không được vượt quá 2MB.',
        ];
    }
}
