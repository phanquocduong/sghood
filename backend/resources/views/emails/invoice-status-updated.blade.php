<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật trạng thái hóa đơn</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; background-color: #f8f9fa; color: #333; }
        .email-container { max-width: 600px; margin: 20px auto; background: white; border: black 1px solid; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 24px; margin-bottom: 10px; font-weight: 600; }
        .header p { font-size: 16px; opacity: 0.9; }
        .content { padding: 40px 30px; }
        .greeting { font-size: 18px; margin-bottom: 20px; color: #2c3e50; }
        .status-update { background: #e8f5e8; border: 1px solid #28a745; border-radius: 10px; padding: 25px; margin: 25px 0; border-left: 4px solid #28a745; }
        .status-update h3 { color: #28a745; margin-bottom: 15px; font-size: 18px; }
        .status-change { display: flex; align-items: center; justify-content: center; margin: 20px 0; }
        .status-old, .status-new { padding: 10px 20px; border-radius: 20px; font-weight: bold; }
        .status-old { background: #f8d7da; color: #721c24; }
        .status-new { background: #d4edda; color: #155724; }
        .status-arrow { margin: 0 15px; font-size: 24px; color: #28a745; }
        .invoice-info { background: #f8f9fa; border-radius: 10px; padding: 25px; margin: 25px 0; border-left: 4px solid #007bff; }
        .invoice-info h3 { color: #007bff; margin-bottom: 15px; font-size: 18px; }
        .info-row { display: flex; margin-bottom: 12px; align-items: center; }
        .info-label { font-weight: 600; color: #495057; min-width: 120px; margin-right: 15px; }
        .info-value { color: #212529; flex: 1; }
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
            .info-row { flex-direction: column; align-items: flex-start; }
            .info-label { min-width: auto; margin-bottom: 5px; }
            .status-change { flex-direction: column; }
            .status-arrow { transform: rotate(90deg); margin: 10px 0; }
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="header">
        <h1>🔄 Cập nhật trạng thái hóa đơn</h1>
        <p>Trạng thái hóa đơn của bạn đã được cập nhật</p>
    </div>

    <div class="content">
        <div class="greeting">
            Xin chào <strong>{{ $user->full_name ?? $user->name }}</strong>,
        </div>

        <div class="status-update">
            <h3>✅ Trạng thái hóa đơn đã được cập nhật</h3>
            <p>Hóa đơn <strong>{{ $invoice->code }}</strong> đã được cập nhật trạng thái:</p>

            <div class="status-change">
                <div class="status-old">{{ $oldStatus ?: 'Chưa xác định' }}</div>
                <div class="status-arrow">→</div>
                <div class="status-new">{{ $newStatus }}</div>
            </div>
        </div>

        <div class="invoice-info">
            <h3>📋 Thông tin hóa đơn</h3>
            <div class="info-row">
                <div class="info-label">Mã hóa đơn:</div>
                <div class="info-value"><strong>{{ $invoice->code }}</strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Phòng:</div>
                <div class="info-value">{{ $room->name ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Nhà trọ:</div>
                <div class="info-value">{{ $motel->name ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Tháng/Năm:</div>
                <div class="info-value">{{ $invoice->month }}/{{ $invoice->year }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Trạng thái mới:</div>
                <div class="info-value">
                    <span style="color: {{ $newStatus === 'Đã trả' ? '#28a745' : '#ffc107' }}; font-weight: bold;">
                        {{ $newStatus }}
                    </span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Tổng tiền:</div>
                <div class="info-value"><strong style="color: #007bff; font-size: 1.1em;">{{ number_format($invoice->total_amount, 0, ',', '.') }} VND</strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Ngày cập nhật:</div>
                <div class="info-value">{{ now()->format('d/m/Y H:i:s') }}</div>
            </div>
        </div>

        @if($newStatus === 'Đã trả')
        <div class="success-message">
            <h4>🎉 Cảm ơn bạn đã thanh toán!</h4>
            <p>Hóa đơn của bạn đã được xác nhận thanh toán thành công. Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi.</p>
        </div>
        @endif

        <div class="message">
            Nếu bạn có bất kỳ thắc mắc nào về hóa đơn này, vui lòng liên hệ với chúng tôi qua thông tin bên dưới.
        </div>

        <div class="cta-section">
            <a href="{{ url('https://sghood.com.vn/quan-ly/hoa-don/') }}" class="cta-button">
                Xem chi tiết hóa đơn
            </a>
        </div>
    </div>

    <div class="footer">
        <p><strong>Troviet Platform</strong></p>
        <p>Hệ thống quản lý nhà trọ hiện đại</p>
        <div class="contact-info">
            <p>📧 Email: support@troviet.com</p>
            <p>📞 Hotline: 1900-xxxx</p>
            <p>&copy; {{ date('Y') }} Troviet Platform. All rights reserved.</p>
        </div>
    </div>
</div>
</body>
</html>
