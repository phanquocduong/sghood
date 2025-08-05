<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
{
    return [
        'status' => 'required|in:Chờ kiểm kê,Đã kiểm kê',
        'item_name.*' => 'required|string|max:255',
        'item_condition.*' => 'nullable|string|max:1000',
        'item_cost.*' => 'nullable|numeric|min:0',
        'images.*' => 'nullable|image|mimes:jpeg,png,gif,jpg|max:2048',
        'images_to_delete.*' => 'nullable|string',
        'existing_images.*' => 'nullable|string',
    ];
}

public function messages(): array
{
    return [
        'status.required' => 'Trạng thái là bắt buộc.',
        'status.in' => 'Trạng thái phải là "Chờ kiểm kê" hoặc "Đã kiểm kê".',
        'item_name.*.required' => 'Tên mục là bắt buộc.',
        'item_name.*.string' => 'Tên mục phải là chuỗi ký tự.',
        'item_name.*.max' => 'Tên mục không được vượt quá 255 ký tự.',
        'item_condition.*.string' => 'Tình trạng phải là chuỗi ký tự.',
        'item_condition.*.max' => 'Tình trạng không được vượt quá 1000 ký tự.',
        'item_cost.*.numeric' => 'Chi phí phải là số.',
        'item_cost.*.min' => 'Chi phí không được nhỏ hơn 0.',
        'images.*.image' => 'File phải là hình ảnh.',
        'images.*.mimes' => 'Hình ảnh phải có định dạng jpeg, png, gif hoặc jpg.',
        'images.*.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
    ];
}
}
