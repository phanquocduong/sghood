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
        return true; // Điều chỉnh theo logic phân quyền của bạn
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'check_out_date' => 'sometimes|required|date',
            'has_left' => 'sometimes|required|boolean',
            'status' => 'sometimes|required|in:Chờ kiểm kê,Đã kiểm kê,Kiểm kê lại',
            'item_name.*' => 'nullable|string|max:255', // Thay required thành nullable
            'item_condition.*' => 'nullable|string|max:1000',
            'item_cost.*' => 'nullable|numeric|min:0',
            'images.*' => 'nullable|image|mimes:jpeg,png,gif,jpg|max:2048',
            'images_to_delete.*' => 'nullable|string',
            'existing_images.*' => 'nullable|string',
        ];
    }

    /**
     * Get custom error messages for validation.
     */
    public function messages(): array
    {
        return [
            'check_out_date.required' => 'Ngày checkout là bắt buộc.',
            'check_out_date.date' => 'Ngày checkout phải là định dạng ngày hợp lệ.',
            'has_left.required' => 'Trạng thái rời đi là bắt buộc.',
            'has_left.boolean' => 'Trạng thái rời đi phải là có hoặc không.',
            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái phải là "Chờ kiểm kê", "Đã kiểm kê" hoặc "Kiểm kê lại".',
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
