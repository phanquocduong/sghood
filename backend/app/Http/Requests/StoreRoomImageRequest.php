<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRoomImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'images' => 'required|array|min:1', // Yêu cầu mảng images, ít nhất 1 file
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Mỗi file phải là hình ảnh hợp lệ
        ];
    }

    public function messages(): array
    {
        return [
            'images.required' => 'Bạn chưa chọn hình ảnh nào',
            'images.array' => 'Danh sách hình ảnh không hợp lệ',
            'images.min' => 'Bạn phải chọn ít nhất một hình ảnh',
            'images.*.required' => 'Bạn chưa chọn hình ảnh',
            'images.*.image' => 'File tải lên phải là hình ảnh',
            'images.*.mimes' => 'Hình ảnh phải có định dạng jpeg, png, jpg, gif, webp',
            'images.*.max' => 'Hình ảnh không được lớn hơn 2MB',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Dữ liệu không hợp lệ',
            'errors' => $validator->errors(),
        ], 422));
    }
}
