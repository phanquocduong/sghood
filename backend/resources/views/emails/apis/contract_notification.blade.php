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

        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .email-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
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
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.1); opacity: 0.1; }
        }

        .header-content {
            position: relative;
            z-index: 2;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .header p {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 300;
        }

        .content {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 18px;
            color: #2d3748;
            margin-bottom: 24px;
            font-weight: 600;
        }

        .message {
            font-size: 16px;
            color: #4a5568;
            margin-bottom: 32px;
            line-height: 1.7;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 24px;
        }

        .status-updated {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-signed, .status-deposit_paid {
            background: #d1fae5;
            color: #065f46;
        }

        .status-canceled, .status-early-terminated {
            background: #fecaca;
            color: #dc2626;
        }

        .details-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 24px;
            margin: 24px 0;
            border-left: 4px solid #667eea;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
            flex-wrap: wrap;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #2d3748;
            min-width: 120px;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .detail-value {
            font-weight: 500;
            color: #4a5568;
            text-align: left;
            flex: 1;
            margin-left: 16px;
            word-wrap: break-word;
            word-break: break-word;
        }

        .highlight {
            color: #667eea;
            font-weight: 600;
        }

        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            min-width: 200px;
            width: auto;
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .button-container {
            text-align: center;
            margin: 32px 0;
        }

        .footer {
            background: #f8fafc;
            padding: 24px 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }

        .footer p {
            font-size: 14px;
            color: #718096;
            margin-bottom: 8px;
        }

        .footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
            margin: 24px 0;
        }

        @media only screen and (max-width: 768px) {
            body { padding: 15px; }
            .email-wrapper { border-radius: 12px; max-width: 100%; }
            .header { padding: 30px 20px; }
            .header h1 { font-size: 24px; }
            .header p { font-size: 14px; }
            .content { padding: 30px 20px; }
            .greeting { font-size: 16px; }
            .message { font-size: 15px; }
            .details-card { padding: 20px; margin: 20px 0; }
            .detail-row { flex-direction: column; align-items: flex-start; padding: 10px 0; }
            .detail-label { margin-bottom: 6px; min-width: auto; font-size: 13px; }
            .detail-value { text-align: left; margin-left: 0; font-size: 14px; }
            .action-button { padding: 14px 24px; font-size: 15px; min-width: 180px; }
            .footer { padding: 20px 15px; }
            .footer p { font-size: 13px; }
        }

        @media only screen and (max-width: 480px) {
            body { padding: 10px; }
            .email-wrapper { border-radius: 8px; }
            .header { padding: 24px 15px; }
            .header h1 { font-size: 22px; line-height: 1.2; }
            .header p { font-size: 13px; }
            .content { padding: 24px 15px; }
            .greeting { font-size: 15px; margin-bottom: 20px; }
            .message { font-size: 14px; margin-bottom: 24px; }
            .status-badge { padding: 6px 12px; font-size: 11px; }
            .details-card { padding: 16px; margin: 16px 0; border-radius: 8px; }
            .detail-row { padding: 8px 0; }
            .detail-label { font-size: 12px; margin-bottom: 4px; }
            .detail-value { font-size: 13px; }
            .action-button { padding: 12px 20px; font-size: 14px; min-width: 160px; border-radius: 25px; }
            .footer { padding: 16px 12px; }
            .footer p { font-size: 12px; }
        }

        @media only screen and (max-width: 360px) {
            .header h1 { font-size: 20px; }
            .header p { font-size: 12px; }
            .content { padding: 20px 12px; }
            .greeting { font-size: 14px; }
            .message { font-size: 13px; }
            .details-card { padding: 14px; }
            .detail-label { font-size: 11px; }
            .detail-value { font-size: 12px; }
            .action-button { padding: 10px 16px; font-size: 13px; min-width: 140px; }
        }

        @media only screen and (min-width: 768px) and (max-width: 1024px) {
            .email-wrapper { max-width: 90%; }
            .header { padding: 35px 25px; }
            .content { padding: 35px 25px; }
            .footer { padding: 22px 25px; }
        }

        img { max-width: 100%; height: auto; }
        * { overflow-wrap: break-word; }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header">
            <div class="header-content">
                <h1>{{ $title }}</h1>
                <p>Hệ thống Quản lý Hợp đồng</p>
            </div>
        </div>
        <div class="content">
            <div class="greeting">
                Kính chào Quản trị viên,
            </div>

            <div class="message">
                @if ($type == 'pending')
                    <span class="status-badge status-pending">Chờ duyệt</span><br>
                    Một hợp đồng mới vừa được gửi và đang chờ sự duyệt từ bạn. Vui lòng kiểm tra và xử lý trong thời gian sớm nhất.
                @elseif ($type == 'signed')
                    <span class="status-badge status-signed">Đã ký</span><br>
                    Hợp đồng vừa được ký bởi người dùng và đang chờ thanh toán tiền cọc.
                @elseif ($type == 'canceled')
                    <span class="status-badge status-canceled">Đã hủy</span><br>
                    Hợp đồng vừa bị người dùng hủy trong hệ thống.
                @elseif ($type == 'deposit_paid')
                    <span class="status-badge status-deposit_paid">Đã thanh toán tiền cọc</span><br>
                    Hợp đồng đã được thanh toán tiền cọc và đang chờ kích hoạt. Vui lòng kiểm tra và xử lý trong hệ thống.
                @elseif ($type == 'early_terminated')
                    <span class="status-badge status-early-terminated">Kết thúc sớm</span><br>
                    Hợp đồng đã được người dùng kết thúc sớm. Vui lòng kiểm tra trong hệ thống.
                @else
                    <span class="status-badge status-updated">Thông báo</span><br>
                    Có một thông báo quan trọng liên quan đến hợp đồng cần sự chú ý của bạn.
                @endif
            </div>

            <div class="details-card">
                <div class="detail-row">
                    <div class="detail-label">Mã hợp đồng:</div>
                    <div class="detail-value highlight">#{{ $contract->id }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Người dùng:</div>
                    <div class="detail-value">{{ $contract->user->name }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Email:</div>
                    <div class="detail-value">{{ $contract->user->email }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Phòng:</div>
                    <div class="detail-value highlight">{{ $contract->room->name }} ({{ $contract->room->motel->name }})</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Địa chỉ:</div>
                    <div class="detail-value">{{ $contract->room->motel->address }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Giá thuê:</div>
                    <div class="detail-value">{{ number_format($contract->rental_price, 0, ',', '.') }} VNĐ</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Tiền cọc:</div>
                    <div class="detail-value">{{ number_format($contract->deposit_amount, 0, ',', '.') }} VNĐ</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Ngày bắt đầu:</div>
                    <div class="detail-value">{{ $contract->start_date->format('d/m/Y') }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Ngày kết thúc:</div>
                    <div class="detail-value">{{ $contract->end_date->format('d/m/Y') }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Trạng thái:</div>
                    <div class="detail-value">{{ $contract->status }}</div>
                </div>
                @if($contract->signed_at)
                <div class="detail-row">
                    <div class="detail-label">Đã ký lúc:</div>
                    <div class="detail-value">{{ $contract->signed_at->format('d/m/Y H:i') }}</div>
                </div>
                @endif
            </div>

            <div class="button-container">
                <a href="{{ url('/contracts/' . $contract->id) }}" class="action-button">
                    🔍 Xem Chi Tiết & Xử Lý
                </a>
            </div>
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} Hệ thống Quản lý Hợp đồng</p>
            <p>
                <a href="{{ url('/') }}">Truy cập Dashboard</a>
            </p>
        </div>
    </div>
</body>
</html>
