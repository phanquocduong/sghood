<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo kết thúc hợp đồng sớm</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; background-color: #f8f9fa; color: #333; }
        .email-container { max-width: 600px; margin: 20px auto; background: white; border: black 1px solid; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #dc3545, #ff6b6b); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 24px; margin-bottom: 10px; font-weight: 600; }
        .header p { font-size: 16px; opacity: 0.9; }
        .content { padding: 40px 30px; }
        .greeting { font-size: 18px; margin-bottom: 20px; color: #2c3e50; }

        .termination-header { background: #f8f9fa; border-radius: 10px; padding: 25px; margin: 25px 0; border-left: 4px solid #dc3545; text-align: center; }
        .termination-header h2 { color: #dc3545; margin-bottom: 5px; font-size: 20px; font-weight: bold; }
        .termination-header .date { color: #495057; font-size: 16px; margin-bottom: 15px; }

        .customer-info { display: flex; justify-content: space-between; margin: 20px 0; flex-wrap: wrap; }
        .customer-left, .customer-right { flex: 1; min-width: 250px; }
        .customer-left { margin-right: 20px; }
        .info-item { margin-bottom: 8px; }
        .info-label { font-weight: 600; color: #495057; display: inline-block; min-width: 80px; }
        .info-value { color: #212529; }
        .contract-number { background: #f8d7da; padding: 5px 15px; border-radius: 5px; display: inline-block; font-weight: bold; color: #721c24; }

        .contract-table { width: 100%; border-collapse: collapse; margin: 25px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        .contract-table th { background: #dc3545; color: white; padding: 15px 12px; text-align: center; font-weight: 600; font-size: 14px; }
        .contract-table td { padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6; }
        .contract-table tr:nth-child(even) { background-color: #f8f9fa; }
        .contract-table tr:hover { background-color: #fdeaea; }
        .contract-table .item-name { text-align: left; font-weight: 500; }
        .contract-table .item-value { text-align: left; font-weight: 400; }

        .termination-reason { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .termination-reason h4 { color: #856404; margin-bottom: 10px; font-size: 16px; }
        .termination-reason p { color: #856404; line-height: 1.5; }

        .important-note { background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .important-note h4 { color: #721c24; margin-bottom: 10px; font-size: 16px; }
        .important-note p { color: #721c24; line-height: 1.5; margin-bottom: 5px; }

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
            .contract-table { font-size: 12px; }
            .contract-table th, .contract-table td { padding: 8px 6px; }
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="header">
        <h1>🏠 THÔNG BÁO KẾT THÚC HỢP ĐỒNG SỚM</h1>
        <p>Hệ thống quản lý cho thuê phòng SGHood</p>
    </div>

    <div class="content">
        <div class="greeting">
            Xin chào <strong>{{ $userName }}</strong>,
        </div>

        <div class="important-note">
            <h4>⚠️ Thông báo quan trọng</h4>
            <p>Hợp đồng thuê phòng của bạn (Mã hợp đồng: <strong style="color: #dc3545;">HD{{ $contractId }}</strong>) đã được kết thúc sớm theo quyết định của ban quản lý.</p>
        </div>

        <div class="termination-header">
            <h2>THÔNG BÁO KẾT THÚC HỢP ĐỒNG</h2>
            <div class="date">Ngày kết thúc: {{ $terminationDate }}</div>
        </div>

        <div class="customer-info">
            <div class="customer-left">
                <div class="info-item">
                    <span class="info-label">Khách hàng:</span>
                    <span class="info-value">{{ $userName }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Phòng:</span>
                    <span class="info-value">{{ $roomName }}</span>
                </div>
            </div>
            <div class="customer-right">
                <div class="info-item">
                    <span class="info-label">Mã hợp đồng:</span>
                    <span class="contract-number">HD{{ $contractId }}</span>
                </div>
            </div>
        </div>

        <table class="contract-table">
            <thead>
                <tr>
                    <th style="width: 200px;">Thông tin</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="item-name">Tên phòng</td>
                    <td class="item-value">{{ $roomName }}</td>
                </tr>
                <tr>
                    <td class="item-name">Ngày bắt đầu</td>
                    <td class="item-value">{{ $startDate }}</td>
                </tr>
                <tr>
                    <td class="item-name">Ngày kết thúc dự kiến</td>
                    <td class="item-value">{{ $endDate }}</td>
                </tr>
                <tr>
                    <td class="item-name"><strong>Ngày kết thúc thực tế</strong></td>
                    <td class="item-value"><strong style="color: #dc3545;">{{ $terminationDate }}</strong></td>
                </tr>
                <tr>
                    <td class="item-name">Giá thuê hàng tháng</td>
                    <td class="item-value">{{ number_format($rentalPrice, 0, ',', '.') }} VNĐ</td>
                </tr>
            </tbody>
        </table>

        @if($terminationReason)
        <div class="termination-reason">
            <h4>📝 Lý do kết thúc hợp đồng sớm:</h4>
            <p>{{ $terminationReason }}</p>
        </div>
        @endif

        <div class="important-note">
            <h4>⚠️ Lưu ý quan trọng:</h4>
            <p>• Vui lòng sắp xếp việc dọn dẹp và trả phòng trong thời gian sớm nhất.</p>
            <p>• Mọi vấn đề về tiền cọc và chi phí phát sinh sẽ được xử lý theo quy định trong hợp đồng.</p>
            <p>• Nếu có thắc mắc, vui lòng liên hệ với chúng tôi qua thông tin dưới đây.</p>
        </div>

        <p class="message">
            Chúng tôi rất tiếc vì sự bất tiện này và cảm ơn bạn đã sử dụng dịch vụ của SGHood. Để biết thêm chi tiết, vui lòng đăng nhập vào hệ thống:
        </p>

        <div class="cta-section">
            <a href="{{ config('app.url') }}" class="cta-button" style="color: #ffffff;">
                <span class="icon">🏠</span> Đăng nhập hệ thống
            </a>
        </div>

        <p class="message">
            Nếu bạn cần hỗ trợ, vui lòng liên hệ với chúng tôi. Xin cảm ơn!
        </p>
    </div>

    <div class="footer">
        <p><strong>📧 Đội ngũ hỗ trợ khách hàng</strong></p>
        <div class="contact-info">
            <p style="color: #ffffff;">📞 Hotline: 1900-xxxx | ✉️ Email: support@sghood.com</p>
            <p style="color: #ffffff;">🌐 Website: sghood.com.vn</p>
            <p style="color: #ffffff;">📍 Địa chỉ: 123 Đường ABC, Quận XYZ, TP.HCM</p>
        </div>
        <p style="margin-top: 15px; font-size: 12px; opacity: 0.7;">
            © {{ date('Y') }} SGHood. Tất cả quyền được bảo lưu. Email này được gửi tự động từ hệ thống.
        </p>
    </div>
</div>
</body>
</html>
