<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Phòng Đã Bị Hủy</title>
</head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f4f4;">
    <div style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px;">
        <div style="text-align: center; padding-bottom: 20px;">
            <h1 style="color: #333333; font-size: 24px; margin: 0;">Đặt Phòng Đã Bị Hủy</h1>
        </div>

        <p style="color: #555555; font-size: 16px; line-height: 1.5; margin-bottom: 20px;">
            Kính gửi Quản trị viên,
        </p>

        <p style="color: #555555; font-size: 16px; line-height: 1.5; margin-bottom: 20px;">
            Một đặt phòng vừa được hủy với các thông tin chi tiết như sau:
        </p>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr style="background-color: #f8f8f8;">
                <td style="padding: 12px; font-size: 14px; font-weight: bold; color: #333333; border: 1px solid #e0e0e0;">ID Đặt phòng</td>
                <td style="padding: 12px; font-size: 14px; color: #333333; border: 1px solid #e0e0e0;">{{ $booking->id ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 12px; font-size: 14px; font-weight: bold; color: #333333; border: 1px solid #e0e0e0;">Người dùng</td>
                <td style="padding: 12px; font-size: 14px; color: #333333; border: 1px solid #e0e0e0;">{{ $booking->user->name ?? 'Không xác định' }}</td>
            </tr>
            <tr style="background-color: #f8f8f8;">
                <td style="padding: 12px; font-size: 14px; font-weight: bold; color: #333333; border: 1px solid #e0e0e0;">Phòng</td>
                <td style="padding: 12px; font-size: 14px; color: #333333; border: 1px solid #e0e0e0;">{{ $booking->room->name ?? 'Không xác định' }}</td>
            </tr>
            <tr>
                <td style="padding: 12px; font-size: 14px; font-weight: bold; color: #333333; border: 1px solid #e0e0e0;">Nhà trọ</td>
                <td style="padding: 12px; font-size: 14px; color: #333333; border: 1px solid #e0e0e0;">{{ $booking->room->motel->name ?? 'Không xác định' }}</td>
            </tr>
            <tr style="background-color: #f8f8f8;">
                <td style="padding: 12px; font-size: 14px; font-weight: bold; color: #333333; border: 1px solid #e0e0e0;">Ngày bắt đầu</td>
                <td style="padding: 12px; font-size: 14px; color: #333333; border: 1px solid #e0e0e0;">{{ $booking->start_date ? $booking->start_date->format('d/m/Y') : 'N/A' }}</td>
            </tr>
            <tr>
                <td style="padding: 12px; font-size: 14px; font-weight: bold; color: #333333; border: 1px solid #e0e0e0;">Ngày kết thúc</td>
                <td style="padding: 12px; font-size: 14px; color: #333333; border: 1px solid #e0e0e0;">{{ $booking->end_date ? $booking->end_date->format('d/m/Y') : 'N/A' }}</td>
            </tr>
            <tr style="background-color: #f8f8f8;">
                <td style="padding: 12px; font-size: 14px; font-weight: bold; color: #333333; border: 1px solid #e0e0e0;">Lý do hủy</td>
                <td style="padding: 12px; font-size: 14px; color: #333333; border: 1px solid #e0e0e0;">{{ $booking->cancellation_reason ?? 'Không có lý do' }}</td>
            </tr>
        </table>

        <p style="color: #555555; font-size: 16px; line-height: 1.5; margin-bottom: 20px;">
            Vui lòng truy cập hệ thống quản trị để xem thêm chi tiết hoặc thực hiện các hành động cần thiết.
        </p>

        <div style="text-align: center; margin-top: 20px;">
            <a href="{{ config('app.url') . '/bookings' }}" style="display: inline-block; padding: 12px 24px; background-color: #dc3545; color: #ffffff; text-decoration: none; border-radius: 4px; font-size: 16px; font-weight: bold;">
                Xem Chi Tiết
            </a>
        </div>

        <p style="color: #555555; font-size: 14px; line-height: 1.5; margin-top: 20px; text-align: center;">
            Cảm ơn bạn đã sử dụng hệ thống của chúng tôi.<br>
            Trân trọng,<br>
            <strong>{{ config('app.name') }}</strong>
        </p>
    </div>

    <div style="text-align: center; padding-top: 20px; color: #999999; font-size: 12px;">
        Email này được gửi tự động, vui lòng không trả lời trực tiếp.
    </div>
</body>
</html>
