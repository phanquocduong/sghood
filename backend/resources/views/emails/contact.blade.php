<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo liên hệ từ khách hàng</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background-color: #f8f9fa;
            color: #333;
        }

        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border: black 1px solid;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .content {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .contact-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin: 25px 0;
            border-left: 4px solid #007bff;
        }

        .contact-info h3 {
            color: #007bff;
            margin-bottom: 15px;
            font-size: 18px;
        }

        .info-row {
            display: flex;
            margin-bottom: 12px;
            align-items: flex-start;
        }

        .info-label {
            font-weight: 600;
            color: #495057;
            min-width: 120px;
            margin-right: 15px;
        }

        .info-value {
            color: #212529;
            flex: 1;
            word-break: break-word;
        }

        .message-content {
            background: #e8f4fd;
            border: 1px solid #b3d7ff;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }

        .message-content h4 {
            color: #0056b3;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .message-content p {
            color: #0056b3;
            line-height: 1.6;
            font-style: italic;
        }

        .message {
            color: #6c757d;
            line-height: 1.8;
            margin: 20px 0;
        }

        .cta-section {
            text-align: center;
            margin: 30px 0;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .footer {
            background: #343a40;
            color: white;
            padding: 25px;
            text-align: center;
        }

        .footer p {
            margin-bottom: 10px;
            opacity: 0.8;
        }

        .contact-footer {
            font-size: 14px;
            opacity: 0.7;
        }

        .icon {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 8px;
            vertical-align: middle;
        }

        .priority-high {
            background: #fff3cd;
            border-left-color: #ffc107;
            border: 1px solid #ffeaa7;
        }

        .priority-high h4 {
            color: #856404;
        }

        .priority-high p {
            color: #856404;
        }

        @media (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 10px;
            }

            .content {
                padding: 30px 20px;
            }

            .header {
                padding: 25px 20px;
            }

            .info-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .info-label {
                min-width: auto;
                margin-bottom: 5px;
            }
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

            <p class="message">
                Một khách hàng đã gửi thông tin liên hệ qua website. Vui lòng kiểm tra và phản hồi <strong style="color: #007bff;">kịp thời</strong>.
            </p>

            <div class="contact-info">
                <h3>👤 Thông tin khách hàng</h3>
                <div class="info-row">
                    <span class="info-label">👨‍💼 Họ và tên:</span>
                    <span class="info-value"><strong>{{ $data['name'] }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">📧 Email:</span>
                    <span class="info-value">{{ $data['email'] }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">📋 Chủ đề:</span>
                    <span class="info-value">{{ $data['subject'] }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">🕐 Thời gian:</span>
                    <span class="info-value">{{ now()->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            <div class="message-content">
                <h4>💬 Nội dung tin nhắn:</h4>
                <p>{{ $data['message'] }}</p>
            </div>

            <p class="message">
                Để đảm bảo chất lượng dịch vụ, vui lòng:
            </p>

            <ul style="margin: 20px 0; padding-left: 20px; color: #6c757d;">
                <li style="margin-bottom: 8px;">✅ Phản hồi trong vòng 24 giờ</li>
                <li style="margin-bottom: 8px;">📞 Liên hệ trực tiếp nếu cần thiết</li>
                <li style="margin-bottom: 8px;">📝 Ghi chú thông tin vào hệ thống CRM</li>
                <li style="margin-bottom: 8px;">🎯 Đưa ra giải pháp phù hợp</li>
            </ul>

            <div class="cta-section">
                <a href="mailto:{{ $data['email'] }}" class="cta-button" style="color: #ffffff;">
                    📧 Phản hồi ngay
                </a>
            </div>

            <p class="message">
                <strong>Lưu ý:</strong> Email này được gửi tự động từ hệ thống website. Vui lòng không trả lời trực tiếp email này.
            </p>
        </div>
        <div class="footer">
            <p><strong>🏢 Hệ thống quản lý Tro Việt Platform</strong></p>
            <div class="contact-footer">
                <p style="color: #ffffff;">📞 Hotline: 082 828 3169 | ✉️ Email: sghood@gmail.com</p>
                <p style="color: #ffffff;">🌐 Website: sghood.store</p>
            </div>
        </div>
    </div>
</body>
</html>
