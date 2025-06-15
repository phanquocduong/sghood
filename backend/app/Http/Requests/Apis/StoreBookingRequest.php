<?php

namespace App\Http\Requests\Apis;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
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
            'room_id' => 'required|exists:rooms,id',
            'start_date' => 'required|date_format:d/m/Y',
            'duration' => 'required|string|in:1 năm,2 năm,3 năm',
            'note' => 'nullable|string|max:500'
        ];
    }

    public function messages(): array
    {
        return [
            'room_id.required' => 'Vui lòng chọn phòng.',
            'room_id.exists' => 'Phòng không tồn tại',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu',
            'start_date.date_format' => 'Ngày bắt đầu phải có định dạng DD/MM/YYYY.',
            'duration.required' => 'Vui lòng chọn thời gian thuê',
            'duration.in' => 'Thời gian thuê phải là 1 năm, 2 năm hoặc 3 năm',
            'note.max' => 'Ghi chú không được vượt quá 500 ký tự'
        ];
    }
}
