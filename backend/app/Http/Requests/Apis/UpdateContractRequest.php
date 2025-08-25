<?php

namespace App\Http\Requests\Apis;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * Yêu cầu xác thực dữ liệu khi cập nhật hợp đồng.
 */
class UpdateContractRequest extends FormRequest
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
     * Các quy tắc xác thực cho yêu cầu cập nhật hợp đồng.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'contract_content' => ['required', 'string', 'max:65535'], // Nội dung hợp đồng là bắt buộc, chuỗi, tối đa 65535 ký tự
            'identity_images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'], // Hình ảnh giấy tờ tùy thân, nếu có, phải là ảnh jpeg/png/jpg, tối đa 2MB
        ];
    }

    /**
     * Thông báo lỗi tùy chỉnh bằng tiếng Việt.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'contract_content.required' => 'Nội dung hợp đồng là bắt buộc.',
            'contract_content.string' => 'Nội dung hợp đồng phải là chuỗi ký tự.',
            'contract_content.max' => 'Nội dung hợp đồng không được vượt quá :max ký tự.',
            'identity_images.*.image' => 'Tệp tải lên phải là hình ảnh.',
            'identity_images.*.mimes' => 'Hình ảnh phải có định dạng jpeg, png hoặc jpg.',
            'identity_images.*.max' => 'Hình ảnh không được lớn hơn :max KB.',
        ];
    }
}
