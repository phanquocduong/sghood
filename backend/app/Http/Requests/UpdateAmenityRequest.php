<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateAmenityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $amenity = $this->route('amenity');
        $amenityId = $amenity ? $amenity->id : null;

        return [
            'name' => [
                'nullable',
                'sometimes',
                'string',
                'max:100',
                'unique:amenities,name,' . ($amenityId ?? 'NULL') . ',id',
            ],
            'description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Tên tiện nghi phải là chuỗi ký tự.',
            'name.max' => 'Tên tiện nghi không được vượt quá 100 ký tự.',
            'name.unique' => 'Tên tiện nghi đã tồn tại. Vui lòng chọn tên khác.',
            'description.string' => 'Mô tả phải là chuỗi ký tự.',
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
