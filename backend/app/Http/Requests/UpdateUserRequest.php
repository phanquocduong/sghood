<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $this->route('id'),
            'birthday' => 'sometimes|date',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'front_id_card_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'back_id_card_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'role' => 'sometimes|in:Người đăng ký,Người thuê,Quản trị viên',
            'status' => 'sometimes|in:Hoạt động,Khóa',
        ];
    }

    public function messages()
    {
        return [
            'name.string' => 'Tên phải là một chuỗi.',
            'name.max' => 'Tên không được vượt quá 255 ký tự.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email đã tồn tại. Vui lòng chọn email khác.',
            'birthday.date' => 'Ngày sinh không hợp lệ.',
            'avatar.image' => 'File tải lên phải là hình ảnh.',
            'avatar.mimes' => 'Hình ảnh phải có định dạng jpeg, png, jpg, gif, hoặc webp.',
            'avatar.max' => 'Hình ảnh không được vượt quá 2MB.',
            'front_id_card_image.image' => 'File tải lên phải là hình ảnh.',
            'front_id_card_image.mimes' => 'Hình ảnh phải có định dạng jpeg, png, jpg, gif, hoặc webp.',
            'front_id_card_image.max' => 'Hình ảnh không được vượt quá 2MB.',
            'back_id_card_image.image' => 'File tải lên phải là hình ảnh.',
            'back_id_card_image.mimes' => 'Hình ảnh phải có định dạng jpeg, png, jpg, gif, hoặc webp.',
            'back_id_card_image.max' => 'Hình ảnh không được vượt quá 2MB.',
            'role.in' => 'Vai trò phải là "Người đăng ký", "Người thuê" hoặc "Quản trị viên".',
            'status.in' => 'Trạng thái phải là "Hoạt động" hoặc "Khóa".',
        ];
    }
}
