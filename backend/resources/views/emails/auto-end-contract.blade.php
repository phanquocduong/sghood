<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo kết thúc hợp đồng</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .email-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #dc3545, #e74c3c);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }

        .header .subtitle {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }

        .content {
            padding: 40px 30px;
        }

        .alert-box {
            background: #fff5f5;
            border: 2px solid #fed7d7;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }

        .alert-icon {
            font-size: 48px;
            color: #e53e3e;
            margin-bottom: 15px;
        }

        .contract-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin: 25px 0;
            border-left: 5px solid #dc3545;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
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
            color: #212529;
            text-align: right;
            flex: 1;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-ended {
            background: #fed7d7;
            color: #c53030;
        }

        .next-steps {
            background: #e8f4fd;
            border: 2px solid #bee3f8;
            border-radius: 10px;
            padding: 20px;
            margin: 25px 0;
        }

        .next-steps h3 {
            color: #2b6cb0;
            margin-top: 0;
            font-size: 18px;
        }

        .next-steps ul {
            margin: 15px 0;
            padding-left: 20px;
        }

        .next-steps li {
            margin: 8px 0;
            color: #2d3748;
        }

        .contact-info {
            background: #f0fff4;
            border: 2px solid #c6f6d5;
            border-radius: 10px;
            padding: 20px;
            margin: 25px 0;
            text-align: center;
        }

        .contact-info h3 {
            color: #276749;
            margin-top: 0;
        }

        .footer {
            background: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }

        .footer p {
            margin: 5px 0;
            color: #6c757d;
            font-size: 14px;
        }

        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 10px 5px;
            transition: all 0.3s ease;
        }

        .btn:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        @media (max-width: 600px) {
            body {
                padding: 10px;
            }

            .content {
                padding: 20px 15px;
            }

            .contract-info {
                padding: 15px;
            }

            .info-row {
                flex-direction: column;
                align-items: flex-start;
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
            <h1>🏠 THÔNG BÁO KẾT THÚC HỢP ĐỒNG</h1>
            <p class="subtitle">Hợp đồng thuê nhà đã kết thúc tự động</p>
        </div>

        <div class="content">
            <div class="alert-box">
                <div class="alert-icon">⚠️</div>
                <h3 style="margin: 0; color: #e53e3e;">Hợp đồng đã kết thúc</h3>
                <p style="margin: 10px 0 0 0; color: #718096;">
                    Hợp đồng thuê nhà của bạn đã được hệ thống tự động kết thúc do hết hạn hiệu lực.
                </p>
            </div>

            <div class="contract-info">
                <h3 style="margin-top: 0; color: #dc3545; font-size: 20px;">
                    📋 Thông tin hợp đồng
                </h3>

                <div class="info-row">
                    <span class="info-label">Mã hợp đồng:</span>
                    <span class="info-value"><strong>#{{ $contract->id }}</strong></span>
                </div>

                <div class="info-row">
                    <span class="info-label">Tên phòng:</span>
                    <span class="info-value">{{ $property->title ?? "{$property->name}" }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Địa chỉ:</span>
                    <span
                        class="info-value">{{ $property->full_address ?? ($property->motel->address ?? 'N/A') }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Người thuê:</span>
                    <span class="info-value">{{ $tenant->name ?? 'N/A' }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Chủ nhà:</span>
                    <span class="info-value">{{ $landlord->name ?? ($property->motel->user->name ?? 'N/A') }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Ngày bắt đầu:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($contract->start_date)->format('d/m/Y') }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Ngày kết thúc:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y') }}</span>
                </div>

                <div class="info-row">
                    <span class="info-label">Trạng thái:</span>
                    <span class="info-value">
                        <span class="status-badge status-ended">Đã kết thúc</span>
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Tiền thuê hàng tháng:</span>
                    <span class="info-value"><strong>{{ number_format($contract->monthly_rent ?? $contract->rental_price, 0, ',', '.') }}
                            VNĐ</strong></span>
                </div>
            </div>

            <div class="next-steps">
                <h3>📝 Các bước tiếp theo cần thực hiện:</h3>
                <ul>
                    <li><strong>Kiểm tra tình trạng bất động sản:</strong> Đảm bảo bàn giao theo đúng tình trạng ban đầu
                    </li>
                    <li><strong>Thanh toán cuối kỳ:</strong> Hoàn tất các khoản phí còn lại (nếu có)</li>
                    <li><strong>Hoàn trả tiền đặt cọc:</strong> Xử lý việc hoàn trả tiền đặt cọc theo quy định</li>
                    <li><strong>Bàn giao chìa khóa:</strong> Trả lại chìa khóa và các tài sản thuộc về chủ nhà</li>
                    <li><strong>Cập nhật thông tin:</strong> Thay đổi địa chỉ và thông tin liên quan</li>
                </ul>
            </div>

            <div class="contact-info">
                <h3>📞 Cần hỗ trợ?</h3>
                <p>Nếu bạn có bất kỳ câu hỏi nào về việc kết thúc hợp đồng này, vui lòng liên hệ với chúng tôi:</p>
                <p><strong>Hotline:</strong> 1900-1234 | <strong>Email:</strong> support@troviet.com</p>

                <div style="margin-top: 20px;">
                    <a href="{{ url('/contracts/' . $contract->id) }}" class="btn">Xem chi tiết hợp đồng</a>
                    <a href="{{ url('/contact') }}" class="btn btn-secondary">Liên hệ hỗ trợ</a>
                </div>
            </div>

            <div style="text-align: center; margin: 30px 0; padding: 20px; background: #fff5f5; border-radius: 10px;">
                <h4 style="color: #e53e3e; margin: 0 0 10px 0;">⏰ Thời gian kết thúc</h4>
                <p style="margin: 0; color: #718096;">
                    Hợp đồng đã được tự động kết thúc vào lúc:
                    <strong>{{ now()->format('H:i:s d/m/Y') }}</strong>
                </p>
            </div>
        </div>

        <div class="footer">
            <p><strong>TroViet Platform</strong> - Nền tảng quản lý bất động sản thông minh</p>
            <p>Địa chỉ: 123 Đường ABC, Quận XYZ, TP.HCM | Điện thoại: (028) 1234-5678</p>
            <p style="font-size: 12px; color: #adb5bd;">
                Email này được gửi tự động từ hệ thống. Vui lòng không reply trực tiếp.
            </p>
        </div>
    </div>
</body>

</html>