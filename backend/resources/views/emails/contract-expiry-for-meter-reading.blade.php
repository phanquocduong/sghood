<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hợp đồng sắp hết hạn - Cần nhập chỉ số điện nước</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #ff6b6b, #ffa500);
            color: white;
            padding: 25px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 25px;
            border: 1px solid #dee2e6;
            border-radius: 0 0 10px 10px;
        }
        .alert-box {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #ffa500;
        }
        .contract-info {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 15px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .action-button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            margin: 15px 0;
            font-weight: bold;
        }
        .footer {
            margin-top: 25px;
            padding: 20px;
            background-color: #e9ecef;
            text-align: center;
            font-size: 0.9em;
            color: #6c757d;
            border-radius: 10px;
        }
        .urgent {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🏠 Thông báo hợp đồng sắp hết hạn</h1>
        <p style="margin: 0; font-size: 1.1em;">Cần nhập chỉ số điện nước</p>
    </div>
    
    <div class="content">
        <p>Xin chào <strong>{{ $adminName }}</strong>,</p>
        
        <div class="alert-box">
            <h3 style="margin-top: 0;">⚠️ THÔNG BÁO QUAN TRỌNG</h3>
            <p class="urgent">Hợp đồng thuê phòng sắp hết hạn và cần thực hiện nhập chỉ số điện nước cuối kỳ!</p>
        </div>
        
        <div class="contract-info">
            <h3 style="color: #007bff; margin-top: 0;">� Thông tin hợp đồng</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; font-weight: bold; width: 40%;">Mã hợp đồng:</td>
                    <td style="padding: 8px 0;">#{{ $contractId }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Phòng:</td>
                    <td style="padding: 8px 0;">{{ $roomNumber }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Nhà trọ:</td>
                    <td style="padding: 8px 0;">{{ $motelName }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Khách thuê:</td>
                    <td style="padding: 8px 0;">{{ $tenantName }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Ngày hết hạn:</td>
                    <td style="padding: 8px 0; color: #dc3545; font-weight: bold;">{{ $endDate }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; font-weight: bold;">Thời gian còn lại:</td>
                    <td style="padding: 8px 0; color: #dc3545; font-weight: bold;">{{ $daysRemaining }} ngày</td>
                </tr>
            </table>
        </div>
        
        <div class="alert-box">
            <h4 style="margin-top: 0;">📝 Hành động cần thực hiện:</h4>
            <p><strong>Vui lòng nhập chỉ số điện nước cuối kỳ</strong> để:</p>
            <ul>
                <li>Tính toán hóa đơn cuối kỳ chính xác</li>
                <li>Hoàn tất quy trình kết thúc hợp đồng</li>
                <li>Đảm bảo quyền lợi cho cả hai bên</li>
            </ul>
        </div>
        
        <div style="text-align: center; margin: 25px 0;">
            <a href="{{ $actionUrl }}" class="action-button">
                ⚡ Nhập chỉ số điện nước ngay
            </a>
        </div>
        
        <p style="margin-top: 25px;">
            <strong>Lưu ý:</strong> Để đảm bảo quy trình thuê trọ diễn ra suôn sẻ, 
            vui lòng thực hiện nhập chỉ số điện nước càng sớm càng tốt.
        </p>
        
        <p>Trân trọng,<br>
        <strong>Hệ thống quản lý SGHood</strong></p>
    </div>
    
    <div class="footer">
        <p><strong>📧 Email tự động từ hệ thống quản lý nhà trọ SGHood</strong></p>
        <p>⏰ Thời gian gửi: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p style="margin-bottom: 0;">
            Nếu có thắc mắc, vui lòng liên hệ bộ phận hỗ trợ kỹ thuật.
        </p>
    </div>
</body>
</html>