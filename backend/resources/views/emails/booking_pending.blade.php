<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Đặt phòng mới chờ duyệt</title>
    <style type="text/css">
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: #f6f9fc;
            color: #333333;
            line-height: 1.6;
        }
        .container {
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: #ffffff;
            text-align: center;
            padding: 30px 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .header p {
            margin: 8px 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .content p {
            font-size: 16px;
            margin: 0 0 15px;
            color: #4b5563;
        }
        .content .highlight {
            font-weight: 600;
            color: #dc2626;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #f9fafb;
            border-radius: 8px;
            overflow: hidden;
        }
        .details-table td {
            padding: 12px 20px;
            font-size: 15px;
            border-bottom: 1px solid #e5e7eb;
        }
        .details-table .label {
            font-weight: 600;
            color: #1f2937;
            width: 40%;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            transition: background 0.3s ease;
        }
        .btn:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
        }
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 13px;
            color: #6b7280;
            background-color: #f1f5f9;
        }
        .footer a {
            color: #3b82f6;
            text-decoration: none;
        }
        @media only screen and (max-width: 600px) {
            .container {
                width: 100% !important;
                border-radius: 0;
            }
            .content {
                padding: 20px;
            }
            .header h1 {
                font-size: 24px;
            }
            .details-table td {
                display: block;
                width: 100%;
                box-sizing: border-box;
            }
            .details-table .label {
                width: 100%;
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <table class="container" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td class="header">
                <h1>Đặt Phòng Mới</h1>
                <p>Thông báo từ Hệ thống Quản lý Lịch</p>
            </td>
        </tr>
        <tr>
            <td class="content">
                <p>Kính gửi Quản trị viên,</p>
                <p>Chúng tôi vừa nhận được một đặt phòng mới từ hệ thống. Vui lòng kiểm tra và xử lý trong thời gian sớm nhất.</p>
                <table class="details-table">
                    <tr>
                        <td class="label">Mã đặt phòng</td>
                        <td class="highlight">{{ $booking->id }}</td>
                    </tr>
                    <tr>
                        <td class="label">Người dùng</td>
                        <td class="highlight">{{ $booking->user->name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Phòng</td>
                        <td class="highlight">{{ $booking->room->name }} - {{ $booking->room->motel->name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Ngày bắt đầu</td>
                        <td class="highlight">{{ $booking->start_date->addHours(7)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Ngày kết thúc</td>
                        <td class="highlight">{{ $booking->end_date->addHours(7)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Ghi chú</td>
                        <td class="highlight">{{ $booking->note ?? 'Không có' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Trạng thái</td>
                        <td class="highlight">{{ $booking->status }}</td>
                    </tr>
                    <tr>
                        <td class="label">Thời gian gửi</td>
                        <td class="highlight">{{ $booking->created_at->addHours(7)->format('d/m/Y H:i:s') }}</td>
                    </tr>
                </table>
                <p style="text-align: center;">
                    <a href="{{ url('/bookings') }}" class="btn">Xem Chi Tiết</a>
                </p>
                <p>Cảm ơn bạn đã hỗ trợ hệ thống vận hành hiệu quả!</p>
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
