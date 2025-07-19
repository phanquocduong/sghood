<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            line-height: 1.6;
            padding: 20px;
            min-height: 100vh;
        }

        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            position: relative;
        }

        .email-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb, #f5576c);
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 45px 35px;
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
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 6s ease-in-out infinite;
        }

        @keyframes shimmer {
            0%, 100% { transform: scale(1) rotate(0deg); opacity: 0.3; }
            50% { transform: scale(1.1) rotate(180deg); opacity: 0.1; }
        }

        .header-content {
            position: relative;
            z-index: 2;
        }

        .header h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .header p {
            font-size: 18px;
            opacity: 0.9;
            font-weight: 300;
        }

        .header .icon {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
        }

        .content {
            padding: 45px 35px;
        }

        .greeting {
            font-size: 20px;
            color: #2d3748;
            margin-bottom: 25px;
            font-weight: 600;
        }

        .message {
            font-size: 17px;
            color: #4a5568;
            margin-bottom: 35px;
            line-height: 1.8;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 25px;
        }

        .status-pending {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: #92400e;
            box-shadow: 0 4px 15px rgba(251, 191, 36, 0.3);
        }

        .status-canceled {
            background: linear-gradient(135deg, #f87171 0%, #ef4444 100%);
            color: #991b1b;
            box-shadow: 0 4px 15px rgba(248, 113, 113, 0.3);
        }

        .status-approved {
            background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
            color: #065f46;
            box-shadow: 0 4px 15px rgba(52, 211, 153, 0.3);
        }

        .booking-card {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            border-left: 6px solid #667eea;
            position: relative;
        }

        .booking-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .booking-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }

        .booking-id {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
        }

        .booking-type {
            background: #667eea;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            line-height: 24px;
            margin-left: 10px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .detail-item {
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .detail-item:hover {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .detail-label {
            font-weight: 600;
            color: #4a5568;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .detail-value {
            font-weight: 600;
            color: #2d3748;
            font-size: 16px;
        }

        .highlight {
            color: #667eea;
        }

        .room-info {
            background: white;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin: 20px 0;
        }

        .room-name {
            font-size: 20px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 5px;
        }

        .motel-name {
            font-size: 16px;
            color: #667eea;
            font-weight: 600;
        }

        .date-range {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin: 20px 0;
        }

        .date-item {
            text-align: center;
            flex: 1;
        }

        .date-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .date-value {
            font-size: 18px;
            font-weight: 700;
            color: #2d3748;
        }

        .date-arrow {
            font-size: 24px;
            color: #667eea;
            margin: 0 15px;
        }

        .note-section {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .note-label {
            font-weight: 600;
            color: #92400e;
            margin-bottom: 5px;
        }

        .note-content {
            color: #78350f;
            font-style: italic;
        }

        .action-section {
            text-align: center;
            margin: 40px 0;
        }

        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            text-decoration: none;
            padding: 18px 35px;
            border-radius: 35px;
            font-weight: 700;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .action-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
        }

        .divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
            margin: 35px 0;
        }

        .footer {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 30px 35px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }

        .footer p {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 10px;
        }

        .footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: #4c51bf;
            text-decoration: underline;
        }

        /* Responsive Design */
        @media only screen and (max-width: 768px) {
            body {
                padding: 10px;
            }

            .email-container {
                border-radius: 10px;
                margin: 10px auto;
            }

            .header, .content, .footer {
                padding: 25px 20px;
            }

            .header h1 {
                font-size: 26px;
            }

            .header p {
                font-size: 16px;
            }

            .header .icon {
                font-size: 36px;
                margin-bottom: 10px;
            }

            .greeting {
                font-size: 18px;
                margin-bottom: 20px;
            }

            .message {
                font-size: 16px;
                margin-bottom: 30px;
            }

            .booking-card {
                padding: 20px;
                margin: 20px 0;
            }

            .booking-header {
                gap: 15px;
                margin-bottom: 20px;
            }

            .booking-id {
                font-size: 20px;
            }

            .detail-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .detail-item {
                padding: 15px;
            }

            .room-info {
                padding: 20px;
                margin: 15px 0;
            }

            .room-name {
                font-size: 18px;
            }

            .motel-name {
                font-size: 15px;
            }

            .date-range {
                flex-direction: column;
                gap: 15px;
                padding: 20px;
            }

            .date-arrow {
                transform: rotate(90deg);
                font-size: 20px;
            }

            .date-value {
                font-size: 16px;
            }

            .note-section {
                padding: 15px;
                margin: 15px 0;
            }

            .action-button {
                padding: 16px 25px;
                font-size: 14px;
                border-radius: 30px;
            }
        }

        @media only screen and (max-width: 480px) {
            body {
                padding: 5px;
            }

            .email-container {
                border-radius: 8px;
                margin: 5px auto;
            }

            .header, .content, .footer {
                padding: 20px 15px;
            }

            .header h1 {
                font-size: 22px;
                line-height: 1.2;
            }

            .header p {
                font-size: 14px;
            }

            .header .icon {
                font-size: 32px;
            }

            .greeting {
                font-size: 16px;
                margin-bottom: 15px;
            }

            .message {
                font-size: 15px;
                margin-bottom: 25px;
                line-height: 1.6;
            }

            .status-badge {
                padding: 6px 12px;
                font-size: 11px;
                margin-bottom: 20px;
            }

            .booking-card {
                padding: 15px;
                margin: 15px 0;
                border-radius: 12px;
            }

            .booking-header {
                margin-bottom: 15px;
            }

            .booking-id {
                font-size: 18px;
            }

            .booking-type {
                font-size: 11px;
                padding: 4px 8px;
            }

            .detail-item {
                padding: 12px;
            }

            .detail-label {
                font-size: 12px;
                margin-bottom: 4px;
            }

            .detail-value {
                font-size: 14px;
            }

            .room-info {
                padding: 15px;
                margin: 12px 0;
            }

            .room-name {
                font-size: 16px;
                margin-bottom: 3px;
            }

            .motel-name {
                font-size: 14px;
            }

            .date-range {
                padding: 15px;
                margin: 12px 0;
            }

            .date-label {
                font-size: 11px;
            }

            .date-value {
                font-size: 15px;
            }

            .date-arrow {
                font-size: 18px;
                margin: 8px 0;
            }

            .note-section {
                padding: 12px;
                margin: 12px 0;
            }

            .note-label {
                font-size: 13px;
                margin-bottom: 3px;
            }

            .note-content {
                font-size: 13px;
            }

            .action-section {
                margin: 30px 0;
            }

            .action-button {
                padding: 14px 20px;
                font-size: 13px;
                border-radius: 25px;
                width: 100%;
                max-width: 280px;
            }

            .footer p {
                font-size: 12px;
                line-height: 1.5;
            }
        }

        @media only screen and (max-width: 360px) {
            .header, .content, .footer {
                padding: 15px 10px;
            }

            .header h1 {
                font-size: 20px;
            }

            .header p {
                font-size: 13px;
            }

            .booking-card {
                padding: 12px;
                margin: 12px 0;
            }

            .detail-item {
                padding: 10px;
            }

            .room-info, .date-range, .note-section {
                padding: 12px;
            }

            .action-button {
                padding: 12px 16px;
                font-size: 12px;
            }
        }

        /* Print styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .email-container {
                box-shadow: none;
                border: 1px solid #e2e8f0;
            }

            .action-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="header-content">
                <span class="icon">🏨</span>
                <h1>{{ $title }}</h1>
                <p>Hệ thống Quản lý Đặt phòng Nhà trọ</p>
            </div>
        </div>

        <div class="content">
            <div class="greeting">
                Kính chào Quản trị viên,
            </div>

            <div class="message">
                @if ($type == 'pending')
                    <span class="status-badge status-pending">🕐 Chờ duyệt</span><br>
                    Có một đặt phòng mới vừa được tạo và đang chờ sự duyệt của bạn. Vui lòng kiểm tra thông tin chi tiết bên dưới và xử lý trong thời gian sớm nhất.
                @elseif ($type == 'canceled')
                    <span class="status-badge status-canceled">❌ Đã hủy</span><br>
                    Một đặt phòng vừa bị hủy trong hệ thống. Vui lòng kiểm tra thông tin và lý do hủy để có biện pháp xử lý phù hợp.
                @elseif ($type == 'approved')
                    <span class="status-badge status-approved">✅ Đã duyệt</span><br>
                    Đặt phòng đã được duyệt thành công. Khách hàng có thể bắt đầu sử dụng phòng theo thời gian đã đăng ký.
                @else
                    <span class="status-badge status-pending">📢 Thông báo</span><br>
                    Có một thông báo quan trọng liên quan đến đặt phòng cần sự chú ý của bạn.
                @endif
            </div>

            <div class="booking-card">
                <div class="booking-header">
                    <div class="booking-id">#{{ $booking->id }}</div>
                    <div class="booking-type">Đặt phòng</div>
                </div>

                <div class="room-info">
                    <div class="room-name">🏠 {{ $booking->room->name }}</div>
                    <div class="motel-name">📍 {{ $booking->room->motel->name }}</div>
                </div>

                <div class="date-range">
                    <div class="date-item">
                        <div class="date-label">Ngày nhận phòng</div>
                        <div class="date-value">{{ $booking->start_date->format('d/m/Y') }}</div>
                    </div>
                    <div class="date-arrow">→</div>
                    <div class="date-item">
                        <div class="date-label">Ngày trả phòng</div>
                        <div class="date-value">{{ $booking->end_date->format('d/m/Y') }}</div>
                    </div>
                </div>

                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Khách hàng</div>
                        <div class="detail-value highlight">{{ $booking->user->name }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Email</div>
                        <div class="detail-value">{{ $booking->user->email ?? 'Không có' }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Số điện thoại</div>
                        <div class="detail-value">{{ $booking->user->phone ?? 'Không có' }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Trạng thái</div>
                        <div class="detail-value">
                            @if($booking->status == 'pending')
                                <span style="color: #f59e0b;">🕐 Chờ duyệt</span>
                            @elseif($booking->status == 'approved')
                                <span style="color: #10b981;">✅ Đã duyệt</span>
                            @elseif($booking->status == 'canceled')
                                <span style="color: #ef4444;">❌ Đã hủy</span>
                            @else
                                {{ $booking->status }}
                            @endif
                        </div>
                    </div>
                </div>

                @if (!empty($booking->note))
                <div class="note-section">
                    <div class="note-label">💬 Ghi chú từ khách hàng:</div>
                    <div class="note-content">"{{ $booking->note }}"</div>
                </div>
                @endif

                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Ngày tạo</div>
                        <div class="detail-value">{{ $booking->created_at->format('d/m/Y H:i') }}</div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label">Cập nhật lần cuối</div>
                        <div class="detail-value">{{ $booking->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            <div class="action-section">
                <a href="{{ url('/bookings') }}" class="action-button">
                    🔍 Xem chi tiết & xử lý
                </a>
            </div>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} Hệ thống Quản lý Đặt phòng Nhà trọ</p>
        </div>
    </div>
</body>
</html>
