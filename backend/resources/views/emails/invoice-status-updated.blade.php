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
        .header { background: linear-gradient(135deg, #28a745, #218838); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 24px; margin-bottom: 10px; font-weight: 600; }
        .header p { font-size: 16px; opacity: 0.9; }
        .content { padding: 40px 30px; }
        .greeting { font-size: 18px; margin-bottom: 20px; color: #2c3e50; }

        .status-update { background: #f8f9fa; border-radius: 10px; padding: 25px; margin: 25px 0; border-left: 4px solid #28a745; text-align: center; }
        .status-update h2 { color: #28a745; margin-bottom: 5px; font-size: 20px; font-weight: bold; }
        .status-update .invoice-code { color: #495057; font-size: 16px; margin-bottom: 15px; }

        .status-change { display: flex; align-items: center; justify-content: center; margin: 20px 0; flex-wrap: wrap; }
        .status-old, .status-new { padding: 10px 20px; border-radius: 20px; font-weight: bold; margin: 5px; }
        .status-old { background: #f8d7da; color: #721c24; }
        .status-new { background: #d4edda; color: #155724; }
        .status-arrow { margin: 0 15px; font-size: 24px; color: #28a745; }

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
            .status-change { flex-direction: column; }
            .status-arrow { transform: rotate(90deg); margin: 10px 0; }
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="header">
        <h1>🔄 Cập nhật trạng thái hóa đơn</h1>
        <p>Trạng thái hóa đơn của bạn đã được cập nhật thành công</p>
    </div>

    <div class="content">
        <div class="greeting">
            Xin chào <strong>{{ $user->full_name ?? $user->name }}</strong>,
        </div>

        <div class="success-message">
            <h4>✅ Trạng thái hóa đơn đã được cập nhật</h4>
            <p>Hóa đơn của bạn (Mã hóa đơn: <strong style="color: #28a745;">#{{ $invoice->code }}</strong>) đã được cập nhật trạng thái thành công.</p>
        </div>

        <div class="status-update">
            <h2>THÔNG BÁO CẬP NHẬT TRẠNG THÁI</h2>
            <div class="invoice-code">Hóa đơn tháng {{ $invoice->month }}/{{ $invoice->year }}</div>
            
            <div class="status-change">
                <div class="status-old">{{ $oldStatus ?: 'Chưa xác định' }}</div>
                <div class="status-arrow">→</div>
                <div class="status-new">{{ $newStatus }}</div>
            </div>
        </div>

        <div class="customer-info">
            <div class="customer-left">
                <div class="info-item">
                    <span class="info-label">Khách hàng:</span>
                    <span class="info-value">{{ $user->full_name ?? $user->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Điện thoại:</span>
                    <span class="info-value">{{ $user->phone ?? 'N/A' }}</span>
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
                <div class="info-item">
                    <span class="info-label">Trạng thái mới:</span>
                    <span class="info-value">
                        <strong style="color: {{ $newStatus === 'Đã trả' ? '#28a745' : '#ffc107' }};">
                            {{ $newStatus }}
                        </strong>
                    </span>
                </div>
            </div>
        </div>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Thông tin</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="item-name">Mã hóa đơn</td>
                    <td class="amount"><strong>{{ $invoice->code }}</strong></td>
                </tr>
                <tr>
                    <td class="item-name">Tháng/Năm</td>
                    <td class="amount">{{ $invoice->month }}/{{ $invoice->year }}</td>
                </tr>
                <tr>
                    <td class="item-name">Ngày cập nhật</td>
                    <td class="amount">{{ now()->format('d/m/Y H:i:s') }}</td>
                </tr>
                <tr class="total-row">
                    <td><strong>Tổng tiền</strong></td>
                    <td><strong>{{ number_format($invoice->total_amount, 0, ',', '.') }}đ</strong></td>
                </tr>
            </tbody>
        </table>

        @if($newStatus === 'Đã trả')
        <div class="success-message">
            <h4>🎉 Cảm ơn bạn đã thanh toán!</h4>
            <p>Hóa đơn của bạn đã được xác nhận thanh toán thành công. Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi.</p>
        </div>
        @else
        <div class="payment-info">
            <h4>📋 Thông tin thanh toán:</h4>
            <div class="payment-details">
                <span class="payment-label">Số tiền cần thanh toán:</span>
                <span class="payment-value">{{ number_format($invoice->total_amount, 0, ',', '.') }}đ</span>
            </div>
            <div class="payment-details">
                <span class="payment-label">Trạng thái hiện tại:</span>
                <span class="payment-value">{{ $newStatus }}</span>
            </div>
        </div>
        @endif

        <p class="message">
            Nếu bạn có bất kỳ thắc mắc nào về việc cập nhật trạng thái hóa đơn này, vui lòng liên hệ với chúng tôi qua thông tin bên dưới.
        </p>

        <div class="cta-section">
            <a href="{{ url('https://sghood.com.vn/quan-ly/hoa-don/') }}" class="cta-button" style="color: #ffffff;">
                <span class="icon">📄</span> Xem chi tiết hóa đơn
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
