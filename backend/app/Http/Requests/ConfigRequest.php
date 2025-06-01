<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConfigRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Thay bằng logic kiểm tra quyền nếu cần (ví dụ: kiểm tra user là admin)
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'config_key' => [
                'required',
                'string',
                'max:255',
            ],
            'config_value' => 'required',
            'description' => 'nullable|string|max:255',
            'config_type' => 'required|in:TEXT,URL,HTML,JSON',
        ];

        // Khi cập nhật, bỏ qua config_key hiện tại trong kiểm tra unique
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['config_key'][] = Rule::unique('configs', 'config_key')->ignore($this->route('id'));
        } else {
            $rules['config_key'][] = 'unique:configs,config_key';
        }

        return $rules;
    }

    /**
     * Get custom error messages for validation.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'config_key.required' => 'Khóa cấu hình là bắt buộc.',
            'config_key.string' => 'Khóa cấu hình phải là chuỗi ký tự.',
            'config_key.max' => 'Khóa cấu hình không được dài quá 255 ký tự.',
            'config_key.unique' => 'Khóa cấu hình đã tồn tại, vui lòng chọn khóa khác.',
            'config_value.required' => 'Giá trị cấu hình là bắt buộc.',
            'description.string' => 'Mô tả phải là chuỗi ký tự.',
            'description.max' => 'Mô tả không được dài quá 255 ký tự.',
            'config_type.required' => 'Loại cấu hình là bắt buộc.',
            'config_type.in' => 'Loại cấu hình phải là một trong các giá trị: TEXT, URL, HTML, JSON.',
        ];
    }
}