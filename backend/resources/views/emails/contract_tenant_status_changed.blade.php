<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật trạng thái người ở chung</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; background-color: #f8f9fa; color: #333; }
        .email-container { max-width: 600px; margin: 20px auto; background: white; border: black 1px solid; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #ffc107, #fd7e14); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 24px; margin-bottom: 10px; font-weight: 600; }
        .header p { font-size: 16px; opacity: 0.9; }
        .content { padding: 40px 30px; }
        .greeting { font-size: 18px; margin-bottom: 20px; color: #2c3e50; }

        .status-header { background: #f8f9fa; border-radius: 10px; padding: 25px; margin: 25px 0; border-left: 4px solid #fd7e14; text-align: center; }
        .status-header h2 { color: #fd7e14; margin-bottom: 5px; font-size: 20px; font-weight: bold; }
        .status-header .room-info { color: #495057; font-size: 16px; margin-bottom: 15px; }

        .tenant-info { display: flex; justify-content: space-between; margin: 20px 0; flex-wrap: wrap; }
        .tenant-left, .tenant-right { flex: 1; min-width: 250px; }
        .tenant-left { margin-right: 20px; }
        .info-item { margin-bottom: 8px; }
        .info-label { font-weight: 600; color: #495057; display: inline-block; min-width: 80px; }
        .info-value { color: #212529; }
        .status-badge { 
            background: #fff3cd; 
            padding: 5px 15px; 
            border-radius: 5px; 
            display: inline-block; 
            font-weight: bold; 
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .status-badge.approved { background: #d4edda; color: #155724; border-color: #c3e6cb; }
        .status-badge.rejected { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }

        .status-table { width: 100%; border-collapse: collapse; margin: 25px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        .status-table th { background: #fd7e14; color: white; padding: 15px 12px; text-align: center; font-weight: 600; font-size: 14px; }
        .status-table td { padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6; }
        .status-table tr:nth-child(even) { background-color: #f8f9fa; }
        .status-table tr:hover { background-color: #fff4e6; }
        .status-table .field-name { text-align: left; font-weight: 500; }
        .status-table .field-value { text-align: left; }

        .rejection-info { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .rejection-info h4 { color: #856404; margin-bottom: 10px; font-size: 16px; }
        .rejection-reason { background: #fff; padding: 10px; border-radius: 5px; border: 1px solid #ffeeba; color: #856404; font-weight: 600; }

        .warning-message { background: #fff3cd; border: 1px solid #ffeeba; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .warning-message h4 { color: #856404; margin-bottom: 10px; font-size: 16px; }
        .warning-message p { color: #856404; line-height: 1.5; }

        .success-message { background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .success-message h4 { color: #155724; margin-bottom: 10px; font-size: 16px; }
        .success-message p { color: #155724; line-height: 1.5; }

        .message { color: #6c757d; line-height: 1.8; margin: 20px 0; }
        .cta-section { text-align: center; margin: 30px 0; }
        .cta-button { display: inline-block; background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 12px 30px; text-decoration: none; border-radius: 25px; font-weight: 600; transition: all 0.3s ease; }
        .cta-button:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3); }
        .footer { background: #343a40; color: white; padding: 25px; text-align: center; }
        .footer p { margin-bottom: 10px; opacity: 0.8; }
        .contact-info { font-size: 14px; opacity: 0.7; }
        .icon { display: inline-block; width: 20px; height: 20px; margin-right: 8px; vertical-align: middle; }

        @media (max-width: 600px) {
            .email-container { margin: 10px; border-radius: 10px; }
            .content { padding: 30px 20px; }
            .header { padding: 25px 20px; }
            .tenant-info { flex-direction: column; }
            .tenant-left { margin-right: 0; margin-bottom: 15px; }
            .status-table { font-size: 12px; }
            .status-table th, .status-table td { padding: 8px 6px; }
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="header">
        <h1>⚠️ Cập nhật trạng thái người ở chung</h1>
        <p>Trạng thái của người ở chung tại phòng {{ $roomName }} đã được cập nhật.</p>
    </div>
    <div class="content">
        <div class="greeting">
            Xin chào <strong>{{ $contractTenant->contract->user->name }}</strong>,
        </div>

        @if($status === 'Đã duyệt')
            <div class="success-message">
                <h4>✅ Trạng thái đã được cập nhật</h4>
                <p>Thông tin người ở chung <strong style="color: #28a745;">{{ $contractTenant->name }}</strong> đã được phê duyệt thành công.</p>
            </div>
        @elseif($status === 'Từ chối')
            <div class="warning-message">
                <h4>❌ Trạng thái đã được cập nhật</h4>
                <p>Thông tin người ở chung <strong style="color: #fd7e14;">{{ $contractTenant->name }}</strong> đã bị từ chối.</p>
            </div>
        @else
            <div class="warning-message">
                <h4>🔄 Trạng thái đã được cập nhật</h4>
                <p>Thông tin người ở chung <strong style="color: #fd7e14;">{{ $contractTenant->name }}</strong> đã được cập nhật trạng thái thành <strong>{{ $status }}</strong>.</p>
            </div>
        @endif

        <div class="status-header">
            <h2>THÔNG TIN NGƯỜI Ở CHUNG</h2>
            <div class="room-info">Phòng {{ $roomName }}</div>
        </div>

        <div class="tenant-info">
            <div class="tenant-left">
                <div class="info-item">
                    <span class="info-label">👤 Tên:</span>
                    <span class="info-value">{{ $contractTenant->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">📧 Email:</span>
                    <span class="info-value">{{ $contractTenant->email }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">📞 SĐT:</span>
                    <span class="info-value">{{ $contractTenant->phone }}</span>
                </div>
            </div>
            <div class="tenant-right">
                <div class="info-item">
                    <span class="info-label">🏠 Phòng:</span>
                    <span class="status-badge">{{ $roomName }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">🕐 Trạng thái:</span>
                    <span class="status-badge {{ $status === 'Đã duyệt' ? 'approved' : ($status === 'Từ chối' ? 'rejected' : '') }}">{{ $status }}</span>
                </div>
            </div>
        </div>

        <table class="status-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Thông tin</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="field-name">👤 Tên người ở chung</td>
                    <td class="field-value">{{ $contractTenant->name }}</td>
                </tr>
                <tr>
                    <td class="field-name">🏠 Phòng</td>
                    <td class="field-value">{{ $roomName }}</td>
                </tr>
                <tr>
                    <td class="field-name">📧 Email</td>
                    <td class="field-value">{{ $contractTenant->email }}</td>
                </tr>
                <tr>
                    <td class="field-name">📞 Số điện thoại</td>
                    <td class="field-value">{{ $contractTenant->phone }}</td>
                </tr>
                <tr>
                    <td class="field-name">🕐 Trạng thái hiện tại</td>
                    <td class="field-value"><strong>{{ $status }}</strong></td>
                </tr>
            </tbody>
        </table>

        @if ($status === 'Từ chối' && $rejectionReason)
            <div class="rejection-info">
                <h4>📋 Lý do từ chối:</h4>
                <div class="rejection-reason">
                    {{ $rejectionReason }}
                </div>
            </div>
        @endif

        <p class="message">
            Để xem chi tiết hoặc quản lý thông tin người ở chung, vui lòng truy cập vào trang quản lý của chúng tôi bằng cách nhấn vào nút bên dưới:
        </p>

        <div class="cta-section">
            <a href="https://sghood.com.vn/quan-ly/nguoi-o-chung" class="cta-button" style="color: #ffffff;">
                <span class="icon">📝</span> Truy cập trang quản lý người ở chung
            </a>
        </div>

        <p class="message">
            Nếu bạn cần hỗ trợ, vui lòng liên hệ với chúng tôi. Xin cảm ơn!
        </p>
    </div>

    <div class="footer">
        <p><strong>📧 Đội ngũ hỗ trợ khách hàng</strong></p>
        <div class="contact-info">
            <p style="color: #ffffff;">📞 Hotline: 082 828 3169 | ✉️ Email: sghood@gmail.com</p>
            <p style="color: #ffffff;">🌐 Website: sghood.com.vn</p>
        </div>
    </div>
</div>
</body>
</html>
