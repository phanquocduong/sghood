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
        .invoice-info { background: #f8f9fa; border-radius: 10px; padding: 25px; margin: 25px 0; border-left: 4px solid #28a745; }
        .invoice-info h3 { color: #28a745; margin-bottom: 15px; font-size: 18px; }
        .info-row { display: flex; margin-bottom: 12px; align-items: center; }
        .info-label { font-weight: 600; color: #495057; min-width: 120px; margin-right: 15px; }
        .info-value { color: #212529; flex: 1; }
        .success-message { background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .success-message h4 { color: #155724; margin-bottom: 10px; font-size: 16px; }
        .success-message p { color: #155724; line-height: 1.5; }
        .fee-details { background: #f8f9fa; border-radius: 10px; padding: 20px; margin: 25px 0; border-left: 4px solid #28a745; }
        .fee-details h3 { color: #28a745; margin-bottom: 15px; font-size: 18px; }
        .fee-row { display: flex; margin-bottom: 10px; align-items: center; }
        .fee-label { font-weight: 600; color: #495057; min-width: 120px; margin-right: 15px; }
        .fee-value { color: #212529; flex: 1; }
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
            .info-row, .fee-row { flex-direction: column; align-items: flex-start; }
            .info-label, .fee-label { min-width: auto; margin-bottom: 5px; }
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
        <div class="invoice-info">
            <h3>📋 Thông tin hóa đơn</h3>
            <div class="info-row">
                <span class="info-label">🏠 Tên phòng trọ:</span>
                <span class="info-value">{{ $room->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">👤 Tên người thuê:</span>
                <span class="info-value">{{ $contract->user->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">📞 Số điện thoại:</span>
                <span class="info-value">{{ $contract->user->phone ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">⚡ Chỉ số điện:</span>
                <span class="info-value">{{ $meterReading->electricity_kwh ?? 0 }} kWh</span>
            </div>
            <div class="info-row">
                <span class="info-label">💧 Chỉ số nước:</span>
                <span class="info-value">{{ $meterReading->water_m3 ?? 0 }} m³</span>
            </div>
            <div class="info-row">
                <span class="info-label">📅 Tháng/Năm:</span>
                <span class="info-value">{{ $invoice->month }}/{{ $invoice->year }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">🕐 Ngày tạo hóa đơn:</span>
                <span class="info-value">{{ $invoice->created_at->format('d/m/Y H:i') }}</span>
            </div>
        </div>
        <div class="fee-details">
            <h3>💸 Chi tiết các phí</h3>
            <div class="fee-row">
                <span class="fee-label">Phí phòng:</span>
                <span class="fee-value">{{ number_format($room->price ?? 0, 0) }} VND</span>
            </div>
            <div class="fee-row">
                <span class="fee-label">Phí điện:</span>
                <span class="fee-value">{{ number_format($invoice->electricity_fee, 0) }} VND</span>
            </div>
            <div class="fee-row">
                <span class="fee-label">Phí nước:</span>
                <span class="fee-value">{{ number_format($invoice->water_fee, 0) }} VND</span>
            </div>
            <div class="fee-row">
                <span class="fee-label">Phí giữ xe:</span>
                <span class="fee-value">{{ number_format($invoice->parking_fee, 0) }} VND</span>
            </div>
            <div class="fee-row">
                <span class="fee-label">Phí rác:</span>
                <span class="fee-value">{{ number_format($invoice->junk_fee, 0) }} VND</span>
            </div>
            <div class="fee-row">
                <span class="fee-label">Phí internet:</span>
                <span class="fee-value">{{ number_format($invoice->internet_fee, 0) }} VND</span>
            </div>
            <div class="fee-row">
                <span class="fee-label">Phí dịch vụ:</span>
                <span class="fee-value">{{ number_format($invoice->service_fee, 0) }} VND</span>
            </div>
            <div class="fee-row">
                <span class="fee-label">Tổng cộng:</span>
                <span class="fee-value">{{ number_format($invoice->total_amount, 0) }} VND</span>
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
