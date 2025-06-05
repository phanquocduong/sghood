<?php

namespace App\Http\Requests\Apis;

use Illuminate\Foundation\Http\FormRequest;

class StoreViewingScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => 'required|date_format:d/m/Y',
            'timeSlot' => 'required|in:8:30 sáng - 9:00 sáng,9:00 sáng - 9:30 sáng,9:30 sáng - 10:00 sáng,10:00 sáng - 10:30 sáng,13:00 chiều - 13:30 chiều,13:30 chiều - 14:00 chiều,14:00 chiều - 14:30 chiều',
            'message' => 'nullable|string|max:255',
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
        ];
    }

    /**
     * Get the custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date.required' => 'Vui lòng chọn ngày.',
            'date.date_format' => 'Định dạng ngày phải là dd/mm/yyyy.',
            'timeSlot.required' => 'Vui lòng chọn khung giờ.',
            'timeSlot.in' => 'Khung giờ không hợp lệ.',
            'message.string' => 'Lời nhắn phải là chuỗi ký tự.',
            'message.max' => 'Lời nhắn không được vượt quá 255 ký tự.',
            'user_id.required' => 'Vui lòng cung cấp ID người dùng.',
            'user_id.exists' => 'ID người dùng không tồn tại.',
            'room_id.required' => 'Vui lòng cung cấp ID phòng.',
            'room_id.exists' => 'ID phòng không tồn tại.',
        ];
    }
}
