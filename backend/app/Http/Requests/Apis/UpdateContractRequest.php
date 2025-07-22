<?php

namespace App\Http\Requests\Apis;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'contract_content' => ['required', 'string', 'max:65535'],
            'identity_images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ];
    }

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
