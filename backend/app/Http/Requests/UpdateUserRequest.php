<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Models\User;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $userId = $this->route('id');
        $user = User::findOrFail($userId);
        $role = $user->role;

        $rules = [];

        if (in_array($role, ['Người đăng ký', 'Người thuê'])) {
            $rules = [
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $userId,
                'birthday' => 'sometimes|required|date',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'front_id_card_image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'back_id_card_image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'role' => 'prohibited',
                'status' => 'prohibited',
            ];
        } elseif ($role === 'Quản trị viên') {
            $rules = [
                'name' => 'prohibited',
                'email' => 'prohibited',
                'birthday' => 'prohibited',
                'avatar' => 'prohibited',
                'front_id_card_image' => 'prohibited',
                'back_id_card_image' => 'prohibited',
                'role' => 'sometimes|required|in:Người đăng ký,Người thuê,Quản trị viên',
                'status' => 'sometimes|required|in:Hoạt động,Khóa',
            ];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập tên.',
            'name.string' => 'Tên phải là một chuỗi.',
            'name.max' => 'Tên không được vượt quá 255 ký tự.',
            'name.prohibited' => 'Quản trị viên không được phép thay đổi tên.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không hợp lệ.',
            'email.unique' => 'Email đã tồn tại. Vui lòng chọn email khác.',
            'email.prohibited' => 'Quản trị viên không được phép thay đổi email.',
            'birthday.required' => 'Vui lòng nhập ngày sinh.',
            'birthday.date' => 'Ngày sinh không hợp lệ.',
            'birthday.prohibited' => 'Quản trị viên không được phép thay đổi ngày sinh.',
            'avatar.image' => 'File tải lên phải là hình ảnh.',
            'avatar.mimes' => 'Hình ảnh phải có định dạng jpeg, png, jpg, gif, hoặc webp.',
            'avatar.max' => 'Hình ảnh không được vượt quá 2MB.',
            'avatar.prohibited' => 'Quản trị viên không được phép thay đổi avatar.',
            'front_id_card_image.required' => 'Vui lòng tải lên mặt trước giấy tờ tùy thân.',
            'front_id_card_image.image' => 'File tải lên phải là hình ảnh.',
            'front_id_card_image.mimes' => 'Hình ảnh phải có định dạng jpeg, png, jpg, gif, hoặc webp.',
            'front_id_card_image.max' => 'Hình ảnh không được vượt quá 2MB.',
            'front_id_card_image.prohibited' => 'Quản trị viên không được phép thay đổi mặt trước giấy tờ tùy thân.',
            'back_id_card_image.required' => 'Vui lòng tải lên mặt sau giấy tờ tùy thân.',
            'back_id_card_image.image' => 'File tải lên phải là hình ảnh.',
            'back_id_card_image.mimes' => 'Hình ảnh phải có định dạng jpeg, png, jpg, gif, hoặc webp.',
            'back_id_card_image.max' => 'Hình ảnh không được vượt quá 2MB.',
            'back_id_card_image.prohibited' => 'Quản trị viên không được phép thay đổi mặt sau giấy tờ tùy thân.',
            'role.required' => 'Vui lòng chọn vai trò.',
            'role.in' => 'Vai trò phải là "Người đăng ký", "Người thuê" hoặc "Quản trị viên".',
            'role.prohibited' => 'Người đăng ký hoặc Người thuê không được phép thay đổi vai trò.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái phải là "Hoạt động" hoặc "Khóa".',
            'status.prohibited' => 'Người đăng ký hoặc Người thuê không được phép thay đổi trạng thái.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Dữ liệu không hợp lệ.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
