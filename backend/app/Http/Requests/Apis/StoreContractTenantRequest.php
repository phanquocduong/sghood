<?php

namespace App\Http\Requests\Apis;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Yêu cầu xác thực dữ liệu khi thêm mới người ở cùng hợp đồng.
 */
class StoreContractTenantRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có được phép thực hiện yêu cầu này không.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::check(); // Yêu cầu người dùng phải đăng nhập
    }

    /**
     * Các quy tắc xác thực cho yêu cầu thêm người ở cùng.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255', // Tên là bắt buộc, chuỗi, tối đa 255 ký tự
            'phone' => 'required|string|regex:/^([0-9]{10})$/', // Số điện thoại là bắt buộc, đúng 10 chữ số
            'email' => 'nullable|email|max:255', // Email không bắt buộc, nếu có phải đúng định dạng, tối đa 255 ký tự
            'gender' => 'nullable|string|in:Nam,Nữ,Khác', // Giới tính không bắt buộc, chỉ được là Nam, Nữ hoặc Khác
            'birthdate' => 'nullable|date_format:d/m/Y', // Ngày sinh không bắt buộc, định dạng dd/mm/yyyy
            'address' => 'nullable|string|max:500', // Địa chỉ không bắt buộc, tối đa 500 ký tự
            'relation_with_primary' => 'required|string|max:255', // Mối quan hệ với người thuê chính là bắt buộc, tối đa 255 ký tự
            'identity_images.*' => 'image|mimes:jpeg,png,jpg|max:2048', // Mỗi ảnh CCCD phải là ảnh, định dạng jpeg/png/jpg, tối đa 2MB
            'identity_images' => ['required', 'array', function ($attribute, $value, $fail) {
                if (count($value) !== 2) {
                    $fail('Vui lòng tải lên đúng 2 ảnh: mặt trước và mặt sau CCCD.');
                }
            }], // Yêu cầu đúng 2 ảnh CCCD
        ];
    }

    /**
     * Thông báo lỗi tùy chỉnh bằng tiếng Việt.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên là bắt buộc.',
            'name.string' => 'Tên phải là chuỗi ký tự.',
            'name.max' => 'Tên không được vượt quá 255 ký tự.',
            'phone.required' => 'Số điện thoại là bắt buộc.',
            'phone.regex' => 'Số điện thoại phải là 10 chữ số.',
            'email.email' => 'Email không hợp lệ.',
            'email.max' => 'Email không được vượt quá 255 ký tự.',
            'gender.in' => 'Giới tính phải là Nam, Nữ hoặc Khác.',
            'birthdate.date_format' => 'Ngày sinh phải có định dạng dd/mm/yyyy.',
            'address.max' => 'Địa chỉ không được vượt quá 500 ký tự.',
            'relation_with_primary.required' => 'Mối quan hệ với người thuê chính là bắt buộc.',
            'relation_with_primary.max' => 'Mối quan hệ không được vượt quá 255 ký tự.',
            'identity_images.required' => 'Vui lòng tải lên ảnh CCCD.',
            'identity_images.*.image' => 'Tệp tải lên phải là ảnh.',
            'identity_images.*.mimes' => 'Ảnh CCCD phải có định dạng JPEG hoặc PNG.',
            'identity_images.*.max' => 'Ảnh CCCD không được vượt quá 2MB.',
        ];
    }
}
