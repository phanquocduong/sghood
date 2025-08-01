<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Thông báo hóa đơn quá hạn</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #e74c3c;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .alert-icon {
            font-size: 48px;
            color: #e74c3c;
        }
        .invoice-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .amount {
            font-size: 24px;
            font-weight: bold;
            color: #e74c3c;
        }
        .btn {
            display: inline-block;
            background-color: #e74c3c;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="alert-icon">🚨</div>
            <h1 style="color: #e74c3c;">HÓA ĐƠN QUÁ HẠN THANH TOÁN</h1>
        </div>

        <p>Kính chào <strong>{{ $user->name }}</strong>,</p>

        <p>Chúng tôi thông báo rằng hóa đơn của bạn đã <strong>quá hạn thanh toán {{ $overdueDays }} ngày</strong>.</p>

        <div class="invoice-details">
            <h3>📋 Thông tin hóa đơn:</h3>
            <ul>
                <li><strong>Mã hóa đơn:</strong> #{{ $invoice->id }}</li>
                <li><strong>Số tiền:</strong> <span class="amount">{{ number_format($invoice->total_amount) }}đ</span></li>
                <li><strong>Phòng:</strong> {{ $room->name ?? 'N/A' }}</li>
                <li><strong>Nhà trọ:</strong> {{ $motel->name ?? 'N/A' }}</li>
                <li><strong>Ngày tạo hoá đơn:</strong> {{ $invoice->created_at->format('d/m/Y') }}</li>
                <li><strong>Quá hạn:</strong> {{ $overdueDays }} ngày</li>
            </ul>
        </div>

        <div style="text-align: center;">
            <a href="http://127.0.0.1:3000/quan-ly/hoa-don" class="btn">
                💳 THANH TOÁN NGAY
            </a>
        </div>

        <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h4 style="color: #856404; margin: 0 0 10px 0;">⚠️ Lưu ý quan trọng:</h4>
            <p style="margin: 0; color: #856404;">
                Vui lòng thanh toán sớm nhất để tránh phát sinh thêm phí phạt và ảnh hưởng đến việc sử dụng dịch vụ.
            </p>
        </div>

        <p>Nếu bạn đã thanh toán, vui lòng bỏ qua email này hoặc liên hệ với chúng tôi để xác nhận.</p>

        <p>Trân trọng,<br>
        <strong>{{ config('app.name') }}</strong></p>

        <div class="footer">
            <p>Email này được gửi tự động từ hệ thống quản lý nhà trọ. Vui lòng không trả lời email này.</p>
        </div>
    </div>
</body>
</html>