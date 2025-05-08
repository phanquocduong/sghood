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
        $userId = $this->route('id');

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $userId,
            'birthday' => 'sometimes|required|date',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'front_id_card_image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'back_id_card_image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'role' => 'sometimes|required|in:Người đăng ký,Người thuê,Quản trị viên',
            'status' => 'sometimes|required|in:Hoạt động,Khóa',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên là bắt buộc.',
            'email.required' => 'Email là bắt buộc.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email đã tồn tại.',
            'avatar.image' => 'File tải lên phải là hình ảnh.',
            'avatar.mimes' => 'Hình ảnh phải có định dạng jpeg, png, jpg, gif, webp.',
            'front_id_card_image.image' => 'File tải lên phải là hình ảnh.',
            'front_id_card_image.mimes' => 'Hình ảnh phải có định dạng jpeg, png, jpg, gif, webp.',
            'back_id_card_image.image' => 'File tải lên phải là hình ảnh.',
            'back_id_card_image.mimes' => 'Hình ảnh phải có định dạng jpeg, png, jpg, gif, webp.',
            'birthday.date' => 'Ngày sinh không hợp lệ.',
            'role.required' => 'Role là bắt buộc.',
            'role.in' => 'Vai trò phải là Người đăng ký, Người thuê hoặc Quản trị viên.',
            'status.required' => 'Status là bắt buộc.',
            'status.in' => 'Trạng thái phải là Hoạt động hoặc Khóa.',
        ];
    }
}
