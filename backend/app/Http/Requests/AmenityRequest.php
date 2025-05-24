<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AmenityRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $isUpdate = $this->method() === 'PUT' || $this->method() === 'PATCH';

        return [
            'name' => $isUpdate ? 'sometimes|required|string|max:100|unique:amenities,name' : 'required|string|max:100|unique:amenities,name',
            'type' => $isUpdate ? 'sometimes|required|in:Nhà trọ,Phòng trọ' : 'required|in:Nhà trọ,Phòng trọ',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập tên tiện nghi.',
            'name.string' => 'Tên tiện nghi phải là một chuỗi.',
            'name.max' => 'Tên tiện nghi không được vượt quá 100 ký tự.',
            'name.unique' => 'Tên tiện nghi đã tồn tại. Vui lòng chọn tên khác.',
            'type.required' => 'Vui lòng chọn loại tiện nghi.',
            'type.in' => 'Loại tiện nghi phải là "Nhà trọ" hoặc "Phòng trọ".',
        ];
    }
}
