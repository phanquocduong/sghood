<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo chấp nhận gia hạn hợp đồng</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; background-color: #f8f9fa; color: #333; }
        .email-container { max-width: 600px; margin: 20px auto; background: white; border: black 1px solid; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #28a745, #218838); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 24px; margin-bottom: 10px; font-weight: 600; }
        .header p { font-size: 16px; opacity: 0.9; }
        .content { padding: 40px 30px; }
        .greeting { font-size: 18px; margin-bottom: 20px; color: #2c3e50; }

        .extension-header { background: #f8f9fa; border-radius: 10px; padding: 25px; margin: 25px 0; border-left: 4px solid #28a745; text-align: center; }
        .extension-header h2 { color: #28a745; margin-bottom: 5px; font-size: 20px; font-weight: bold; }
        .extension-header .status { color: #495057; font-size: 16px; margin-bottom: 15px; }

        .customer-info { display: flex; justify-content: space-between; margin: 20px 0; flex-wrap: wrap; }
        .customer-left, .customer-right { flex: 1; min-width: 250px; }
        .customer-left { margin-right: 20px; }
        .info-item { margin-bottom: 8px; }
        .info-label { font-weight: 600; color: #495057; display: inline-block; min-width: 80px; }
        .info-value { color: #212529; }
        .room-number { background: #d4edda; padding: 5px 15px; border-radius: 5px; display: inline-block; font-weight: bold; color: #155724; }

        .extension-table { width: 100%; border-collapse: collapse; margin: 25px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        .extension-table th { background: #28a745; color: white; padding: 15px 12px; text-align: center; font-weight: 600; font-size: 14px; }
        .extension-table td { padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6; }
        .extension-table tr:nth-child(even) { background-color: #f8f9fa; }
        .extension-table tr:hover { background-color: #e8f5e8; }
        .extension-table .item-name { text-align: left; font-weight: 500; }
        .extension-table .details { text-align: left; font-size: 13px; color: #666; }
        .extension-table .amount { text-align: right; font-weight: 600; }

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
            .extension-table { font-size: 12px; }
            .extension-table th, .extension-table td { padding: 8px 6px; }
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="header">
        <h1>✅ Gia hạn hợp đồng được chấp nhận</h1>
        <p>Chúng tôi rất vui được xác nhận yêu cầu gia hạn hợp đồng của bạn</p>
    </div>
    
    <div class="content">
        <div class="greeting">
            Xin chào <strong>{{ $contractExtension->contract->user->name ?? 'Khách hàng' }}</strong>,
        </div>

        <div class="success-message">
            <h4>🎉 Chúc mừng bạn!</h4>
            <p>Yêu cầu gia hạn hợp đồng của bạn đã được <strong style="color: #28a745;">chấp nhận</strong>.</p>
        </div>

        <div class="extension-header">
            <h2>THÔNG BÁO GIA HẠN HỢP ĐỒNG</h2>
            <div class="status">Đã được phê duyệt</div>
        </div>

        <div class="customer-info">
            <div class="customer-left">
                <div class="info-item">
                    <span class="info-label">Kính gửi:</span>
                    <span class="info-value">{{ $contractExtension->contract->user->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Điện thoại:</span>
                    <span class="info-value">{{ $contractExtension->contract->user->phone ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="customer-right">
                <div class="info-item">
                    <span class="info-label">Phòng số:</span>
                    <span class="room-number">{{ $contractExtension->contract->room->name ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <table class="extension-table">
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
                    <td class="item-name">Ngày kết thúc mới</td>
                    <td class="details">{{ \Carbon\Carbon::parse($contractExtension->new_end_date)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td class="item-name">Giá thuê mới</td>
                    <td class="details">{{ number_format($contractExtension->new_rental_price, 0, ',', '.') }} VNĐ</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td class="item-name">Ngày tạo yêu cầu</td>
                    <td class="details">{{ \Carbon\Carbon::parse($contractExtension->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td>4</td>
                    <td class="item-name">Trạng thái</td>
                    <td class="details"><span style="color: #28a745; font-weight: bold;">Đã được chấp nhận</span></td>
                </tr>
            </tbody>
        </table>

        <p class="message">
            Vui lòng kiểm tra thông tin hợp đồng của bạn trong hệ thống quản lý để đảm bảo mọi thứ chính xác. Bạn có thể truy cập trang quản lý hợp đồng bằng cách nhấp vào nút bên dưới:
        </p>

        <div class="cta-section">
            <a href="{{ url('https://sghood.com.vn/quan-ly/hop-dong') }}" class="cta-button" style="color: #ffffff;">
                <span class="icon">🏠</span> Xem hợp đồng
            </a>
        </div>

        <p class="message">
            Cảm ơn bạn đã tiếp tục sử dụng dịch vụ của chúng tôi. Chúc bạn có một trải nghiệm tuyệt vời!
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
