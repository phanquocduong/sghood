<?php

namespace App\Http\Requests\Apis;

use App\Models\Config;
use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Lấy danh sách timeSlots từ Config
        $timeSlots = Config::getValue('time_slots_viewing_schedule', [
            '8:00 sáng - 8:30 sáng',
            '9:00 sáng - 9:30 sáng',
            '10:00 sáng - 10:30 sáng',
            '11:00 sáng - 11:30 sáng',
            '13:00 chiều - 13:30 chiều',
            '14:00 chiều - 14:30 chiều',
            '15:00 chiều - 15:30 chiều',
            '16:00 chiều - 16:30 chiều',
            '17:00 chiều - 17:30 chiều'
        ]);

        // Đảm bảo timeSlots là mảng
        if (!is_array($timeSlots)) {
            try {
                $timeSlots = json_decode($timeSlots, true);
                if (!is_array($timeSlots)) {
                    // Nếu vẫn không phải mảng, sử dụng danh sách mặc định
                    $timeSlots = [
                        '8:00 sáng - 8:30 sáng',
                        '9:00 sáng - 9:30 sáng',
                        '10:00 sáng - 10:30 sáng',
                        '11:00 sáng - 11:30 sáng',
                        '13:00 chiều - 13:30 chiều',
                        '14:00 chiều - 14:30 chiều',
                        '15:00 chiều - 15:30 chiều',
                        '16:00 chiều - 16:30 chiều',
                        '17:00 chiều - 17:30 chiều'
                    ];
                }
            } catch (\Exception $e) {
                // Nếu parse JSON thất bại, sử dụng danh sách mặc định
                $timeSlots = [
                    '8:00 sáng - 8:30 sáng',
                    '9:00 sáng - 9:30 sáng',
                    '10:00 sáng - 10:30 sáng',
                    '11:00 sáng - 11:30 sáng',
                    '13:00 chiều - 13:30 chiều',
                    '14:00 chiều - 14:30 chiều',
                    '15:00 chiều - 15:30 chiều',
                    '16:00 chiều - 16:30 chiều',
                    '17:00 chiều - 17:30 chiều'
                ];
            }
        }

        // Chuyển mảng timeSlots thành chuỗi phân tách bằng dấu phẩy
        $timeSlotsString = implode(',', $timeSlots);

        return [
            'date' => 'required|date_format:d/m/Y',
            'timeSlot' => [
                'required',
                'in:' . $timeSlotsString,
            ],
            'message' => 'nullable|string|max:255',
            'user_id' => 'required|exists:users,id',
            'motel_id' => 'required|exists:motels,id',
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'Vui lòng chọn ngày xem.',
            'date.date_format' => 'Định dạng ngày phải là dd/mm/yyyy.',
            'timeSlot.required' => 'Vui lòng chọn khung giờ.',
            'timeSlot.in' => 'Khung giờ không hợp lệ.',
            'message.string' => 'Lời nhắn phải là chuỗi ký tự.',
            'message.max' => 'Lời nhắn không được vượt quá 255 ký tự.',
            'user_id.required' => 'ID người dùng là bắt buộc.',
            'user_id.exists' => 'Người dùng không tồn tại.',
            'motel_id.required' => 'ID nhà trọ là bắt buộc.',
            'motel_id.exists' => 'Nhà trọ không tồn tại.',
        ];
    }
}
