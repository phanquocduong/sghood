<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo liên hệ từ khách hàng</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; background-color: #f8f9fa; color: #333; }
        .email-container { max-width: 600px; margin: 20px auto; background: white; border: black 1px solid; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 24px; margin-bottom: 10px; font-weight: 600; }
        .header p { font-size: 16px; opacity: 0.9; }
        .content { padding: 40px 30px; }
        .greeting { font-size: 18px; margin-bottom: 20px; color: #2c3e50; }

        .contact-header { background: #f8f9fa; border-radius: 10px; padding: 25px; margin: 25px 0; border-left: 4px solid #007bff; text-align: center; }
        .contact-header h2 { color: #007bff; margin-bottom: 5px; font-size: 20px; font-weight: bold; }
        .contact-header .timestamp { color: #495057; font-size: 16px; margin-bottom: 15px; }

        .customer-info { display: flex; justify-content: space-between; margin: 20px 0; flex-wrap: wrap; }
        .customer-left, .customer-right { flex: 1; min-width: 250px; }
        .customer-left { margin-right: 20px; }
        .info-item { margin-bottom: 8px; }
        .info-label { font-weight: 600; color: #495057; display: inline-block; min-width: 80px; }
        .info-value { color: #212529; font-weight: bold; }

        .contact-table { width: 100%; border-collapse: collapse; margin: 25px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        .contact-table th { background: #007bff; color: white; padding: 15px 12px; text-align: left; font-weight: 600; font-size: 14px; }
        .contact-table td { padding: 12px; text-align: left; border-bottom: 1px solid #dee2e6; }
        .contact-table tr:nth-child(even) { background-color: #f8f9fa; }
        .contact-table tr:hover { background-color: #e3f2fd; }
        .contact-table .field-name { font-weight: 600; color: #495057; width: 150px; }
        .contact-table .field-value { color: #212529; word-break: break-word; }

        .message-content { background: #e8f4fd; border: 1px solid #b3d7ff; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .message-content h4 { color: #0056b3; margin-bottom: 10px; font-size: 16px; }
        .message-content p { color: #0056b3; line-height: 1.6; font-style: italic; word-break: break-word; }

        .instructions-info { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .instructions-info h4 { color: #856404; margin-bottom: 10px; font-size: 16px; }
        .instructions-list { list-style: none; margin: 15px 0; padding: 0; }
        .instructions-list li { margin-bottom: 8px; color: #856404; }
        .instructions-list li:before { content: "✅ "; margin-right: 8px; }

        .success-message { background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .success-message h4 { color: #155724; margin-bottom: 10px; font-size: 16px; }
        .success-message p { color: #155724; line-height: 1.5; }

        .message { color: #6c757d; line-height: 1.8; margin: 20px 0; }
        .cta-section { text-align: center; margin: 30px 0; }
        .cta-button { display: inline-block; background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 12px 30px; text-decoration: none; border-radius: 25px; font-weight: 600; transition: all 0.3s ease; }
        .cta-button:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3); }
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
            .contact-table { font-size: 12px; }
            .contact-table th, .contact-table td { padding: 8px 6px; }
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="header">
        <h1>📧 Thông báo liên hệ mới</h1>
        <p>Có khách hàng vừa gửi yêu cầu liên hệ</p>
    </div>
    <div class="content">
        <div class="greeting">
            Xin chào <strong>Admin</strong>,
        </div>

        <div class="success-message">
            <h4>📧 Yêu cầu liên hệ mới</h4>
            <p>Một khách hàng đã gửi thông tin liên hệ qua website. Vui lòng kiểm tra và phản hồi <strong style="color: #28a745;">kịp thời</strong>.</p>
        </div>

        <div class="contact-header">
            <h2>THÔNG TIN LIÊN HỆ</h2>
            <div class="timestamp">Thời gian: {{ now()->format('d/m/Y H:i') }}</div>
        </div>

        <table class="contact-table">
            <thead>
                <tr>
                    <th style="width: 150px;">Thông tin</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="field-name">👨‍💼 Họ và tên:</td>
                    <td class="field-value"><strong>{{ $data['name'] }}</strong></td>
                </tr>
                <tr>
                    <td class="field-name">📧 Email:</td>
                    <td class="field-value">{{ $data['email'] }}</td>
                </tr>
                <tr>
                    <td class="field-name">📋 Chủ đề:</td>
                    <td class="field-value">{{ $data['subject'] }}</td>
                </tr>
                <tr>
                    <td class="field-name">🕐 Thời gian gửi:</td>
                    <td class="field-value">{{ now()->format('d/m/Y H:i') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="message-content">
            <h4>💬 Nội dung tin nhắn:</h4>
            <p>{{ $data['message'] }}</p>
        </div>

        <div class="instructions-info">
            <h4>📋 Hướng dẫn xử lý:</h4>
            <ul class="instructions-list">
                <li>Phản hồi trong vòng 24 giờ</li>
                <li>Liên hệ trực tiếp nếu cần thiết</li>
                <li>Ghi chú thông tin vào hệ thống CRM</li>
                <li>Đưa ra giải pháp phù hợp</li>
            </ul>
        </div>

        <p class="message">
            Để đảm bảo chất lượng dịch vụ, vui lòng xử lý yêu cầu này một cách chuyên nghiệp và kịp thời.
        </p>

        <div class="cta-section">
            <a href="mailto:{{ $data['email'] }}" class="cta-button" style="color: #ffffff;">
                <span class="icon">📧</span> Phản hồi ngay
            </a>
        </div>

        <p class="message">
            <strong>Lưu ý:</strong> Email này được gửi tự động từ hệ thống website. Vui lòng không trả lời trực tiếp email này.
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
