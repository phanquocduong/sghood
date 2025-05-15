<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoomRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $isUpdate = $this->method() === 'PUT' || $this->method() === 'PATCH';

        return [
            'name' => $isUpdate ? 'sometimes|required|string|max:255|unique:rooms,name,' . $this->route('id') . ',id,motel_id,' . $this->input('motel_id', $this->input('motel_id')) : 'required|string|max:255|unique:rooms,name,NULL,id,motel_id,' . $this->input('motel_id'),
            'price' => $isUpdate ? 'sometimes|required|numeric|min:0' : 'required|numeric|min:0',
            'area' => $isUpdate ? 'sometimes|required|numeric|min:0' : 'required|numeric|min:0',
            'status' => $isUpdate ? 'sometimes|required|in:Còn trống,Đã thuê,Đang sửa,Ẩn' : 'required|in:Còn trống,Đã thuê,Đang sửa,Ẩn',
            'motel_id' => $isUpdate ? 'sometimes|required|exists:motels,id' : 'required|exists:motels,id',
            'device_token' => 'nullable|string',
            'images' => $isUpdate ? 'sometimes|required|array' : 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'amenities' => $isUpdate ? 'sometimes|required|array' : 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập tên phòng.',
            'name.string' => 'Tên phòng phải là một chuỗi.',
            'name.max' => 'Tên phòng không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên phòng đã tồn tại trong nhà trọ này. Vui lòng chọn tên khác.',
            'price.required' => 'Vui lòng nhập giá phòng.',
            'price.numeric' => 'Giá phòng phải là số.',
            'price.min' => 'Giá phòng phải lớn hơn hoặc bằng 0.',
            'area.required' => 'Vui lòng nhập diện tích phòng.',
            'area.numeric' => 'Diện tích phòng phải là số.',
            'area.min' => 'Diện tích phòng phải lớn hơn hoặc bằng 0.',
            'status.required' => 'Vui lòng chọn trạng thái phòng.',
            'status.in' => 'Trạng thái phải là "Còn trống", "Đã thuê", "Đang sửa" hoặc "Ẩn".',
            'motel_id.required' => 'Vui lòng chọn nhà trọ.',
            'motel_id.exists' => 'Nhà trọ không tồn tại.',
            'device_token.string' => 'Device token phải là chuỗi ký tự.',
            'images.array' => 'Bộ sưu tập ảnh phải là một mảng.',
            'images.*.image' => 'Ảnh phải là các file ảnh hợp lệ.',
            'images.*.mimes' => 'Ảnh phải có định dạng jpeg, png, jpg, gif, hoặc webp.',
            'images.*.max' => 'Ảnh không được vượt quá 2MB.',
            'amenities.array' => 'Danh sách tiện nghi phải là một mảng.',
            'amenities.*.exists' => 'Tiện nghi không tồn tại.',
        ];
    }
}
