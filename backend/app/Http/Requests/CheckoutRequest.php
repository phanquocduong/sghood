<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'check_out_date' => 'required|date',
            'has_left' => 'required|boolean',
            'status' => 'required|in:Chờ kiểm kê,Đã kiểm kê,Kiểm kê lại',
            'item_name' => 'nullable|array',
            'item_name.*' => 'nullable|string|max:255',
            'item_condition' => 'nullable|array',
            'item_condition.*' => 'nullable|string|max:255',
            'item_cost' => 'nullable|array',
            'item_cost.*' => 'nullable|numeric|min:0',
            'images' => 'nullable|array',
            'images.*' => 'nullable|file|image|mimes:jpeg,png,jpg,gif|max:5120',
            'existing_images' => 'nullable|array',
            'existing_images.*' => 'nullable|string',
            'deleted_images' => 'nullable|array',
            'deleted_images.*' => 'nullable|string',
            'deduction_amount' => 'nullable|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'check_out_date.required' => 'Ngày checkout là bắt buộc.',
            'has_left.required' => 'Trạng thái rời đi là bắt buộc.',
            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'images.*.image' => 'File phải là hình ảnh.',
            'images.*.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, gif.',
            'images.*.max' => 'Kích thước hình ảnh không được vượt quá 5MB.',
        ];
    }
}
