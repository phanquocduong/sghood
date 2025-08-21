<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo chấp nhận đặt phòng</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; background-color: #f8f9fa; color: #333; }
        .email-container { max-width: 600px; margin: 20px auto; background: white; border: black 1px solid; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #28a745, #218838); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 24px; margin-bottom: 10px; font-weight: 600; }
        .header p { font-size: 16px; opacity: 0.9; }
        .content { padding: 40px 30px; }
        .greeting { font-size: 18px; margin-bottom: 20px; color: #2c3e50; }

        .booking-header { background: #f8f9fa; border-radius: 10px; padding: 25px; margin: 25px 0; border-left: 4px solid #28a745; text-align: center; }
        .booking-header h2 { color: #28a745; margin-bottom: 5px; font-size: 20px; font-weight: bold; }
        .booking-header .status { color: #495057; font-size: 16px; margin-bottom: 15px; }

        .customer-info { display: flex; justify-content: space-between; margin: 20px 0; flex-wrap: wrap; }
        .customer-left, .customer-right { flex: 1; min-width: 250px; }
        .customer-left { margin-right: 20px; }
        .info-item { margin-bottom: 8px; }
        .info-label { font-weight: 600; color: #495057; display: inline-block; min-width: 80px; }
        .info-value { color: #212529; }
        .room-number { background: #d4edda; padding: 5px 15px; border-radius: 5px; display: inline-block; font-weight: bold; color: #155724; }

        .booking-table { width: 100%; border-collapse: collapse; margin: 25px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        .booking-table th { background: #28a745; color: white; padding: 15px 12px; text-align: center; font-weight: 600; font-size: 14px; }
        .booking-table td { padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6; }
        .booking-table tr:nth-child(even) { background-color: #f8f9fa; }
        .booking-table tr:hover { background-color: #e8f5e8; }
        .booking-table .item-name { text-align: left; font-weight: 500; }
        .booking-table .details { text-align: left; font-size: 13px; color: #666; }
        .booking-table .value { text-align: left; font-weight: 600; }

        .contract-info { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .contract-info h4 { color: #856404; margin-bottom: 10px; font-size: 16px; }
        .contract-details { display: flex; justify-content: space-between; margin-bottom: 8px; }
        .contract-label { font-weight: 600; color: #856404; }
        .contract-value { color: #856404; font-weight: bold; }

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
            .booking-table { font-size: 12px; }
            .booking-table th, .booking-table td { padding: 8px 6px; }
            .contract-details { flex-direction: column; }
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="header">
        <h1>✅ Đặt phòng của bạn đã được chấp nhận!</h1>
        <p>Chúng tôi rất vui được xác nhận yêu cầu đặt phòng của bạn</p>
    </div>
    <div class="content">
        <div class="greeting">
            Xin chào <strong>{{ $userName }}</strong>,
        </div>

        <div class="success-message">
            <h4>🎉 Chúc mừng bạn!</h4>
            <p>Yêu cầu đặt phòng của bạn đã được <strong style="color: #28a745;">chấp nhận</strong>. Trước tiên bạn hãy điền thông tin <strong style="color: red;">hợp đồng</strong> cho chúng tôi.</p>
        </div>

        <div class="booking-header">
            <h2>THÔNG TIN ĐẶT PHÒNG</h2>
            <div class="status">Trạng thái: Đã được chấp nhận</div>
        </div>

        <div class="customer-info">
            <div class="customer-left">
                <div class="info-item">
                    <span class="info-label">Khách hàng:</span>
                    <span class="info-value">{{ $userName }}</span>
                </div>
                @if($booking->created_at)
                <div class="info-item">
                    <span class="info-label">Ngày đặt:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($booking->created_at)->format('d/m/Y H:i') }}</span>
                </div>
                @endif
            </div>
            <div class="customer-right">
                <div class="info-item">
                    <span class="info-label">Phòng số:</span>
                    <span class="room-number">{{ $roomName }}</span>
                </div>
            </div>
        </div>

        <table class="booking-table">
            <thead>
                <tr>
                    <th style="width: 60px;">STT</th>
                    <th style="width: 150px;">Thông tin</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td class="item-name">🏠 Tên phòng</td>
                    <td class="value">{{ $roomName }}</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td class="item-name">📅 Ngày bắt đầu</td>
                    <td class="value">{{ $startDate }}</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td class="item-name">📅 Ngày kết thúc</td>
                    <td class="value">{{ $endDate }}</td>
                </tr>
                @if($booking->created_at)
                <tr>
                    <td>4</td>
                    <td class="item-name">🕐 Ngày đặt</td>
                    <td class="value">{{ \Carbon\Carbon::parse($booking->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
                @endif
            </tbody>
        </table>

        <div class="contract-info">
            <h4>📋 Thông tin hợp đồng:</h4>
            <div class="contract-details">
                <span class="contract-label">- Bước tiếp theo:</span>
                <span class="contract-value">Điền thông tin hợp đồng</span>
            </div>
            <div class="contract-details">
                <span class="contract-label">- Trạng thái:</span>
                <span class="contract-value">Chờ hoàn thiện hợp đồng</span>
            </div>
        </div>

        <p class="message">
            Để hoàn tất quá trình đặt phòng, vui lòng truy cập vào trang quản lý đặt phòng của chúng tôi và điền thông tin hợp đồng cần thiết. Bạn có thể làm điều này bằng cách nhấp vào nút bên dưới:
        </p>

        <div class="cta-section">
            <a href="https://sghood.com.vn/quan-ly/hop-dong" class="cta-button" style="color: #ffffff;">
                <span class="icon">🏠</span> Truy cập trang quản lý đặt phòng
            </a>
        </div>

        <p class="message">
            Cảm ơn bạn đã tin tưởng và lựa chọn dịch vụ của chúng tôi. Chúc bạn có một trải nghiệm tuyệt vời!
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
