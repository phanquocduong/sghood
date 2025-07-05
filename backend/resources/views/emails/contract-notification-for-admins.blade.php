<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 20px;
            line-height: 1.6;
        }
        .content h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .content p {
            color: #555;
            margin-bottom: 15px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .table th {
            background-color: #f9f9f9;
            color: #333;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 15px;
        }
        .footer {
            background-color: #f4f4f4;
            padding: 10px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
        @media only screen and (max-width: 600px) {
            .container {
                width: 100%;
                margin: 10px;
            }
            .content {
                padding: 15px;
            }
            .header {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Thông báo hợp đồng</h1>
        </div>
        <div class="content">
            <h2>{{ $title }}</h2>
            <p>{{ $body }}</p>

            <h3>Thông tin hợp đồng</h3>
            <table class="table">
                <tr>
                    <th>Mã hợp đồng</th>
                    <td>#{{ $contract->id }}</td>
                </tr>
                <tr>
                    <th>Người dùng</th>
                    <td>{{ $contract->user->name }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $contract->user->email }}</td>
                </tr>
                <tr>
                    <th>Phòng</th>
                    <td>{{ $contract->room->name }} ({{ $contract->room->motel->name }})</td>
                </tr>
                <tr>
                    <th>Địa chỉ</th>
                    <td>{{ $contract->room->motel->address }}</td>
                </tr>
                <tr>
                    <th>Giá thuê</th>
                    <td>{{ number_format($contract->rental_price, 0, ',', '.') }} VNĐ</td>
                </tr>
                <tr>
                    <th>Tiền cọc</th>
                    <td>{{ number_format($contract->deposit_amount, 0, ',', '.') }} VNĐ</td>
                </tr>
                <tr>
                    <th>Ngày bắt đầu</th>
                    <td>{{ $contract->start_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Ngày kết thúc</th>
                    <td>{{ $contract->end_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Trạng thái</th>
                    <td>{{ $contract->status }}</td>
                </tr>
                @if($contract->signed_at)
                <tr>
                    <th>Ngày ký</th>
                    <td>{{ $contract->signed_at->format('d/m/Y H:i') }}</td>
                </tr>
                @endif
            </table>

            <a href="{{ config('app.url') }}/contracts/{{ $contract->id }}" class="button">Xem chi tiết hợp đồng</a>
        </div>
        <div class="footer">
            <p>Đây là email tự động, vui lòng không trả lời trực tiếp. Nếu có thắc mắc, liên hệ qua hệ thống.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
