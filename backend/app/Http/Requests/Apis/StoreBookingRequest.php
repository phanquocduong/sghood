<?php

namespace App\Http\Requests\Apis;

use App\Models\Config;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Yêu cầu xác thực dữ liệu khi tạo mới một đặt phòng.
 */
class StoreBookingRequest extends FormRequest
{
    /**
     * Xác định xem người dùng có được phép thực hiện yêu cầu này không.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Cho phép tất cả người dùng thực hiện yêu cầu
    }

    /**
     * Các quy tắc xác thực cho yêu cầu tạo đặt phòng.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Lấy danh sách thời gian thuê từ cấu hình
        $durations = Config::getValue('booking_durations', [
            '1 năm',
            '2 năm',
            '3 năm',
            '4 năm',
            '5 năm'
        ]);

        // Đảm bảo durations là mảng
        if (!is_array($durations)) {
            try {
                $durations = json_decode($durations, true);
                if (!is_array($durations)) {
                    // Sử dụng danh sách mặc định nếu không phải mảng
                    $durations = [
                        '1 năm',
                        '2 năm',
                        '3 năm',
                        '4 năm',
                        '5 năm'
                    ];
                }
            } catch (\Exception $e) {
                // Sử dụng danh sách mặc định nếu parse JSON thất bại
                $durations = [
                    '1 năm',
                    '2 năm',
                    '3 năm',
                    '4 năm',
                    '5 năm'
                ];
            }
        }

        // Chuyển mảng thời gian thuê thành chuỗi phân tách bằng dấu phẩy
        $durationsString = implode(',', $durations);

        return [
            'room_id' => 'required|exists:rooms,id', // Phòng phải tồn tại và là bắt buộc
            'start_date' => 'required|date_format:d/m/Y', // Ngày bắt đầu phải đúng định dạng DD/MM/YYYY
            'duration' => 'required|string|in:' . $durationsString, // Thời gian thuê phải thuộc danh sách hợp lệ
            'note' => 'nullable|string|max:500' // Ghi chú là tùy chọn, tối đa 500 ký tự
        ];
    }

    /**
     * Tùy chỉnh thông báo lỗi cho các quy tắc xác thực.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'room_id.required' => 'Vui lòng chọn phòng.',
            'room_id.exists' => 'Phòng không tồn tại',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu',
            'start_date.date_format' => 'Ngày bắt đầu phải có định dạng DD/MM/YYYY.',
            'duration.required' => 'Vui lòng chọn thời gian thuê',
            'duration.in' => 'Thời gian thuê phải là một trong các giá trị hợp lệ.',
            'note.max' => 'Ghi chú không được vượt quá 500 ký tự'
        ];
    }
}
