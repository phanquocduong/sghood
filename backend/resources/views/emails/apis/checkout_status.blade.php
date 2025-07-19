<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật trạng thái yêu cầu trả phòng</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }

        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }

        .email-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1, #96ceb4);
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .header h1 {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
        }

        .header .subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            font-weight: 300;
            position: relative;
            z-index: 1;
        }

        .content {
            padding: 40px 30px;
        }

        .status-card {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            color: white;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .status-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="20" cy="20" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="40" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="80" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .status-card h2 {
            font-size: 22px;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
        }

        .status-card p {
            font-size: 16px;
            line-height: 1.6;
            position: relative;
            z-index: 1;
        }

        .status-highlight {
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
            margin: 10px 0;
        }

        .details-section {
            margin-top: 30px;
        }

        .details-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .details-header h3 {
            font-size: 20px;
            color: #333;
            margin-left: 10px;
        }

        .details-header::before {
            content: '📋';
            font-size: 24px;
        }

        .details-grid {
            display: grid;
            gap: 15px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            padding: 18px;
            background: #f8f9fa;
            border-radius: 12px;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }

        .detail-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        .detail-item .label {
            font-weight: 600;
            color: #555;
            min-width: 180px;
            font-size: 14px;
            margin-right: 5px;
        }

        .detail-item .value {
            color: #333;
            font-weight: 500;
            font-size: 14px;
            flex: 1;
        }

        .money-highlight {
            color: #27ae60;
            font-weight: 700;
            font-size: 16px;
        }

        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white !important;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            font-size: 16px;
            margin: 30px 0;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .footer {
            background: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }

        .footer p {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
        }

        .footer .brand {
            font-weight: 700;
            color: #667eea;
            font-size: 16px;
        }

        @media (max-width: 600px) {
            body {
                padding: 10px;
            }

            .email-container {
                border-radius: 15px;
            }

            .header {
                padding: 30px 20px;
            }

            .header h1 {
                font-size: 24px;
            }

            .content {
                padding: 30px 20px;
            }

            .status-card {
                padding: 20px;
            }

            .detail-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .detail-item .label {
                min-width: auto;
                margin-bottom: 5px;
            }

            .action-button {
                display: block;
                text-align: center;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🏠 Cập nhật Trạng thái Trả phòng</h1>
            <p class="subtitle">Thông báo từ hệ thống quản lý nhà trọ</p>
        </div>

        <div class="content">
            <div class="status-card">
                <h2>📢 Thông báo Trạng thái</h2>
                <p>Kính gửi <strong>Quản trị viên</strong>,</p>
                <p>
                    Yêu cầu trả phòng <strong>#{{ $checkout->id }}</strong> đã được cập nhật với trạng thái:
                    <span class="status-highlight">
                        @switch($action)
                            @case('confirm')
                                Người dùng đã đồng ý với kết quả kiểm kê
                                @break
                            @case('reject')
                                Người dùng đã từ chối kết quả kiểm kê
                                @break
                            @case('cancel')
                                Người dùng hủy yêu cầu trả phòng
                                @break
                            @default
                                Chưa xác định
                        @endswitch
                    </span>
                </p>
                @if($action === 'reject' && $checkout->user_rejection_reason)
                    <p><strong>Lý do từ chối:</strong> {{ $checkout->user_rejection_reason }}</p>
                @endif
            </div>

            <div class="details-section">
                <div class="details-header">
                    <h3>Thông tin Chi tiết</h3>
                </div>

                <div class="details-grid">
                    <div class="detail-item">
                        <span class="label">📄 Hợp đồng:</span>
                        <span class="value">#{{ $checkout->contract_id }}</span>
                    </div>

                    <div class="detail-item">
                        <span class="label">🏠 Phòng:</span>
                        <span class="value">{{ $checkout->contract->room->name }}</span>
                    </div>

                    <div class="detail-item">
                        <span class="label">🏢 Nhà trọ:</span>
                        <span class="value">{{ $checkout->contract->room->motel->name }}</span>
                    </div>

                    <div class="detail-item">
                        <span class="label">👤 Người thuê:</span>
                        <span class="value">{{ $checkout->contract->user->name }}</span>
                    </div>

                    <div class="detail-item">
                        <span class="label">📅 Ngày dự kiến rời phòng:</span>
                        <span class="value">{{ \Carbon\Carbon::parse($checkout->check_out_date)->format('d/m/Y') }}</span>
                    </div>

                    <div class="detail-item">
                        <span class="label">📋 Trạng thái kiểm kê:</span>
                        <span class="value">{{ $checkout->inventory_status }}</span>
                    </div>

                    <div class="detail-item">
                        <span class="label">✅ Trạng thái xác nhận của người dùng:</span>
                        <span class="value">{{ $checkout->user_confirmation_status }}</span>
                    </div>

                    @if($action === 'reject' && $checkout->user_rejection_reason)
                    <div class="detail-item">
                        <span class="label">❌ Lý do từ chối:</span>
                        <span class="value">{{ $checkout->user_rejection_reason }}</span>
                    </div>
                    @endif

                    <div class="detail-item">
                        <span class="label">🚪 Trạng thái rời phòng:</span>
                        <span class="value">{{ $checkout->has_left ? 'Đã rời' : 'Chưa rời' }}</span>
                    </div>

                    <div class="detail-item">
                        <span class="label">💰 Tiền cọc hợp đồng:</span>
                        <span class="value money-highlight">{{ number_format($checkout->contract->deposit_amount ?? 0, 0, ',', '.') }} VNĐ</span>
                    </div>

                    <div class="detail-item">
                        <span class="label">💸 Số tiền khấu trừ:</span>
                        <span class="value">{{ number_format($checkout->deduction_amount ?? 0, 0, ',', '.') }} VNĐ</span>
                    </div>

                    <div class="detail-item">
                        <span class="label">💵 Số tiền hoàn lại cuối cùng:</span>
                        <span class="value money-highlight">{{ number_format($checkout->final_refunded_amount ?? 0, 0, ',', '.') }} VNĐ</span>
                    </div>

                    @if($checkout->note)
                    <div class="detail-item">
                        <span class="label">📝 Ghi chú:</span>
                        <span class="value">{{ $checkout->note }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div style="text-align: center;">
                <a href="{{ config('app.url') }}/checkouts" class="action-button">
                    👁️ Xem chi tiết yêu cầu
                </a>
            </div>
        </div>

        <div class="footer">
            <p class="brand">Trân trọng,<br>{{ config('app.name') }}</p>
            <p>© {{ date('Y') }} {{ config('app.name') }}. Tất cả quyền được bảo lưu.</p>
            <p>📧 Email được gửi tự động, vui lòng không trả lời email này.</p>
        </div>
    </div>
</body>
</html>
