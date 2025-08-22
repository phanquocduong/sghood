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

        .auto-confirm-header { background: #f8f9fa; border-radius: 10px; padding: 25px; margin: 25px 0; border-left: 4px solid #ffc107; text-align: center; }
        .auto-confirm-header h2 { color: #ffc107; margin-bottom: 5px; font-size: 20px; font-weight: bold; }
        .auto-confirm-header .status-text { color: #495057; font-size: 16px; margin-bottom: 15px; }

        .auto-confirm-notice { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .auto-confirm-notice h4 { color: #856404; margin-bottom: 10px; font-size: 16px; }
        .auto-confirm-notice p { color: #856404; line-height: 1.5; }

        .customer-info { display: flex; justify-content: space-between; margin: 20px 0; flex-wrap: wrap; }
        .customer-left, .customer-right { flex: 1; min-width: 250px; }
        .customer-left { margin-right: 20px; }
        .info-item { margin-bottom: 8px; }
        .info-label { font-weight: 600; color: #495057; display: inline-block; min-width: 120px; }
        .info-value { color: #212529; }
        .room-number { background: #d4edda; padding: 5px 15px; border-radius: 5px; display: inline-block; font-weight: bold; color: #155724; }

        .checkout-table { width: 100%; border-collapse: collapse; margin: 25px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        .checkout-table th { background: #ffc107; color: white; padding: 15px 12px; text-align: center; font-weight: 600; font-size: 14px; }
        .checkout-table td { padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6; }
        .checkout-table tr:nth-child(even) { background-color: #f8f9fa; }
        .checkout-table tr:hover { background-color: #fff3cd; }
        .checkout-table .item-name { text-align: left; font-weight: 500; }
        .checkout-table .details { text-align: left; font-size: 13px; color: #666; }
        .checkout-table .amount { text-align: right; font-weight: 600; }
        .total-row { background-color: #28a745 !important; color: white; font-weight: bold; }
        .total-row td { border-bottom: none; font-size: 16px; }

        .refund-info { background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .refund-info h4 { color: #155724; margin-bottom: 10px; font-size: 16px; }
        .refund-details { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .refund-label { font-weight: 600; color: #155724; }
        .refund-value { color: #155724; font-weight: bold; }
        .refund-total { border-top: 1px solid #c3e6cb; padding-top: 8px; margin-top: 8px; }

        .next-steps { background: #e8f4fd; border: 1px solid #b3d9ff; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .next-steps h4 { color: #0066cc; margin-bottom: 10px; font-size: 16px; }
        .next-steps ul { padding-left: 20px; }
        .next-steps li { margin-bottom: 8px; color: #0066cc; }

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
            .checkout-table { font-size: 12px; }
            .checkout-table th, .checkout-table td { padding: 8px 6px; }
            .refund-details { flex-direction: column; }
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
            <h4>🔔 Thông báo tự động xác nhận</h4>
            <p>Kiểm kê phòng <strong>{{ $room->name }}</strong> đã được <strong>tự động xác nhận</strong> do bạn không phản hồi trong vòng 7 ngày kể từ khi admin hoàn thành kiểm kê. (Lý do: Quá hạn không phản hồi kết quả kiểm kê)</p>
        </div>

        <div class="auto-confirm-header">
            <h2>THÔNG TIN KIỂM KÊ PHÒNG</h2>
            <div class="status-text">Trạng thái: Đã xác nhận tự động</div>
        </div>

        <div class="customer-info">
            <div class="customer-left">
                <div class="info-item">
                    <span class="info-label">Phòng:</span>
                    <span class="room-number">{{ $room->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Nhà trọ:</span>
                    <span class="info-value">{{ $motel->name ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="customer-right">
                <div class="info-item">
                    <span class="info-label">Ngày kiểm kê:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($checkout->check_out_date)->format('d/m/Y') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Ngày tự động xác nhận:</span>
                    <span class="info-value">{{ now()->format('d/m/Y H:i:s') }}</span>
                </div>
            </div>
        </div>

        <table class="checkout-table">
            <thead>
                <tr>
                    <th style="width: 60px;">STT</th>
                    <th style="width: 150px;">Khoản</th>
                    <th>Chi tiết</th>
                    <th style="width: 120px;">Số tiền</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td class="item-name">Tiền cọc ban đầu</td>
                    <td class="details">Số tiền cọc đã đặt</td>
                    <td class="amount">{{ number_format($depositAmount, 0, ',', '.') }}đ</td>
                </tr>
                @if($deductionAmount > 0)
                <tr>
                    <td>2</td>
                    <td class="item-name">Khấu trừ</td>
                    <td class="details">Chi phí sửa chữa/làm sạch</td>
                    <td class="amount" style="color: #dc3545;">-{{ number_format($deductionAmount, 0, ',', '.') }}đ</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td></td>
                    <td></td>
                    <td><strong>Số tiền hoàn trả:</strong></td>
                    <td><strong>{{ number_format($finalRefundAmount, 0, ',', '.') }}đ</strong></td>
                </tr>
            </tbody>
        </table>

        <div class="refund-info">
            <h4>💰 Chi tiết hoàn tiền:</h4>
            <div class="refund-details">
                <span class="refund-label">- Tiền cọc ban đầu:</span>
                <span class="refund-value">{{ number_format($depositAmount, 0, ',', '.') }}đ</span>
            </div>
            @if($deductionAmount > 0)
            <div class="refund-details">
                <span class="refund-label">- Số tiền khấu trừ:</span>
                <span class="refund-value" style="color: #dc3545;">-{{ number_format($deductionAmount, 0, ',', '.') }}đ</span>
            </div>
            @endif
            <div class="refund-details refund-total">
                <span class="refund-label"><strong>Tổng tiền hoàn trả:</strong></span>
                <span class="refund-value"><strong style="color: #28a745; font-size: 1.2em;">{{ number_format($finalRefundAmount, 0, ',', '.') }}đ</strong></span>
            </div>
        </div>

        <div class="next-steps">
            <h4>📝 Các bước tiếp theo:</h4>
            <ul>
                <li>Kết quả kiểm kê đã được tự động xác nhận</li>
                <li>Quy trình hoàn tiền sẽ được tiến hành</li>
                <li>Bạn sẽ nhận được thông báo khi hoàn tiền thành công</li>
                <li>Nếu có thắc mắc, vui lòng liên hệ với chúng tôi</li>
            </ul>
        </div>

        <p class="message">
            <strong>Lưu ý:</strong> Việc tự động xác nhận này được thực hiện theo quy định của hệ thống.
            Nếu bạn có bất kỳ thắc mắc nào về kết quả kiểm kê, vui lòng liên hệ với chúng tôi ngay.
        </p>

        <div class="cta-section">
            <a href="{{ url('https://sghood.com.vn/quan-ly/kiem-ke') }}" class="cta-button" style="color: #ffffff;">
                <span class="icon">📄</span> Xem chi tiết kiểm kê
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
