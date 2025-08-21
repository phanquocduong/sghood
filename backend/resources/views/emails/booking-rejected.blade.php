<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo từ chối đặt phòng</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; background-color: #f8f9fa; color: #333; }
        .email-container { max-width: 600px; margin: 20px auto; background: white; border: black 1px solid; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #dc3545, #c82333); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 24px; margin-bottom: 10px; font-weight: 600; }
        .header p { font-size: 16px; opacity: 0.9; }
        .content { padding: 40px 30px; }
        .greeting { font-size: 18px; margin-bottom: 20px; color: #2c3e50; }

        .booking-header { background: #f8f9fa; border-radius: 10px; padding: 25px; margin: 25px 0; border-left: 4px solid #dc3545; text-align: center; }
        .booking-header h2 { color: #dc3545; margin-bottom: 5px; font-size: 20px; font-weight: bold; }
        .booking-header .status { color: #495057; font-size: 16px; margin-bottom: 15px; }

        .booking-info { display: flex; justify-content: space-between; margin: 20px 0; flex-wrap: wrap; }
        .booking-left, .booking-right { flex: 1; min-width: 250px; }
        .booking-left { margin-right: 20px; }
        .info-item { margin-bottom: 8px; }
        .info-label { font-weight: 600; color: #495057; display: inline-block; min-width: 120px; }
        .info-value { color: #212529; }
        .room-name { background: #f8d7da; padding: 5px 15px; border-radius: 5px; display: inline-block; font-weight: bold; color: #721c24; }

        .booking-table { width: 100%; border-collapse: collapse; margin: 25px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        .booking-table th { background: #dc3545; color: white; padding: 15px 12px; text-align: center; font-weight: 600; font-size: 14px; }
        .booking-table td { padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6; }
        .booking-table tr:nth-child(even) { background-color: #f8f9fa; }
        .booking-table tr:hover { background-color: #f5c6cb; }
        .booking-table .item-name { text-align: left; font-weight: 500; }
        .booking-table .details { text-align: left; font-size: 13px; color: #666; }
        .booking-table .amount { text-align: right; font-weight: 600; }

        .rejection-reason { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .rejection-reason h4 { color: #856404; margin-bottom: 10px; font-size: 16px; }
        .rejection-reason p { color: #856404; line-height: 1.5; }

        .rejection-message { background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .rejection-message h4 { color: #721c24; margin-bottom: 10px; font-size: 16px; }
        .rejection-message p { color: #721c24; line-height: 1.5; }

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
            .booking-info { flex-direction: column; }
            .booking-left { margin-right: 0; margin-bottom: 15px; }
            .booking-table { font-size: 12px; }
            .booking-table th, .booking-table td { padding: 8px 6px; }
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="header">
        <h1>🚫 Thông báo từ chối đặt phòng</h1>
        <p>Chúng tôi rất tiếc phải thông báo về quyết định này</p>
    </div>
    <div class="content">
        <div class="greeting">
            Xin chào <strong>{{ $userName }}</strong>,
        </div>

        <div class="rejection-message">
            <h4>❌ Yêu cầu đặt phòng đã bị từ chối</h4>
            <p>Chúng tôi rất tiếc phải thông báo rằng yêu cầu đặt phòng của bạn đã bị <strong style="color: #dc3545;">từ chối</strong>. Vui lòng xem chi tiết bên dưới.</p>
        </div>

        <div class="booking-header">
            <h2>THÔNG TIN ĐẶT PHÒNG</h2>
            <div class="status">Trạng thái: <strong style="color: #dc3545;">ĐÃ TỪ CHỐI</strong></div>
        </div>

        <div class="booking-info">
            <div class="booking-left">
                <div class="info-item">
                    <span class="info-label">🏠 Tên phòng:</span>
                    <span class="room-name">{{ $roomName }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">📅 Ngày bắt đầu:</span>
                    <span class="info-value">{{ $startDate }}</span>
                </div>
            </div>
            <div class="booking-right">
                <div class="info-item">
                    <span class="info-label">📅 Ngày kết thúc:</span>
                    <span class="info-value">{{ $endDate }}</span>
                </div>
                @if($booking->created_at)
                <div class="info-item">
                    <span class="info-label">🕐 Ngày đặt:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($booking->created_at)->format('d/m/Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>

        <table class="booking-table">
            <thead>
                <tr>
                    <th style="width: 60px;">STT</th>
                    <th style="width: 120px;">Thông tin</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td class="item-name">Phòng</td>
                    <td class="details">{{ $roomName }}</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td class="item-name">Thời gian</td>
                    <td class="details">{{ $startDate }} - {{ $endDate }}</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td class="item-name">Trạng thái</td>
                    <td class="details" style="color: #dc3545; font-weight: bold;">ĐÃ TỪ CHỐI</td>
                </tr>
            </tbody>
        </table>

        @if($rejectionReason)
        <div class="rejection-reason">
            <h4>💬 Lý do từ chối:</h4>
            <p>{{ $rejectionReason }}</p>
        </div>
        @endif

        <p class="message">
            Chúng tôi hiểu rằng điều này có thể gây ra sự bất tiện cho bạn. Tuy nhiên, bạn vẫn có thể:
        </p>

        <ul style="margin: 20px 0; padding-left: 20px; color: #6c757d;">
            <li style="margin-bottom: 8px;">🔍 Tìm kiếm các phòng khác còn trống</li>
            <li style="margin-bottom: 8px;">📞 Liên hệ trực tiếp với chúng tôi để được hỗ trợ</li>
            <li style="margin-bottom: 8px;">📝 Đặt phòng cho thời gian khác</li>
        </ul>

        <div class="cta-section">
            <a href="{{ url('https://sghood.com.vn/') }}" class="cta-button" style="color: #ffffff;">
                <span class="icon">🏠</span> Xem phòng khác
            </a>
        </div>

        <p class="message">
            Cảm ơn bạn đã quan tâm đến dịch vụ của chúng tôi. Chúng tôi hy vọng có cơ hội phục vụ bạn trong tương lai.
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
