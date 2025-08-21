<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo trạng thái kiểm kê</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; background-color: #f8f9fa; color: #333; }
        .email-container { max-width: 600px; margin: 20px auto; background: white; border: black 1px solid; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #28a745, #218838); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 24px; margin-bottom: 10px; font-weight: 600; }
        .header p { font-size: 16px; opacity: 0.9; }
        .content { padding: 40px 30px; }
        .greeting { font-size: 18px; margin-bottom: 20px; color: #2c3e50; }

        .checkout-header { background: #f8f9fa; border-radius: 10px; padding: 25px; margin: 25px 0; border-left: 4px solid #28a745; text-align: center; }
        .checkout-header h2 { color: #28a745; margin-bottom: 5px; font-size: 20px; font-weight: bold; }
        .checkout-header .checkout-date { color: #495057; font-size: 16px; margin-bottom: 15px; }

        .customer-info { display: flex; justify-content: space-between; margin: 20px 0; flex-wrap: wrap; }
        .customer-left, .customer-right { flex: 1; min-width: 250px; }
        .customer-left { margin-right: 20px; }
        .info-item { margin-bottom: 8px; }
        .info-label { font-weight: 600; color: #495057; display: inline-block; min-width: 120px; }
        .info-value { color: #212529; }
        .room-number { background: #d4edda; padding: 5px 15px; border-radius: 5px; display: inline-block; font-weight: bold; color: #155724; }

        .checkout-table { width: 100%; border-collapse: collapse; margin: 25px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        .checkout-table th { background: #28a745; color: white; padding: 15px 12px; text-align: center; font-weight: 600; font-size: 14px; }
        .checkout-table td { padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6; }
        .checkout-table tr:nth-child(even) { background-color: #f8f9fa; }
        .checkout-table tr:hover { background-color: #e8f5e8; }
        .checkout-table .item-name { text-align: left; font-weight: 500; }
        .checkout-table .details { text-align: left; font-size: 13px; color: #666; }
        .checkout-table .amount { text-align: right; font-weight: 600; }
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
            .checkout-table { font-size: 12px; }
            .checkout-table th, .checkout-table td { padding: 8px 6px; }
            .payment-details { flex-direction: column; }
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="header">
        <h1>✅ Trạng thái kiểm kê đã được cập nhật!</h1>
        <p>Thông tin kiểm kê của bạn đã được xử lý</p>
    </div>

    <div class="content">
        <div class="greeting">
            Xin chào <strong>{{ $userName }}</strong>,
        </div>

        <div class="success-message">
            <h4>🎉 Thông báo!</h4>
            <p>Quá trình kiểm kê của bạn đã được <strong style="color: #28a745;">hoàn tất</strong>. Vui lòng kiểm tra thông tin chi tiết dưới đây.</p>
        </div>

        <div class="checkout-header">
            <h2>THÔNG BÁO KẾT QUỢ KIỂM KÊ</h2>
            <div class="checkout-date">Ngày kiểm kê: {{ \Carbon\Carbon::parse($checkOutDate)->format('d/m/Y') }}</div>
        </div>

        <div class="customer-info">
            <div class="customer-left">
                <div class="info-item">
                    <span class="info-label">Khách hàng:</span>
                    <span class="info-value">{{ $userName }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Trạng thái:</span>
                    <span class="info-value">Đã kiểm kê</span>
                </div>
            </div>
            <div class="customer-right">
                <div class="info-item">
                    <span class="info-label">Phòng số:</span>
                    <span class="room-number">{{ $roomName }}</span>
                </div>
            </div>
        </div>

        <table class="checkout-table">
            <thead>
                <tr>
                    <th style="width: 60px;">STT</th>
                    <th style="width: 120px;">Khoản mục</th>
                    <th>Chi tiết</th>
                    <th style="width: 120px;">Số tiền</th>
                </tr>
            </thead>
            <tbody>
                @if($checkout->deduction_amount)
                <tr>
                    <td>1</td>
                    <td class="item-name">Khấu trừ</td>
                    <td class="details">Tiền bồi thường thiệt hại</td>
                    <td class="amount">{{ number_format($checkout->deduction_amount, 0, ',', '.') }}đ</td>
                </tr>
                @endif
                @if($checkout->final_refunded_amount)
                <tr class="total-row">
                    <td></td>
                    <td></td>
                    <td><strong>Số tiền hoàn trả:</strong></td>
                    <td><strong>{{ number_format($checkout->final_refunded_amount, 0, ',', '.') }}đ</strong></td>
                </tr>
                @endif
                @if(!$checkout->deduction_amount && !$checkout->final_refunded_amount)
                <tr>
                    <td colspan="4" style="text-align: center; color: #28a745; font-weight: bold;">
                        Không có phát sinh chi phí
                    </td>
                </tr>
                @endif
            </tbody>
        </table>

        @if($checkout->final_refunded_amount)
        <div class="payment-info">
            <h4>💰 Thông tin hoàn trả:</h4>
            <div class="payment-details">
                <span class="payment-label">- Số tiền hoàn trả:</span>
                <span class="payment-value">{{ number_format($checkout->final_refunded_amount, 0, ',', '.') }} VNĐ</span>
            </div>
            @if($checkout->deduction_amount)
            <div class="payment-details">
                <span class="payment-label">- Số tiền khấu trừ:</span>
                <span class="payment-value">{{ number_format($checkout->deduction_amount, 0, ',', '.') }} VNĐ</span>
            </div>
            @endif
        </div>
        @endif

        <p class="message">
            Vui lòng truy cập trang quản lý kiểm kê để xem chi tiết và xác nhận thông tin trước 7 ngày! Nhấp vào nút bên dưới để tiếp tục:
        </p>

        <div class="cta-section">
            <a href="https://sghood.com.vn/quan-ly/kiem-ke" class="cta-button" style="color: #ffffff;">
                <span class="icon">🏠</span> Xem chi tiết kiểm kê
            </a>
        </div>

        <p class="message">
            Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi. Nếu có bất kỳ câu hỏi nào, vui lòng liên hệ với đội ngũ hỗ trợ.
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
