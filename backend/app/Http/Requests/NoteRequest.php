<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NoteRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'content' => [
                'required',
                'string',
                'max:255',
                'min:1'
            ],
            'type' => [
                'required',
                'string',
                'max:50',
            ]
        ];
    }

    public function messages()
    {
        return [
            'content.required' => 'Vui lòng nhập nội dung ghi chú.',
            'content.string' => 'Nội dung ghi chú phải là một chuỗi.',
            'content.max' => 'Nội dung ghi chú không được vượt quá 255 ký tự.',
            'content.min' => 'Nội dung ghi chú không được để trống.',
            'type.required' => 'Vui lòng chọn loại ghi chú.',
            'type.string' => 'Loại ghi chú phải là một chuỗi.',
            'type.max' => 'Nội dung loại không được vượt quá 50 ký tự.',
        ];
    }
}
