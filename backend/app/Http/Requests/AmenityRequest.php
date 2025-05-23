<?php

namespace App\Http\Requests;

use App\Models\Amenity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AmenityRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $isUpdate = $this->method() === 'PUT' || $this->method() === 'PATCH';
        $amenityId = $isUpdate ? $this->route('id') : null;

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('amenities', 'name')->ignore($amenityId),
            ],
            'type' => 'required|in:Nhà trọ,Phòng trọ',
            'status' => 'required|in:Hoạt động,Không hoạt động',
            'order' => $isUpdate ? 'sometimes|required|integer|min:1' : 'integer|min:1',        ];
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
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái phải là "Hoạt động" hoặc "Không hoạt động".',
            'order.required' => 'Vui lòng nhập thứ tự sắp xếp.',
            'order.integer' => 'Thứ tự sắp xếp phải là một số nguyên.',
            'order.min' => 'Thứ tự sắp xếp không được nhỏ hơn 1.',
        ];
    }
}
