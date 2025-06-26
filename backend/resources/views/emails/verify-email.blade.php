<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác minh Email</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f91942 0%, #b3122f 100%);
            padding: 20px;
            min-height: 100vh;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }

        .header {
            background: linear-gradient(135deg, #f91942 0%, #ff4d6a 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 30px 30px;
            animation: float 20s infinite linear;
        }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(-30px, -30px) rotate(360deg); }
        }

        .logo {
            font-size: 2.5em;
            color: white;
            margin-bottom: 10px;
            position: relative;
            z-index: 2;
        }

        .header-title {
            color: white;
            font-size: 1.4em;
            font-weight: bold;
            position: relative;
            z-index: 2;
        }

        .content {
            padding: 50px 40px;
            text-align: center;
        }

        .welcome-message {
            font-size: 1.8em;
            color: #333;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .user-name {
            color: #f91942;
            font-weight: 700;
        }

        .description {
            font-size: 1.1em;
            color: #666;
            line-height: 1.6;
            margin-bottom: 40px;
        }

        .verify-button {
            display: inline-block;
            background: linear-gradient(135deg, #f91942 0%, #ff4d6a 100%);
            color: white !important;
            text-decoration: none;
            padding: 18px 40px;
            border-radius: 50px;
            font-size: 1.1em;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 8px 20px rgba(249, 25, 66, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .verify-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .verify-button:hover::before {
            left: 100%;
        }

        .verify-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(249, 25, 66, 0.4);
        }

        .security-info {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 15px;
            margin: 40px 0;
            border-left: 4px solid #f91942;
        }

        .security-title {
            font-size: 1.1em;
            color: #333;
            margin-bottom: 10px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .security-title::before {
            content: '🔒';
            margin-right: 8px;
            font-size: 1.2em;
        }

        .security-text {
            color: #666;
            font-size: 0.95em;
            line-height: 1.5;
        }

        .manual-link {
            background: #f1f3f4;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            word-break: break-all;
            font-size: 0.9em;
            color: #666;
        }

        .footer {
            background: #2c3e50;
            color: white;
            padding: 30px;
            text-align: center;
        }

        .footer-text {
            margin-bottom: 15px;
            font-size: 0.95em;
            opacity: 0.9;
        }

        .company-name {
            font-size: 1.1em;
            font-weight: 600;
            color: #f91942;
        }

        .social-links {
            margin-top: 20px;
        }

        .social-link {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: #34495e;
            border-radius: 50%;
            margin: 0 5px;
            line-height: 40px;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: #f91942;
            transform: translateY(-2px);
        }

        @media (max-width: 600px) {
            body {
                padding: 10px;
            }

            .content {
                padding: 30px 20px;
            }

            .header {
                padding: 30px 20px;
            }

            .welcome-message {
                font-size: 1.5em;
            }

            .verify-button {
                padding: 15px 30px;
                font-size: 1em;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="logo">🎉</div>
            <div class="header-title">Chào mừng bạn đến với {{ $appName }}!</div>
        </div>

        <!-- Content -->
        <div class="content">
            <h1 class="welcome-message">
                Xin chào, <span class="user-name">{{ $user->name }}</span>!
            </h1>

            <p class="description">
                Cảm ơn bạn đã đăng ký tài khoản tại <strong>{{ $appName }}</strong>.
                Để hoàn tất quá trình đăng ký và bảo mật tài khoản của bạn,
                vui lòng xác minh địa chỉ email bằng cách nhấn vào nút bên dưới.
            </p>

            <a href="{{ $verificationUrl }}" class="verify-button">
                ✉️ Xác Minh Email Ngay
            </a>

            <div class="security-info">
                <div class="security-title">Bảo mật và An toàn</div>
                <div class="security-text">
                    Liên kết xác minh này sẽ hết hạn sau 60 phút. Nếu bạn không thể nhấn vào nút trên,
                    vui lòng sao chép và dán đường link dưới đây vào trình duyệt của bạn:
                </div>
            </div>

            <div class="manual-link">
                {{ $verificationUrl }}
            </div>

            <p class="description" style="margin-top: 30px; font-size: 0.95em; color: #888;">
                <strong>Lưu ý:</strong> Nếu bạn không tạo tài khoản này, vui lòng bỏ qua email này.
                Tài khoản sẽ không được kích hoạt nếu không xác minh email.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-text">
                Email này được gửi từ <span class="company-name">{{ $appName }}</span>
            </div>
            <div class="footer-text">
                © {{ date('Y') }} {{ $appName }}. Tất cả quyền được bảo lưu.
            </div>

            <div class="social-links">
                <a href="#" class="social-link">📧</a>
                <a href="#" class="social-link">🌐</a>
                <a href="#" class="social-link">📱</a>
            </div>
        </div>
    </div>
</body>
</html>
