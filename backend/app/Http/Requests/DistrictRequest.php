<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DistrictRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // Quy tắc validate
    public function rules(): array
    {
        $isUpdate = $this->method() === 'PUT' || $this->method() === 'PATCH';
        $districtId = $this->route('id');

        return [
            'name' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:100',
                $isUpdate ? "unique:districts,name,{$districtId}" : 'unique:districts,name',
            ],
            'image' => $isUpdate ? 'sometimes|required|mimes:jpeg,png,jpg,webp|max:2048' : 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }

    // Tùy chỉnh thông báo lỗi
    public function messages(): array
    {
        return [
            'name.required' => 'Tên quận/huyện là bắt buộc.',
            'name.string' => 'Tên quận/huyện phải là chuỗi ký tự.',
            'name.max' => 'Tên quận/huyện không được vượt quá 100 ký tự.',
            'name.unique' => 'Tên quận/huyện đã tồn tại.',
            'image.required' => 'Hình ảnh là bắt buộc.',
            'image.image' => 'Tệp phải là một hình ảnh.',
            'image.mimes' => 'Hình ảnh chỉ chấp nhận các định dạng: jpeg, png, jpg, webp.',
            'image.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
        ];
    }
}
