<?php

namespace App\Http\Requests\Apis;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Lớp xác thực dữ liệu cho yêu cầu cập nhật hồ sơ người dùng.
 */
class UpdateProfileRequest extends FormRequest
{
    /**
     * Kiểm tra quyền truy cập của người dùng.
     *
     * @return bool True nếu người dùng được phép thực hiện yêu cầu
     */
    public function authorize(): bool
    {
        return true; // Cho phép tất cả người dùng đã đăng nhập thực hiện
    }

    /**
     * Các quy tắc xác thực dữ liệu đầu vào.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string> Quy tắc xác thực
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255', // Tên là bắt buộc nếu được gửi, tối đa 255 ký tự
            'gender' => 'sometimes|nullable|in:Nam,Nữ,Khác', // Giới tính là tùy chọn, chỉ chấp nhận các giá trị hợp lệ
            'birthdate' => 'sometimes|nullable|date', // Ngày sinh là tùy chọn, phải là định dạng ngày hợp lệ
            'address' => 'sometimes|nullable|string|max:255', // Địa chỉ là tùy chọn, tối đa 255 ký tự
            'avatar' => 'sometimes|nullable|image|max:2048', // Ảnh đại diện là tùy chọn, phải là ảnh và tối đa 2MB
        ];
    }

    /**
     * Tùy chỉnh thông báo lỗi cho các quy tắc xác thực.
     *
     * @return array Thông báo lỗi tùy chỉnh
     */
    public function messages()
    {
        return [
            'name.required' => 'Họ tên là bắt buộc.',
            'gender.in' => 'Giới tính không hợp lệ.',
            'birthdate.date' => 'Ngày sinh không hợp lệ.', // Sửa lỗi typo trong mã gốc (birthDate -> birthdate)
            'avatar.image' => 'File phải là ảnh.',
            'avatar.max' => 'Kích thước ảnh không được vượt quá 2MB.',
        ];
    }
}
