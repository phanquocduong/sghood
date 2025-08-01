<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tự động xác nhận kiểm kê phòng</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; background-color: #f8f9fa; color: #333; }
        .email-container { max-width: 600px; margin: 20px auto; background: white; border: black 1px solid; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #ffc107, #ff8c00); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 24px; margin-bottom: 10px; font-weight: 600; }
        .header p { font-size: 16px; opacity: 0.9; }
        .content { padding: 40px 30px; }
        .greeting { font-size: 18px; margin-bottom: 20px; color: #2c3e50; }
        .auto-confirm-notice { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 10px; padding: 25px; margin: 25px 0; border-left: 4px solid #ffc107; }
        .auto-confirm-notice h3 { color: #856404; margin-bottom: 15px; font-size: 18px; }
        .auto-confirm-notice .reason { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 3px solid #ffc107; }
        .checkout-info { background: #f8f9fa; border-radius: 10px; padding: 25px; margin: 25px 0; border-left: 4px solid #007bff; }
        .checkout-info h3 { color: #007bff; margin-bottom: 15px; font-size: 18px; }
        .info-row { display: flex; margin-bottom: 12px; align-items: center; }
        .info-label { font-weight: 600; color: #495057; min-width: 150px; margin-right: 15px; }
        .info-value { color: #212529; flex: 1; }
        .refund-details { background: #d4edda; border: 1px solid #c3e6cb; border-radius: 10px; padding: 25px; margin: 25px 0; }
        .refund-details h3 { color: #155724; margin-bottom: 15px; font-size: 18px; }
        .amount-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; padding: 10px 0; border-bottom: 1px solid #c3e6cb; }
        .amount-row:last-child { border-bottom: 2px solid #155724; font-weight: bold; font-size: 1.1em; }
        .amount-label { color: #155724; }
        .amount-value { color: #155724; font-weight: 600; }
        .next-steps { background: #e8f4fd; border: 1px solid #b3d9ff; border-radius: 10px; padding: 25px; margin: 25px 0; }
        .next-steps h3 { color: #0066cc; margin-bottom: 15px; font-size: 18px; }
        .next-steps ul { padding-left: 20px; }
        .next-steps li { margin-bottom: 8px; color: #0066cc; }
        .message { color: #6c757d; line-height: 1.8; margin: 20px 0; }
        .cta-section { text-align: center; margin: 30px 0; }
        .cta-button { display: inline-block; background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 12px 30px; text-decoration: none; border-radius: 25px; font-weight: 600; transition: all 0.3s ease; }
        .cta-button:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3); }
        .footer { background: #343a40; color: white; padding: 25px; text-align: center; }
        .footer p { margin-bottom: 10px; opacity: 0.8; }
        .contact-info { font-size: 14px; opacity: 0.7; }
        @media (max-width: 600px) {
            .email-container { margin: 10px; border-radius: 10px; }
            .content { padding: 30px 20px; }
            .header { padding: 25px 20px; }
            .info-row { flex-direction: column; align-items: flex-start; }
            .info-label { min-width: auto; margin-bottom: 5px; }
            .amount-row { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="header">
        <h1>⏰ Tự động xác nhận kiểm kê</h1>
        <p>Kiểm kê phòng của bạn đã được tự động xác nhận</p>
    </div>

    <div class="content">
        <div class="greeting">
            Xin chào <strong>{{ $user->full_name ?? $user->name }}</strong>,
        </div>

        <div class="auto-confirm-notice">
            <h3>🔔 Thông báo tự động xác nhận</h3>
            <p>Kiểm kê phòng <strong>{{ $room->name }}</strong> đã được <strong>tự động xác nhận</strong> do bạn không phản hồi trong vòng 7 ngày kể từ khi admin hoàn thành kiểm kê.</p>

            <div class="reason">
                <strong>Lý do:</strong> Quá hạn 7 ngày không phản hồi kết quả kiểm kê
            </div>
        </div>

        <div class="checkout-info">
            <h3>📋 Thông tin kiểm kê</h3>
            <div class="info-row">
                <div class="info-label">Phòng:</div>
                <div class="info-value"><strong>{{ $room->name }}</strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Nhà trọ:</div>
                <div class="info-value">{{ $motel->name ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Ngày kiểm kê:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($checkout->check_out_date)->format('d/m/Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Trạng thái:</div>
                <div class="info-value">
                    <span style="color: #28a745; background: #d4edda; padding: 4px 12px; border-radius: 12px; font-weight: bold;">
                        Đã xác nhận tự động
                    </span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Ngày tự động xác nhận:</div>
                <div class="info-value">{{ now()->format('d/m/Y H:i:s') }}</div>
            </div>
        </div>

        <div class="refund-details">
            <h3>💰 Chi tiết hoàn tiền</h3>
            <div class="amount-row">
                <div class="amount-label">Tiền cọc ban đầu:</div>
                <div class="amount-value">{{ number_format($depositAmount, 0, ',', '.') }} VND</div>
            </div>
            @if($deductionAmount > 0)
            <div class="amount-row">
                <div class="amount-label">Số tiền khấu trừ:</div>
                <div class="amount-value" style="color: #dc3545;">-{{ number_format($deductionAmount, 0, ',', '.') }} VND</div>
            </div>
            @endif
            <div class="amount-row">
                <div class="amount-label">Số tiền hoàn trả:</div>
                <div class="amount-value" style="color: #28a745; font-size: 1.2em;">{{ number_format($finalRefundAmount, 0, ',', '.') }} VND</div>
            </div>
        </div>

        <div class="next-steps">
            <h3>📝 Các bước tiếp theo</h3>
            <ul>
                <li>Kết quả kiểm kê đã được tự động xác nhận</li>
                <li>Quy trình hoàn tiền sẽ được tiến hành</li>
                <li>Bạn sẽ nhận được thông báo khi hoàn tiền thành công</li>
                <li>Nếu có thắc mắc, vui lòng liên hệ với chúng tôi</li>
            </ul>
        </div>

        <div class="message">
            <strong>Lưu ý:</strong> Việc tự động xác nhận này được thực hiện theo quy định của hệ thống.
            Nếu bạn có bất kỳ thắc mắc nào về kết quả kiểm kê, vui lòng liên hệ với chúng tôi ngay.
        </div>

        <div class="cta-section">
            <a href="{{ url('https://sghood.com.vn/quan-ly/kiem-ke') }}" class="cta-button">
                Xem chi tiết kiểm kê
            </a>
        </div>
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
