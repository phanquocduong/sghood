<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật trạng thái lịch xem phòng</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; background-color: #f8f9fa; color: #333; }
        .email-container { max-width: 600px; margin: 20px auto; background: white; border: black 1px solid; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); }
        .header { background: linear-gradient(135deg, #3490dc, #2c5aa0); color: white; padding: 30px; text-align: center; }
        .header h1 { font-size: 24px; margin-bottom: 10px; font-weight: 600; }
        .header p { font-size: 16px; opacity: 0.9; }
        .content { padding: 40px 30px; }
        .greeting { font-size: 18px; margin-bottom: 20px; color: #2c3e50; }

        .schedule-header { background: #f8f9fa; border-radius: 10px; padding: 25px; margin: 25px 0; border-left: 4px solid #3490dc; text-align: center; }
        .schedule-header h2 { color: #3490dc; margin-bottom: 5px; font-size: 20px; font-weight: bold; }

        .schedule-info { display: flex; justify-content: space-between; margin: 20px 0; flex-wrap: wrap; }
        .schedule-left, .schedule-right { flex: 1; min-width: 250px; }
        .schedule-left { margin-right: 20px; }
        .info-item { margin-bottom: 8px; }
        .info-label { font-weight: 600; color: #495057; display: inline-block; min-width: 100px; }
        .info-value { color: #212529; }

        .status { padding: 8px 15px; border-radius: 5px; font-weight: bold; display: inline-block; margin-left: 10px; }
        .status-pending { background-color: #f0f0f0; color: #555; }
        .status-confirmed { background-color: #fff3cd; color: #856404; }
        .status-completed { background-color: #d4edda; color: #155724; }
        .status-canceled { background-color: #f8d7da; color: #721c24; }

        .status-update { background: #e3f2fd; border: 1px solid #bbdefb; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .status-update h4 { color: #1976d2; margin-bottom: 10px; font-size: 16px; }

        .success-message { background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .success-message h4 { color: #155724; margin-bottom: 10px; font-size: 16px; }
        .success-message p { color: #155724; line-height: 1.5; }

        .warning-message { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .warning-message h4 { color: #856404; margin-bottom: 10px; font-size: 16px; }
        .warning-message p { color: #856404; line-height: 1.5; }

        .error-message { background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 20px; margin: 25px 0; }
        .error-message h4 { color: #721c24; margin-bottom: 10px; font-size: 16px; }
        .error-message p { color: #721c24; line-height: 1.5; }

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
            .schedule-info { flex-direction: column; }
            .schedule-left { margin-right: 0; margin-bottom: 15px; }
        }
    </style>
</head>
<body>
<div class="email-container">
    <div class="header">
        <h1>🏠 Thông báo cập nhật lịch xem phòng</h1>
        <p>Lịch xem phòng của bạn đã được chúng tôi cập nhật</p>
    </div>
    <div class="content">
        <div class="greeting">
            Xin chào <strong>{{ $schedule->user->name }}</strong>,
        </div>

        <div class="schedule-header">
            <h2>THÔNG TIN LỊCH XEM PHÒNG</h2>
        </div>

        <div class="schedule-info">
            <div class="schedule-left">
                <div class="info-item">
                    <span class="info-label">Phòng:</span>
                    <span class="info-value">{{ $schedule->motel->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Địa chỉ:</span>
                    <span class="info-value">{{ $schedule->room->motel->address ?? 'Không có thông tin' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Thời gian:</span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($schedule->scheduled_at)->format('H:i - d/m/Y') }}</span>
                </div>
            </div>
        </div>

        <div class="status-update">
            <h4>📋 Cập nhật trạng thái:</h4>
            <div class="info-item">
                <span class="info-label">Trạng thái cũ:</span>
                @php
                    $oldStatusClass = match ($oldStatus) {
                        'Đã xác nhận' => 'status-confirmed',
                        'Từ chối' => 'status-canceled',
                        'Huỷ bỏ' => 'status-canceled',
                        'Hoàn thành' => 'status-completed',
                        default => 'status-pending'
                    };
                @endphp
                <span class="status {{ $oldStatusClass }}">{{ $oldStatus }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Trạng thái mới:</span>
                @php
                    $newStatusClass = match ($newStatus) {
                        'Đã xác nhận' => 'status-confirmed',
                        'Từ chối' => 'status-canceled',
                        'Huỷ bỏ' => 'status-canceled',
                        'Hoàn thành' => 'status-completed',
                        default => 'status-pending'
                    };
                @endphp
                <span class="status {{ $newStatusClass }}">{{ $newStatus }}</span>
            </div>
        </div>

        @if($newStatus == 'Đã xác nhận')
            <div class="success-message">
                <h4>🎉 Lịch xem phòng đã được xác nhận!</h4>
                <p>Tuyệt vời! Lịch xem phòng của bạn đã được xác nhận. Chúng tôi rất mong được gặp bạn đúng giờ hẹn.</p>
                <p><strong>💡 Lưu ý:</strong> Nếu có bất kỳ thay đổi nào, bạn hãy cho chúng tôi biết trước ít nhất 2 giờ nhé!</p>
            </div>
        @elseif($newStatus == 'Huỷ bỏ')
            <div class="error-message">
                <h4>😔 Lịch xem phòng đã bị huỷ bỏ</h4>
                <p>Rất tiếc, lịch xem phòng của bạn đã phải huỷ bỏ.</p>
                @if($schedule->cancellation_reason)
                    <p><strong>Lý do:</strong> {{ $schedule->cancellation_reason }}</p>
                @endif
                <p><strong>🔄 Đừng lo lắng!</strong> Bạn có thể đặt lịch mới bất cứ lúc nào hoặc liên hệ trực tiếp với chúng tôi để được hỗ trợ tốt nhất.</p>
            </div>
        @elseif($newStatus == 'Từ chối')
            <div class="error-message">
                <h4>😔 Lịch xem phòng đã bị từ chối</h4>
                <p>Rất tiếc, lịch xem phòng của bạn đã phải bị từ chối.</p>
                @if($schedule->cancellation_reason)
                    <p><strong>Lý do:</strong> {{ $schedule->cancellation_reason }}</p>
                @endif
                <p><strong>🔄 Đừng lo lắng!</strong> Bạn có thể đặt lịch mới bất cứ lúc nào hoặc liên hệ trực tiếp với chúng tôi để được hỗ trợ tốt nhất.</p>
            </div>
        @elseif($newStatus == 'Hoàn thành')
            <div class="success-message">
                <h4>🙏 Cảm ơn bạn đã đến xem phòng!</h4>
                <p>Cảm ơn bạn đã dành thời gian đến xem phòng hôm nay!</p>
                <p>😊 Chúng tôi hy vọng bạn có trải nghiệm tuyệt vời và tìm được căn phòng ưng ý. Nếu cần hỗ trợ thêm, đừng ngần ngại liên hệ nhé!</p>
            </div>
        @else
            <div class="warning-message">
                <h4>📝 Đang xem xét lịch xem phòng</h4>
                <p>Chúng tôi đã nhận được thông tin lịch xem phòng của bạn và đang xem xét kỹ lưỡng.</p>
                <p>⏰ Chúng tôi sẽ phản hồi sớm nhất có thể. Cảm ơn bạn đã kiên nhẫn chờ đợi!</p>
            </div>
        @endif

        <p class="message">
            💬 Có bất kỳ thắc mắc nào? Đừng ngại liên hệ với chúng tôi qua email hoặc hotline. Chúng tôi luôn sẵn sàng hỗ trợ bạn!
        </p>

        <div class="cta-section">
            <a href="https://sghood.com.vn" class="cta-button" style="color: #ffffff;">
                <span class="icon">🏠</span> Xem thêm phòng trọ
            </a>
        </div>
    </div>

    <div class="footer">
        <p><strong>🏢 Hệ thống quản lý SGHood</strong></p>
        <div class="contact-info">
            <p style="color: #ffffff;">📞 Hotline: 082 828 3169 | ✉️ Email: sghood@gmail.com</p>
            <p style="color: #ffffff;">🌐 Website: sghood.com.vn</p>
        </div>
    </div>
</div>
</body>
</html>
