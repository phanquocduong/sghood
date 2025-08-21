<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn đã được tạo thành công</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; background-color: #f8f9fa; color: #333; }
        .email-container { max-width: 600px; margin: 20px auto; background: white; border: black 1px solid; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #28a745, #218838); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 24px; margin-bottom: 10px; font-weight: 600; }
        .header p { font-size: 16px; opacity: 0.9; }
        .content { padding: 40px 30px; }
        .greeting { font-size: 18px; margin-bottom: 20px; color: #2c3e50; }

        .invoice-header { background: #f8f9fa; border-radius: 10px; padding: 25px; margin: 25px 0; border-left: 4px solid #28a745; text-align: center; }
        .invoice-header h2 { color: #28a745; margin-bottom: 5px; font-size: 20px; font-weight: bold; }
        .invoice-header .month-year { color: #495057; font-size: 16px; margin-bottom: 15px; }

        .customer-info { display: flex; justify-content: space-between; margin: 20px 0; flex-wrap: wrap; }
        .customer-left, .customer-right { flex: 1; min-width: 250px; }
        .customer-left { margin-right: 20px; }
        .info-item { margin-bottom: 8px; }
        .info-label { font-weight: 600; color: #495057; display: inline-block; min-width: 80px; }
        .info-value { color: #212529; }
        .room-number { background: #d4edda; padding: 5px 15px; border-radius: 5px; display: inline-block; font-weight: bold; color: #155724; }

        .invoice-table { width: 100%; border-collapse: collapse; margin: 25px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        .invoice-table th { background: #28a745; color: white; padding: 15px 12px; text-align: center; font-weight: 600; font-size: 14px; }
        .invoice-table td { padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6; }
        .invoice-table tr:nth-child(even) { background-color: #f8f9fa; }
        .invoice-table tr:hover { background-color: #e8f5e8; }
        .invoice-table .item-name { text-align: left; font-weight: 500; }
        .invoice-table .details { text-align: left; font-size: 13px; color: #666; }
        .invoice-table .amount { text-align: right; font-weight: 600; }
        .total-row { background-color: #28a745 !important; color: white; font-weight: bold; }
        .total-row td { border-bottom: none; font-size: 16px; }

        .payment-info { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .payment-info h4 { color: #856404; margin-bottom: 10px; font-size: 16px; }
        .payment-details { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .payment-label { font-weight: 600; color: #856404; }
        .payment-value { color: #856404; font-weight: bold; }

        .success-message { background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .success-message h4 { color: #155724; margin-bottom: 10px; font-size: 16px; }
        .success-message p { color: #155724; line-height: 1.5; }

        .message { color: #6c757d; line-height: 1.8; margin: 20px 0; }
        .cta-section { text-align: center; margin: 30px 0; }
        .cta-button { display: inline-block; background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 12px 30px; text-decoration: none; border-radius: 25px; font-weight: 600; transition: all 0.3s ease; }
        .cta-button:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3); }
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
        <h1>Hóa đơn tiền phòng</h1>
        <p>Xin chào! Đây là hóa đơn của bạn tháng {{ $invoice->month }}/{{ $invoice->year }}.</p>
    </div>
    <div class="content">
        <div class="greeting">
            Xin chào <strong>{{ $contract->user->name ?? 'Khách hàng' }}</strong>,
        </div>

        <div class="success-message">
            <h4>✅ Hóa đơn đã được xác nhận</h4>
            <p>Hóa đơn của bạn (Mã hóa đơn: <strong style="color: #28a745;">#{{ $invoice->code }}</strong>) đã được tạo thành công. Vui lòng kiểm tra chi tiết và thanh toán trước thời hạn.</p>
        </div>

        <div class="invoice-header">
            <h2>THÔNG BÁO TIỀN PHÒNG TRỌ</h2>
            <div class="month-year">Tháng {{ $invoice->month }}/{{ $invoice->year }}</div>
        </div>

        <div class="customer-info">
            <div class="customer-left">
                <div class="info-item">
                    <span class="info-label">Kính gửi:</span>
                    <span class="info-value">{{ $contract->user->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Điện thoại:</span>
                    <span class="info-value">{{ $contract->user->phone ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="customer-right">
                <div class="info-item">
                    <span class="info-label">Ở phòng số:</span>
                    <span class="room-number">{{ $room->name ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th style="width: 60px;">STT</th>
                    <th style="width: 100px;">Khoản</th>
                    <th>Chi tiết</th>
                    <th style="width: 120px;">Thành Tiền</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td class="item-name">Phòng</td>
                    <td class="details"></td>
                    <td class="amount">{{ number_format($room->price ?? 0, 0) }}đ</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td class="item-name">Điện</td>
                    <td class="details">{{ $meterReading->electricity_kwh ?? 0 }} kWh x {{ number_format($invoice->electricity_price ?? 3000, 0) }}đ/kWh</td>
                    <td class="amount">{{ number_format($invoice->electricity_fee, 0) }}đ</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td class="item-name">Nước</td>
                    <td class="details">{{ $meterReading->water_m3 ?? 0 }}m3 x {{ number_format($invoice->water_price ?? 7000, 0) }}đ/m3</td>
                    <td class="amount">{{ number_format($invoice->water_fee, 0) }}đ</td>
                </tr>
                <tr>
                    <td>4</td>
                    <td class="item-name">Wifi</td>
                    <td class="details"></td>
                    <td class="amount">{{ number_format($invoice->internet_fee, 0) }}đ</td>
                </tr>
                <tr>
                    <td>5</td>
                    <td class="item-name">Rác</td>
                    <td class="details"></td>
                    <td class="amount">{{ number_format($invoice->junk_fee, 0) }}đ</td>
                </tr>
                @if($invoice->parking_fee > 0)
                <tr>
                    <td>6</td>
                    <td class="item-name">Giữ xe</td>
                    <td class="details">{{ $contract->contractTenants->where('status', 'Đang ở')->count() + 1 }} người x {{ number_format($contract->room->motel->parking_fee, 0) }}</td>
                    <td class="amount">{{ number_format($invoice->parking_fee, 0) }}đ</td>
                </tr>
                @endif
                @if($invoice->service_fee > 0)
                <tr>
                    <td>7</td>
                    <td class="item-name">Dịch vụ</td>
                    <td class="details"></td>
                    <td class="amount">{{ number_format($invoice->service_fee, 0) }}đ</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td></td>
                    <td></td>
                    <td><strong>Cộng:</strong></td>
                    <td><strong>{{ number_format($invoice->total_amount, 0) }}đ</strong></td>
                </tr>
            </tbody>
        </table>

        <div class="payment-info">
            <h4>📋 Phần Thanh toán:</h4>
            <div class="payment-details">
                <span class="payment-label">- Số tiền phải trả:</span>
                <span class="payment-value">{{ number_format($invoice->total_amount, 0) }}đ</span>
            </div>
            <div class="payment-details" style="border-top: 1px solid #ffeaa7; padding-top: 8px; margin-top: 8px;">
                <span class="payment-label"><strong>Tổng Cộng:</strong></span>
                <span class="payment-value"><strong>{{ number_format(($invoice->total_amount + ($invoice->previous_debt ?? 0)), 0) }}đ</strong></span>
            </div>
        </div>

        <p class="message">
            Vui lòng thanh toán hóa đơn trước thời hạn để tránh các rủi ro khác. Bạn có thể xem chi tiết hóa đơn trong hệ thống bằng cách nhấn vào nút bên dưới:
        </p>

        <div class="cta-section">
            <a href="https://sghood.com.vn/quan-ly/hoa-don" class="cta-button" style="color: #ffffff;">
                <span class="icon">📄</span> Xem hóa đơn của tôi
            </a>
        </div>

        <p class="message">
            Nếu bạn cần hỗ trợ, vui lòng liên hệ với chúng tôi. Xin cảm ơn!
        </p>
    </div>

    <div class="footer">
        <p><strong>📧 Đội ngũ hỗ trợ khách hàng</strong></p>
        <div class="contact-info">
            <p style="color: #ffffff;">📞 Hotline: 082 828 3169 | ✉️ Email: sghood@gmail.com</p>
            <p style="color: #ffffff;">🌐 Website: sghood.com.vn</p>
        </div>
    </div>
</div>
</body>
</html>
