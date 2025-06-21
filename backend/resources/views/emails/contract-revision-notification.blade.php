<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu nhập lại thông tin hợp đồng</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
            background: linear-gradient(135deg, #ffc107, #fd7e14);
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
        .contract-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin: 25px 0;
            border-left: 4px solid #fd7e14;
        }
        .contract-info h3 {
            color: #fd7e14;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .info-row {
            display: flex;
            margin-bottom: 12px;
            align-items: center;
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
        }
        .warning-message {
            background: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .warning-message h4 {
            color: #856404;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .warning-message p {
            color: #856404;
            line-height: 1.5;
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
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
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
        .contact-info {
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
        @media (max-width: 600px) {
            .email-container { margin: 10px; border-radius: 10px; }
            .content { padding: 30px 20px; }
            .header { padding: 25px 20px; }
            .info-row { flex-direction: column; align-items: flex-start; }
            .info-label { min-width: auto; margin-bottom: 5px; }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>⚠️ Yêu cầu nhập lại thông tin hợp đồng</h1>
            <p>Chúng tôi nhận thấy thông tin hợp đồng của bạn chưa đầy đủ hoặc cần được cập nhật lại.</p>
        </div>
        <div class="content">
            <div class="greeting">
                Xin chào <strong>{{ $userName }}</strong>,
            </div>
            <div class="warning-message">
                <h4>🔄 Vui lòng cập nhật lại hợp đồng</h4>
                <p>Thông tin hợp đồng của bạn (Mã hợp đồng: <strong style="color: #fd7e14;">#{{ $contractId }}</strong>) cần được bổ sung hoặc chỉnh sửa. Vui lòng truy cập hệ thống và nhập lại thông tin hợp đồng theo yêu cầu.</p>
            </div>
            <div class="contract-info">
                <h3>📋 Thông tin hợp đồng</h3>
                <div class="info-row">
                    <span class="info-label">🏠 Tên phòng:</span>
                    <span class="info-value">{{ $roomName }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">📅 Ngày bắt đầu:</span>
                    <span class="info-value">{{ $startDate }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">📅 Ngày kết thúc:</span>
                    <span class="info-value">{{ $endDate }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">🕐 Ngày tạo hợp đồng:</span>
                    <span class="info-value">{{ $createdAt }}</span>
                </div>
            </div>
            <p class="message">
                Để hoàn tất quá trình đặt phòng, vui lòng truy cập vào trang quản lý hợp đồng của chúng tôi và nhập lại thông tin hợp đồng cần thiết. Bạn có thể thực hiện điều này bằng cách nhấn vào nút bên dưới:
            </p>
            <div class="cta-section">
                <a href="http://127.0.0.1:3000/quan-ly/hop-dong" class="cta-button" style="color: #ffffff;">
                    <span class="icon">📝</span> Truy cập trang quản lý hợp đồng
                </a>
            </div>
            <p class="message">
                Nếu bạn cần hỗ trợ, vui lòng liên hệ với chúng tôi. Xin cảm ơn!
            </p>
        </div>
        <div class="footer">
            <p><strong>📧 Đội ngũ hỗ trợ khách hàng</strong></p>
            <div class="contact-info">
                <p style="color: #ffffff;">📞 Hotline: 1900 1234 | ✉️ Email: sghood@gmail.com</p>
                <p style="color: #ffffff;">🌐 Website: sghood.com</p>
            </div>
        </div>
    </div>
</body>
</html>
