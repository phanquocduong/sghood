<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Xem Nhà Trọ Đã Được Cập Nhật</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: #f6f9fc;
            color: #333333;
            line-height: 1.6;
        }
        .container {
            max-width: 640px; margin: 0 auto;
            background-color: #ffffff; border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: #ffffff; text-align: center; padding: 30px 20px;
        }
        .header h1 {
            margin: 0; font-size: 28px; font-weight: 600;
        }
        .header p {
            margin: 8px 0 0; font-size: 16px; opacity: 0.9;
        }
        .content { padding: 30px; }
        .content p {
            font-size: 16px; margin: 0 0 15px; color: #4b5563;
        }
        .content .highlight {
            font-weight: 600; color: #dc2626;
        }
        .details-table {
            width: 100%; border-collapse: collapse; margin: 20px 0;
            background-color: #f9fafb; border-radius: 8px;
        }
        .details-table td {
            padding: 12px 20px; font-size: 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        .details-table .label {
            font-weight: 600; color: #1f2937; width: 40%;
        }
        .btn {
            display: inline-block; padding: 12px 24px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #ffffff !important; text-decoration: none;
            border-radius: 6px; font-size: 16px; font-weight: 500;
        }
        .btn:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        }
        .footer {
            text-align: center; padding: 20px; font-size: 13px;
            color: #6b7280; background-color: #f1f5f9;
        }
        .footer a { color: #3b82f6; text-decoration: none; }
        @media only screen and (max-width: 600px) {
            .container { width: 100%; border-radius: 0; }
            .content { padding: 20px; }
            .header h1 { font-size: 24px; }
            .details-table td {
                display: block; width: 100%; box-sizing: border-box;
            }
            .details-table .label { width: 100%; margin-bottom: 5px; }
        }
    </style>
</head>
<body>
    <table class="container" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td class="header">
                <h1>Lịch Xem Nhà Trọ Đã Cập Nhật</h1>
                <p>Thông báo từ Hệ thống Quản lý Lịch</p>
            </td>
        </tr>
        <tr>
            <td class="content">
                <p>Kính gửi Quản trị viên,</p>
                <p>Chúng tôi vừa nhận được cập nhật cho một lịch xem nhà trọ từ hệ thống.</p>
                <table class="details-table">
                    <tr>
                        <td class="label">Mã lịch</td>
                        <td class="highlight">{{ $schedule->id ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Người dùng</td>
                        <td class="highlight">{{ $schedule->user->name ?? 'Không xác định' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Nhà trọ</td>
                        <td class="highlight">{{ $schedule->motel->name ?? 'Không xác định' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Thời gian lịch</td>
                        <td class="highlight">
                            {{ $schedule->scheduled_at ? $schedule->scheduled_at->format('d/m/Y H:i') : 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Lời nhắn</td>
                        <td class="highlight">{{ $schedule->message ?? 'Không có' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Trạng thái</td>
                        <td class="highlight">{{ $schedule->status ?? 'Chưa xác định' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Thời gian cập nhật</td>
                        <td class="highlight">
                            {{ $schedule->updated_at ? $schedule->updated_at->format('d/m/Y H:i:s') : 'N/A' }}
                        </td>
                    </tr>
                </table>
                <p style="text-align: center;">
                    <a href="{{ url('/schedules') }}" class="btn">Xem Chi Tiết</a>
                </p>
            </td>
        </tr>
        <tr>
            <td class="footer">
                <p>© {{ date('Y') }} Hệ thống Quản lý Lịch. <a href="{{ url('/') }}">Truy cập hệ thống</a>.</p>
            </td>
        </tr>
    </table>
</body>
</html>
