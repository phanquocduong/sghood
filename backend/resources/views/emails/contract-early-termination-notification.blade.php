<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo kết thúc hợp đồng sớm</title>
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
            background: linear-gradient(135deg, #dc3545, #ff6b6b);
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
            border-left: 4px solid #dc3545;
        }
        .contract-info h3 {
            color: #dc3545;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #495057;
            min-width: 140px;
        }
        .info-value {
            color: #2c3e50;
            flex: 1;
            text-align: right;
        }
        .termination-reason {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .termination-reason h4 {
            color: #856404;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .termination-reason p {
            color: #856404;
            font-style: italic;
        }
        .important-note {
            background: #e7f3ff;
            border: 1px solid #b3d4fc;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .important-note h4 {
            color: #0056b3;
            margin-bottom: 10px;
        }
        .important-note p {
            color: #0056b3;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
            transition: all 0.3s ease;
        }
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }
        .footer {
            background: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #eee;
        }
        .footer p {
            color: #6c757d;
            margin-bottom: 10px;
        }
        .contact-info {
            background: #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        .contact-info h4 {
            color: #495057;
            margin-bottom: 10px;
        }
        .social-links {
            margin-top: 20px;
        }
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #007bff;
            text-decoration: none;
        }
        @media (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 10px;
            }
            .content {
                padding: 25px 20px;
            }
            .header {
                padding: 25px 20px;
            }
            .info-row {
                flex-direction: column;
            }
            .info-value {
                text-align: left;
                margin-top: 5px;
            }
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

            <p>Chúng tôi xin thông báo rằng hợp đồng thuê phòng của bạn đã được kết thúc sớm theo quyết định của ban quản lý.</p>

            <div class="contract-info">
                <h3>📋 Thông tin hợp đồng</h3>
                <div class="info-row">
                    <span class="info-label">Mã hợp đồng:</span>
                    <span class="info-value">HD{{ $contractId }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tên phòng:</span>
                    <span class="info-value">{{ $roomName }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ngày bắt đầu:</span>
                    <span class="info-value">{{ $startDate }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ngày kết thúc dự kiến:</span>
                    <span class="info-value">{{ $endDate }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ngày kết thúc thực tế:</span>
                    <span class="info-value"><strong>{{ $terminationDate }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Giá thuê hàng tháng:</span>
                    <span class="info-value">{{ number_format($rentalPrice, 0, ',', '.') }} VNĐ</span>
                </div>
            </div>

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

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}" class="cta-button">
                    Đăng nhập hệ thống
                </a>
            </div>

            <p style="margin-top: 25px;">
                Chúng tôi rất tiếc vì sự bất tiện này và cảm ơn bạn đã sử dụng dịch vụ của SGHood.
            </p>
        </div>

        <div class="footer">
            <div class="contact-info">
                <h4>📞 Thông tin liên hệ</h4>
                <p><strong>Email:</strong> support@sghood.com</p>
                <p><strong>Hotline:</strong> 1900-xxxx</p>
                <p><strong>Địa chỉ:</strong> 123 Đường ABC, Quận XYZ, TP.HCM</p>
            </div>

            <p>© {{ date('Y') }} SGHood. Tất cả quyền được bảo lưu.</p>
            <p style="font-size: 12px; color: #999;">
                Email này được gửi tự động từ hệ thống. Vui lòng không trả lời email này.
            </p>

            <div class="social-links">
                <a href="#">Facebook</a> |
                <a href="#">Website</a> |
                <a href="#">Hỗ trợ</a>
            </div>
        </div>
    </div>
</body>
</html>
