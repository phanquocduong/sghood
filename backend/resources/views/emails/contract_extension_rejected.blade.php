<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo từ chối gia hạn hợp đồng</title>
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
            background: linear-gradient(135deg, #dc3545, #c82333);
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

        .booking-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin: 25px 0;
            border-left: 4px solid #dc3545;
        }

        .booking-info h3 {
            color: #dc3545;
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

        .rejection-reason {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }

        .rejection-reason h4 {
            color: #856404;
            margin-bottom: 10px;
            font-size: 16px;
        }

        .rejection-reason p {
            color: #856404;
            font-style: italic;
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
            <h1>🚫 Thông báo từ chối gia hạn hợp đồng</h1>
            <p>Chúng tôi rất tiếc phải thông báo về quyết định này</p>
        </div>

        <div class="content">
            <div class="greeting">
                Xin chào <strong>{{ $contractExtension->contract->user->name ?? 'Khách hàng' }}</strong>,
            </div>

            <p class="message">
                Chúng tôi rất tiếc phải thông báo rằng yêu cầu gia hạn hợp đồng của bạn đã bị <strong style="color: #dc3545;">từ chối</strong>.
            </p>

            <div class="booking-info">
                <h3>📋 Thông tin gia hạn hợp đồng</h3>
                <div class="info-row">
                    <span class="info-label">🏠 Tên phòng:</span>
                    <span class="info-value">{{ $contractExtension->contract->room->name ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">📅 Ngày kết thúc mới:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($contractExtension->new_end_date)->format('d/m/Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">💰 Giá thuê mới:</span>
                    <span class="info-value">{{ number_format($contractExtension->new_rental_price, 0, ',', '.') }} VNĐ</span>
                </div>
                <div class="info-row">
                    <span class="info-label">🕐 Ngày tạo yêu cầu:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($contractExtension->created_at)->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            @if($contractExtension->rejection_reason)
            <div class="rejection-reason">
                <h4>💬 Lý do từ chối:</h4>
                <p>{{ $contractExtension->rejection_reason }}</p>
            </div>
            @endif

            <p class="message">
                Chúng tôi hiểu rằng điều này có thể gây ra sự bất tiện cho bạn. Tuy nhiên, bạn vẫn có thể:
            </p>

            <ul style="margin: 20px 0; padding-left: 20px; color: #6c757d;">
                <li style="margin-bottom: 8px;">🔍 Kiểm tra thông tin hợp đồng hiện tại</li>
                <li style="margin-bottom: 8px;">📞 Liên hệ trực tiếp với chúng tôi để được hỗ trợ</li>
                <li style="margin-bottom: 8px;">📝 Gửi yêu cầu gia hạn khác</li>
            </ul>

            <div class="cta-section">
                <a href="{{ url('https://sghood.com.vn/quan-ly/hop-dong') }}" class="cta-button" style="color: #ffffff;">
                    <span class="icon">🏠</span> Xem hợp đồng
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
                <p style="color: #ffffff;">🌐 Website: sghood.com</p>
            </div>
        </div>
    </div>
</body>
</html>
