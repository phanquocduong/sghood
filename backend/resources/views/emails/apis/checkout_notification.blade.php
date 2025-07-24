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

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-confirm {
            background: #d1fae5;
            color: #059669;
        }

        .status-reject {
            background: #fecaca;
            color: #dc2626;
        }

        .status-canceled {
            background: #fecaca;
            color: #dc2626;
        }

        .status-left-room {
            background: #bfdbfe;
            color: #1e40af;
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

        /* Responsive Design cho màn hình nhỏ hơn 768px */
        @media only screen and (max-width: 768px) {
            body {
                padding: 15px;
            }

            .email-wrapper {
                border-radius: 12px;
                max-width: 100%;
            }

            .header {
                padding: 30px 20px;
            }

            .header h1 {
                font-size: 24px;
            }

            .header p {
                font-size: 14px;
            }

            .content {
                padding: 30px 20px;
            }

            .greeting {
                font-size: 16px;
            }

            .message {
                font-size: 15px;
            }

            .details-card {
                padding: 20px;
                margin: 20px 0;
            }

            .detail-row {
                flex-direction: column;
                align-items: flex-start;
                padding: 10px 0;
            }

            .detail-label {
                margin-bottom: 6px;
                min-width: auto;
                font-size: 13px;
            }

            .detail-value {
                text-align: left;
                margin-left: 0;
                font-size: 14px;
            }

            .action-button {
                padding: 14px 24px;
                font-size: 15px;
                min-width: 180px;
            }

            .footer {
                padding: 20px 15px;
            }

            .footer p {
                font-size: 13px;
            }
        }

        /* Responsive Design cho màn hình rất nhỏ (< 480px) */
        @media only screen and (max-width: 480px) {
            body {
                padding: 10px;
            }

            .email-wrapper {
                border-radius: 8px;
            }

            .header {
                padding: 24px 15px;
            }

            .header h1 {
                font-size: 22px;
                line-height: 1.2;
            }

            .header p {
                font-size: 13px;
            }

            .content {
                padding: 24px 15px;
            }

            .greeting {
                font-size: 15px;
                margin-bottom: 20px;
            }

            .message {
                font-size: 14px;
                margin-bottom: 24px;
            }

            .status-badge {
                padding: 6px 12px;
                font-size: 11px;
            }

            .details-card {
                padding: 16px;
                margin: 16px 0;
                border-radius: 8px;
            }

            .detail-row {
                padding: 8px 0;
            }

            .detail-label {
                font-size: 12px;
                margin-bottom: 4px;
            }

            .detail-value {
                font-size: 13px;
            }

            .action-button {
                padding: 12px 20px;
                font-size: 14px;
                min-width: 160px;
                border-radius: 25px;
            }

            .footer {
                padding: 16px 12px;
            }

            .footer p {
                font-size: 12px;
            }
        }

        /* Responsive Design cho màn hình cực nhỏ (< 360px) */
        @media only screen and (max-width: 360px) {
            .header h1 {
                font-size: 20px;
            }

            .header p {
                font-size: 12px;
            }

            .content {
                padding: 20px 12px;
            }

            .greeting {
                font-size: 14px;
            }

            .message {
                font-size: 13px;
            }

            .details-card {
                padding: 14px;
            }

            .detail-label {
                font-size: 11px;
            }

            .detail-value {
                font-size: 12px;
            }

            .action-button {
                padding: 10px 16px;
                font-size: 13px;
                min-width: 140px;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .details-card {
                background: #1a202c;
                border-left-color: #667eea;
            }

            .detail-label {
                color: #e2e8f0;
            }

            .detail-value {
                color: #cbd5e0;
            }
        }

        /* Responsive cho tablet (768px - 1024px) */
        @media only screen and (min-width: 768px) and (max-width: 1024px) {
            .email-wrapper {
                max-width: 90%;
            }

            .header {
                padding: 35px 25px;
            }

            .content {
                padding: 35px 25px;
            }

            .footer {
                padding: 22px 25px;
            }
        }

        /* Ensure images are responsive if any */
        img {
            max-width: 100%;
            height: auto;
        }

        /* Prevent horizontal scrolling */
        * {
            overflow-wrap: break-word;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="header">
            <div class="header-content">
                <h1>{{ $title }}</h1>
                <p>Hệ thống Quản lý Trả phòng Nhà Trọ</p>
            </div>
        </div>

        <div class="content">
            <div class="greeting">
                Kính chào Quản trị viên,
            </div>

            <div class="message">
                @if ($type == 'pending')
                    <span class="status-badge status-pending">Chờ xử lý</span><br>
                    Một yêu cầu trả phòng mới vừa được tạo và đang chờ sự xử lý từ bạn.
                @elseif ($type == 'confirm')
                    <span class="status-badge status-confirm">Đã đồng ý</span><br>
                    Kết quả kiểm kê trả phòng vừa được người dùng xác nhận đồng ý.
                @elseif ($type == 'reject')
                    <span class="status-badge status-reject">Đã từ chối</span><br>
                    Kết quả kiểm kê trả phòng vừa bị người dùng từ chối.
                @elseif ($type == 'canceled')
                    <span class="status-badge status-canceled">Đã hủy</span><br>
                    Một yêu cầu trả phòng vừa bị người dùng hủy.
                @elseif ($type == 'left-room')
                    <span class="status-badge status-left-room">Đã rời phòng</span><br>
                    Người dùng vừa xác nhận đã rời phòng.
                @elseif ($type == 'update-bank')
                    <span class="status-badge status-pending">Cập nhật ngân hàng</span><br>
                    Thông tin chuyển khoản của yêu cầu trả phòng vừa được cập nhật.
                @elseif ($type == 'refund')
                    <span class="status-badge status-confirm">Đã hoàn tiền</span><br>
                    Yêu cầu hoàn tiền vừa được xác nhận.
                @else
                    <span class="status-badge status-pending">Thông báo</span><br>
                    Có một thông báo quan trọng liên quan đến yêu cầu trả phòng.
                @endif
            </div>

            <div class="details-card">
                <div class="detail-row">
                    <div class="detail-label">Mã trả phòng:</div>
                    <div class="detail-value highlight">#{{ $checkout->id }}</div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Mã hợp đồng:</div>
                    <div class="detail-value highlight">#{{ $checkout->contract_id }}</div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Người dùng:</div>
                    <div class="detail-value">{{ $checkout->contract->user->name }}</div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Email:</div>
                    <div class="detail-value">{{ $checkout->contract->user->email }}</div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Nhà trọ:</div>
                    <div class="detail-value highlight">{{ $checkout->contract->room->motel->name }}</div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Phòng:</div>
                    <div class="detail-value">{{ $checkout->contract->room->name }}</div>
                </div>

                <div class="detail-row">
                    <div class="detail-label">Ngày trả phòng:</div>
                    <div class="detail-value highlight">
                        {{ $checkout->check_out_date->format('d/m/Y') }}
                    </div>
                </div>

                @if ($checkout->refund_request)
                <div class="detail-row">
                    <div class="detail-label">Số tiền cọc:</div>
                    <div class="detail-value">
                        {{ number_format($checkout->refund_request->deposit_amount, 0, ',', '.') }} VND
                    </div>
                </div>
                @endif

                @if ($checkout->deduction_amount)
                <div class="detail-row">
                    <div class="detail-label">Số tiền khấu trừ:</div>
                    <div class="detail-value">
                        {{ number_format($checkout->deduction_amount, 0, ',', '.') }} VND
                    </div>
                </div>
                @endif

                @if ($checkout->final_refunded_amount)
                <div class="detail-row">
                    <div class="detail-label">Số tiền hoàn cuối cùng:</div>
                    <div class="detail-value">
                        {{ number_format($checkout->final_refunded_amount, 0, ',', '.') }} VND
                    </div>
                </div>
                @endif

                <div class="detail-row">
                    <div class="detail-label">Trạng thái kiểm kê:</div>
                    <div class="detail-value">
                        @if($checkout->inventory_status == 'Chờ xử lý')
                            <span style="color: #92400e;">Chờ xử lý</span>
                        @elseif($checkout->inventory_status == 'Đã duyệt' || $checkout->inventory_status == 'Hoàn thành')
                            <span style="color: #059669;">{{ $checkout->inventory_status }}</span>
                        @elseif($checkout->inventory_status == 'Huỷ bỏ')
                            <span style="color: #dc2626;">Huỷ bỏ</span>
                        @else
                            {{ $checkout->inventory_status ?? 'Chưa xác định' }}
                        @endif
                    </div>
                </div>

                @if ($checkout->user_confirmation_status)
                <div class="detail-row">
                    <div class="detail-label">Trạng thái xác nhận:</div>
                    <div class="detail-value">
                        @if($checkout->user_confirmation_status == 'Đồng ý')
                            <span style="color: #059669;">Đồng ý</span>
                        @elseif($checkout->user_confirmation_status == 'Từ chối')
                            <span style="color: #dc2626;">Từ chối</span>
                        @else
                            {{ $checkout->user_confirmation_status }}
                        @endif
                    </div>
                </div>
                @endif

                @if ($checkout->user_rejection_reason)
                <div class="detail-row">
                    <div class="detail-label">Lý do từ chối:</div>
                    <div class="detail-value">"{{ $checkout->user_rejection_reason }}"</div>
                </div>
                @endif

                @if ($checkout->has_left)
                <div class="detail-row">
                    <div class="detail-label">Trạng thái rời phòng:</div>
                    <div class="detail-value">
                        <span style="color: #1e40af;">Đã rời</span>
                    </div>
                </div>
                @endif

                @if ($checkout->note)
                <div class="detail-row">
                    <div class="detail-label">Ghi chú:</div>
                    <div class="detail-value">"{{ $checkout->note }}"</div>
                </div>
                @endif

                @if ($checkout->bank_info)
                <div class="detail-row">
                    <div class="detail-label">Thông tin ngân hàng:</div>
                    <div class="detail-value">
                        {{ $checkout->bank_info['bank_name'] ?? '' }}<br>
                        Số tài khoản: {{ $checkout->bank_info['account_number'] ?? '' }}<br>
                        Chủ tài khoản: {{ $checkout->bank_info['account_holder'] ?? '' }}
                    </div>
                </div>
                @endif

                @if ($checkout->refund_status)
                <div class="detail-row">
                    <div class="detail-label">Trạng thái hoàn tiền:</div>
                    <div class="detail-value">
                        @if($checkout->refund_status == 'Chờ xử lý')
                            <span style="color: #92400e;">Chờ xử lý</span>
                        @elseif($checkout->refund_status == 'Đã xử lý')
                            <span style="color: #059669;">Đã xử lý</span>
                        @elseif($checkout->refund_status == 'Huỷ bỏ')
                            <span style="color: #dc2626;">Huỷ bỏ</span>
                        @endif
                    </div>
                </div>
                @endif

                @if ($checkout->receipt_path)
                <div class="detail-row">
                    <div class="detail-label">Biên lai hoàn tiền:</div>
                    <div class="detail-value">
                        <a href="{{ url('/storage/' . $checkout->receipt_path) }}" target="_blank">Xem biên lai</a>
                    </div>
                </div>
                @endif

                <div class="detail-row">
                    <div class="detail-label">Cập nhật lần cuối:</div>
                    <div class="detail-value">
                        {{ $checkout->updated_at->format('d/m/Y H:i:s') }}
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            <div class="button-container">
                <a href="{{ url('/checkouts') }}" class="action-button">
                    🔍 Xem Chi Tiết & Xử Lý
                </a>
            </div>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} Hệ thống Quản lý Trả phòng Nhà Trọ</p>
            <p>
                <a href="{{ url('/') }}">Truy cập Dashboard</a>
            </p>
        </div>
    </div>
</body>
</html>
