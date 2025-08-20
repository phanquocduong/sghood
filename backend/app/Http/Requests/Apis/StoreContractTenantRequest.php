<?php

namespace App\Http\Requests\Apis;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StoreContractTenantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check(); // Cho phép nếu người dùng đã đăng nhập
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|regex:/^([0-9]{10})$/',
            'email' => 'nullable|email|max:255',
            'gender' => 'nullable|string|in:Nam,Nữ,Khác',
            'birthdate' => 'nullable|date_format:d/m/Y',
            'address' => 'nullable|string|max:500',
            'relation_with_primary' => 'required|string|max:255',
            'identity_document' => 'required|string|regex:/^\d{9,12}$/',
            'bypass_extract' => 'boolean',
            'identity_images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            'identity_images' => ['required', 'array', function ($attribute, $value, $fail) {
                if (count($value) !== 2) {
                    $fail('Vui lòng tải lên đúng 2 ảnh: mặt trước và mặt sau CCCD.');
                }
            }],
        ];
    }

    /**
     * Get custom error messages for validation rules.
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
            'identity_document.required' => 'Số CCCD là bắt buộc.',
            'identity_document.regex' => 'Số CCCD phải có từ 9 đến 12 chữ số.',
            'bypass_extract.boolean' => 'Bypass extract phải là giá trị boolean.',
            'identity_images.required' => 'Vui lòng tải lên ảnh CCCD.',
            'identity_images.*.image' => 'Tệp tải lên phải là ảnh.',
            'identity_images.*.mimes' => 'Ảnh CCCD phải có định dạng JPEG hoặc PNG.',
            'identity_images.*.max' => 'Ảnh CCCD không được vượt quá 2MB.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Chuyển bypass_extract thành boolean nếu cần
        $this->merge([
            'bypass_extract' => filter_var($this->input('bypass_extract'), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
