<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MotelImageRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'motel_id' => 'required|integer|exists:motels,id',
            'image_url' => 'required|string|max:255',
        ];
    }
    public function messages()
    {
        return [
            'motel_id.required' => 'Vui lòng nhập ID motel.',
            'motel_id.integer' => 'ID motel phải là một số nguyên.',
            'motel_id.exists' => 'ID motel không tồn tại trong cơ sở dữ liệu.',
            'image_url.required' => 'Vui lòng nhập URL hình ảnh.',
            'image_url.string' => 'URL hình ảnh phải là một chuỗi.',
            'image_url.max' => 'URL hình ảnh không được vượt quá 255 ký tự.',
        ];
    }
}

