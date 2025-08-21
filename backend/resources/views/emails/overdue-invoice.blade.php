<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo hóa đơn quá hạn</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; background-color: #f8f9fa; color: #333; }
        .email-container { max-width: 600px; margin: 20px auto; background: white; border: black 1px solid; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 24px; margin-bottom: 10px; font-weight: 600; }
        .header p { font-size: 16px; opacity: 0.9; }
        .content { padding: 40px 30px; }
        .greeting { font-size: 18px; margin-bottom: 20px; color: #2c3e50; }

        .invoice-header { background: #f8f9fa; border-radius: 10px; padding: 25px; margin: 25px 0; border-left: 4px solid #e74c3c; text-align: center; }
        .invoice-header h2 { color: #e74c3c; margin-bottom: 5px; font-size: 20px; font-weight: bold; }
        .invoice-header .overdue-info { color: #495057; font-size: 16px; margin-bottom: 15px; }

        .customer-info { display: flex; justify-content: space-between; margin: 20px 0; flex-wrap: wrap; }
        .customer-left, .customer-right { flex: 1; min-width: 250px; }
        .customer-left { margin-right: 20px; }
        .info-item { margin-bottom: 8px; }
        .info-label { font-weight: 600; color: #495057; display: inline-block; min-width: 80px; }
        .info-value { color: #212529; }
        .room-number { background: #ffebee; padding: 5px 15px; border-radius: 5px; display: inline-block; font-weight: bold; color: #c62828; }

        .invoice-table { width: 100%; border-collapse: collapse; margin: 25px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        .invoice-table th { background: #e74c3c; color: white; padding: 15px 12px; text-align: center; font-weight: 600; font-size: 14px; }
        .invoice-table td { padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6; }
        .invoice-table tr:nth-child(even) { background-color: #f8f9fa; }
        .invoice-table tr:hover { background-color: #ffebee; }
        .invoice-table .item-name { text-align: left; font-weight: 500; }
        .invoice-table .details { text-align: left; font-size: 13px; color: #666; }
        .invoice-table .amount { text-align: right; font-weight: 600; }
        .total-row { background-color: #e74c3c !important; color: white; font-weight: bold; }
        .total-row td { border-bottom: none; font-size: 16px; }

        .payment-info { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .payment-info h4 { color: #856404; margin-bottom: 10px; font-size: 16px; }
        .payment-details { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .payment-label { font-weight: 600; color: #856404; }
        .payment-value { color: #856404; font-weight: bold; }

        .overdue-message { background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .overdue-message h4 { color: #721c24; margin-bottom: 10px; font-size: 16px; }
        .overdue-message p { color: #721c24; line-height: 1.5; }

        .message { color: #6c757d; line-height: 1.8; margin: 20px 0; }
        .cta-section { text-align: center; margin: 30px 0; }
        .cta-button { display: inline-block; background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; padding: 12px 30px; text-decoration: none; border-radius: 25px; font-weight: 600; transition: all 0.3s ease; }
        .cta-button:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3); }
        .footer { background: #343a40; color: white; padding: 25px; text-align: center; }
        .footer p { margin-bottom: 10px; opacity: 0.8; }
        .contact-info { font-size: 14px; opacity: 0.7; }
        .icon { display: inline-block; width: 20px; height: 20px; margin-right: 8px; vertical-align: middle; }

        @media (max-width: 600px) {
            .email-container { margin: 10px; border-radius: 10px; }
            .content { padding: 30px 20px; }
            .header { padding: 25px 20px; }
            .customer-info { flex-direction: column; }
            .customer-left { margin-right: 0; margin-bottom: 15px; }
            .invoice-table { font-size: 12px; }
            .invoice-table th, .invoice-table td { padding: 8px 6px; }
            .payment-details { flex-direction: column; }
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="header">
        <h1>🚨 HÓA ĐƠN QUÁ HẠN THANH TOÁN</h1>
        <p>Thông báo hóa đơn đã quá hạn {{ $overdueDays }} ngày</p>
    </div>
    <div class="content">
        <div class="greeting">
            Kính chào <strong>{{ $user->name }}</strong>,
        </div>

        <div class="overdue-message">
            <h4>⚠️ HÓA ĐƠN QUÁ HẠN</h4>
            <p>Hóa đơn của bạn (Mã hóa đơn: <strong style="color: #e74c3c;">#{{ $invoice->id }}</strong>) đã <strong>quá hạn {{ $overdueDays }} ngày</strong>. Vui lòng thanh toán sớm nhất để tránh phát sinh thêm phí phạt.</p>
        </div>

        <div class="invoice-header">
            <h2>THÔNG TIN HÓA ĐƠN QUÁ HẠN</h2>
            <div class="overdue-info">Ngày tạo: {{ $invoice->created_at->format('d/m/Y') }} - Quá hạn: {{ $overdueDays }} ngày</div>
        </div>

        <div class="customer-info">
            <div class="customer-left">
                <div class="info-item">
                    <span class="info-label">Khách hàng:</span>
                    <span class="info-value">{{ $user->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Nhà trọ:</span>
                    <span class="info-value">{{ $motel->name ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="customer-right">
                <div class="info-item">
                    <span class="info-label">Phòng số:</span>
                    <span class="room-number">{{ $room->name ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th style="width: 60px;">STT</th>
                    <th style="width: 120px;">Khoản</th>
                    <th>Chi tiết</th>
                    <th style="width: 120px;">Thành Tiền</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="3" class="item-name"><strong>Tổng số tiền hóa đơn</strong></td>
                    <td class="amount"><strong style="color: #e74c3c; font-size: 18px;">{{ number_format($invoice->total_amount) }}đ</strong></td>
                </tr>
            </tbody>
        </table>

        <div class="payment-info">
            <h4>📋 Thông tin thanh toán:</h4>
            <div class="payment-details">
                <span class="payment-label">- Mã hóa đơn:</span>
                <span class="payment-value">#{{ $invoice->id }}</span>
            </div>
            <div class="payment-details">
                <span class="payment-label">- Ngày tạo:</span>
                <span class="payment-value">{{ $invoice->created_at->format('d/m/Y') }}</span>
            </div>
            <div class="payment-details">
                <span class="payment-label">- Số ngày quá hạn:</span>
                <span class="payment-value" style="color: #e74c3c;"><strong>{{ $overdueDays }} ngày</strong></span>
            </div>
            <div class="payment-details" style="border-top: 1px solid #ffeaa7; padding-top: 8px; margin-top: 8px;">
                <span class="payment-label"><strong>Số tiền cần thanh toán:</strong></span>
                <span class="payment-value"><strong style="color: #e74c3c;">{{ number_format($invoice->total_amount) }}đ</strong></span>
            </div>
        </div>

        <p class="message">
            <strong>Vui lòng thanh toán sớm nhất để tránh phát sinh thêm phí phạt và ảnh hưởng đến việc sử dụng dịch vụ.</strong> Bạn có thể thanh toán trực tuyến bằng cách nhấn vào nút bên dưới:
        </p>

        <div class="cta-section">
            <a href="https://sghood.com.vn/quan-ly/hoa-don" class="cta-button" style="color: #ffffff;">
                <span class="icon">💳</span> THANH TOÁN NGAY
            </a>
        </div>

        <p class="message">
            Nếu bạn đã thanh toán, vui lòng bỏ qua email này hoặc liên hệ với chúng tôi để xác nhận. Cảm ơn bạn đã hợp tác!
        </p>

        <p class="message">
            Trân trọng,<br>
            <strong>{{ config('app.name') }}</strong>
        </p>
    </div>

    <div class="footer">
        <p><strong>📧 Đội ngũ hỗ trợ khách hàng</strong></p>
        <div class="contact-info">
            <p style="color: #ffffff;">📞 Hotline: 082 828 3169 | ✉️ Email: sghood@gmail.com</p>
            <p style="color: #ffffff;">🌐 Website: sghood.com.vn</p>
            <p style="color: #ffffff;">Email này được gửi tự động từ hệ thống quản lý nhà trọ. Vui lòng không trả lời email này.</p>
        </div>
    </div>
</div>
</body>
</html>
