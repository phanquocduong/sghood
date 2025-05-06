<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('room') ?? 'NULL';
        return [
            'name' => 'required|string|max:255|unique:rooms,name,' . $id . ',id,motel_id,' . $this->input('motel_id'),
            'price' => 'required|numeric|min:0',
            'area' => 'required|numeric|min:0',
            'status' => 'required|in:Còn trống,Đã thuê,Đang sửa,Ẩn',
            'motel_id' => 'required|exists:motels,id',
            'device_token' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Bạn chưa nhập tên phòng',
            'name.string' => 'Tên phòng phải là dạng ký tự',
            'name.max' => 'Độ dài tên phòng không vượt quá 255 ký tự',
            'name.unique' => 'Tên phòng đã tồn tại trong nhà trọ này',
            'price.required' => 'Bạn chưa nhập giá phòng',
            'price.numeric' => 'Giá phòng phải là số',
            'price.min' => 'Giá phòng không được nhỏ hơn 0',
            'area.required' => 'Bạn chưa nhập diện tích phòng',
            'area.numeric' => 'Diện tích phòng phải là số',
            'area.min' => 'Diện tích phòng không được nhỏ hơn 0',
            'status.required' => 'Trạng thái phòng không được để trống',
            'status.in' => 'Trạng thái phải là "Còn trống", "Đã thuê", "Đang sửa" hoặc "Ẩn"',
            'motel_id.required' => 'Bạn chưa chọn nhà trọ',
            'motel_id.exists' => 'Nhà trọ không tồn tại',
            'device_token.string' => 'Device token phải là chuỗi ký tự',
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
