<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreAmenityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:amenities,name',
            'type' => 'required|in:Nhà trọ,Phòng trọ',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên tiện nghi là bắt buộc.',
            'name.string' => 'Tên tiện nghi phải là chuỗi ký tự.',
            'name.max' => 'Tên tiện nghi không được vượt quá 100 ký tự.',
            'name.unique' => 'Tên tiện nghi đã tồn tại. Vui lòng chọn tên khác.',
            'type.required' => 'Trạng thái phòng không được để trống',
            'type.in' => 'Trạng thái phải là "Nhà trọ", "Phòng trọ"',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Dữ liệu không hợp lệ.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
