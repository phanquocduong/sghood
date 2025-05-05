<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Cho phép tất cả, có thể thêm logic kiểm tra quyền
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:rooms,name,NULL,id,motel_id,' . $this->input('motel_id'),
            'price' => 'required|numeric|min:0',
            'area' => 'required|numeric|min:0',
            'status' => 'required|in:Còn trống,Đã thuê,Đang sửa,Ẩn',
            'motel_id' => 'required|exists:motels,id',
            'device_token' => 'nullable|string', // Cho Firebase
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên phòng là bắt buộc.',
            'name.string' => 'Tên phòng phải là chuỗi ký tự.',
            'name.max' => 'Tên phòng không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên phòng đã tồn tại trong nhà trọ này. Vui lòng chọn tên khác.',
            'price.required' => 'Giá phòng là bắt buộc.',
            'price.numeric' => 'Giá phòng phải là số.',
            'price.min' => 'Giá phòng không được âm.',
            'area.required' => 'Diện tích phòng là bắt buộc.',
            'area.numeric' => 'Diện tích phòng phải là số.',
            'area.min' => 'Diện tích phòng không được âm.',
            'status.required' => 'Trạng thái phòng là bắt buộc.',
            'status.in' => 'Trạng thái phải là "Còn trống", "Đã thuê", "Đang sửa" hoặc "Ẩn".',
            'motel_id.required' => 'ID nhà trọ là bắt buộc.',
            'motel_id.exists' => 'Nhà trọ không tồn tại.',
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
