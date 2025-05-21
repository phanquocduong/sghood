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
            'motel_id' => $isUpdate ? 'sometimes|required|exists:motels,id' : 'required|exists:motels,id',
            'name' => $isUpdate ? 'sometimes|required|string|max:255|unique:rooms,name,' . $this->route('id') . ',id,motel_id,' . $this->input('motel_id') : 'required|string|max:255|unique:rooms,name,NULL,id,motel_id,' . $this->input('motel_id'),
            'price' => $isUpdate ? 'sometimes|required|integer|min:0' : 'required|integer|min:0',
            'area' => $isUpdate ? 'sometimes|required|numeric|min:0' : 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'status' => $isUpdate ? 'sometimes|required|in:Trống,Đã thuê,Sửa chữa,Ẩn' : 'required|in:Trống,Đã thuê,Sửa chữa,Ẩn',
            'note' => 'nullable|string|max:255',
            'images' => $isUpdate ? 'sometimes|array' : 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'amenities' => $isUpdate ? 'sometimes|array' : 'nullable|array',
            'amenities.*' => 'exists:amenities,id',
        ];
    }

    public function messages()
    {
        return [
            'motel_id.required' => 'Vui lòng chọn nhà trọ.',
            'motel_id.exists' => 'Nhà trọ không tồn tại.',
            'name.required' => 'Vui lòng nhập tên phòng.',
            'name.string' => 'Tên phòng phải là một chuỗi.',
            'name.max' => 'Tên phòng không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên phòng đã tồn tại trong nhà trọ này. Vui lòng chọn tên khác.',
            'price.required' => 'Vui lòng nhập giá phòng.',
            'price.integer' => 'Giá phòng phải là số nguyên.',
            'price.min' => 'Giá phòng phải lớn hơn hoặc bằng 0.',
            'area.required' => 'Vui lòng nhập diện tích phòng.',
            'area.numeric' => 'Diện tích phòng phải là số.',
            'area.min' => 'Diện tích phòng phải lớn hơn hoặc bằng 0.',
            'description.string' => 'Mô tả phải là một chuỗi.',
            'description.max' => 'Mô tả không được vượt quá 1000 ký tự.',
            'status.required' => 'Vui lòng chọn trạng thái phòng.',
            'status.in' => 'Trạng thái phải là "Trống", "Đã thuê", "Sửa chữa" hoặc "Ẩn".',
            'note.string' => 'Ghi chú phải là một chuỗi.',
            'note.max' => 'Ghi chú không được vượt quá 255 ký tự.',
            'images.array' => 'Bộ sưu tập ảnh phải là một mảng.',
            'images.*.image' => 'Ảnh phải là các file ảnh hợp lệ.',
            'images.*.mimes' => 'Ảnh phải có định dạng jpeg, png, jpg, gif, hoặc webp.',
            'images.*.max' => 'Ảnh không được vượt quá 2MB.',
            'amenities.array' => 'Danh sách tiện nghi phải là một mảng.',
            'amenities.*.exists' => 'Tiện nghi không tồn tại.',
        ];
    }
}
