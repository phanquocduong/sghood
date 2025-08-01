<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Cập nhật trạng thái lịch xem phòng</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .header {
            background-color: #3490dc;
            color: #fff;
            padding: 15px;
            text-align: center;
            border-radius: 5px 5px 0 0;
            margin: -20px -20px 20px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.9em;
            color: #777;
        }

        .details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .status {
            padding: 8px 12px;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
        }

        .status-pending {
            background-color: #f0f0f0;
            color: #555;
        }

        .status-confirmed {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-canceled {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h2>Thông báo cập nhật lịch xem phòng</h2>
        </div>

        <p>Xin chào {{ $schedule->user->name }},</p>

        <p>Lịch xem phòng của bạn đã được chúng tôi cập nhật.</p>

        <div class="details">
            <h3>Thông tin lịch xem phòng:</h3>
            <p><strong>Phòng:</strong> {{ $schedule->motel->name }}</p>
            <p><strong>Địa chỉ:</strong> {{ $schedule->room->motel->address ?? 'Không có thông tin' }}</p>
            <p><strong>Thời gian:</strong> {{ \Carbon\Carbon::parse($schedule->scheduled_at)->format('H:i - d/m/Y') }}
            </p>
            <p><strong>Trạng thái cũ:</strong>
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
            </p>
            <p><strong>Trạng thái mới:</strong>
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
            </p>
        </div>

        @if($newStatus == 'Đã xác nhận')
            <p>🎉 Tuyệt vời! Lịch xem phòng của bạn đã được xác nhận. Chúng tôi rất mong được gặp bạn đúng giờ hẹn.</p>
            <p>💡 <em>Lưu ý nhỏ:</em> Nếu có bất kỳ thay đổi nào, bạn hãy cho chúng tôi biết trước ít nhất 2 giờ nhé!</p>
        @elseif($newStatus == 'Huỷ bỏ')
            <p>😔 Rất tiếc, lịch xem phòng của bạn đã phải huỷ bỏ.</p>
            @if($schedule->cancellation_reason)
                <p><strong>Lý do:</strong> {{ $schedule->cancellation_reason }}</p>
            @endif
            <p>🔄 Đừng lo lắng! Bạn có thể đặt lịch mới bất cứ lúc nào hoặc liên hệ trực tiếp với chúng tôi để được hỗ trợ
                tốt nhất.</p>
        @elseif($newStatus == 'Từ chối')
            <p>😔 Rất tiếc, lịch xem phòng của bạn đã phải bị từ chối.</p>
            @if($schedule->cancellation_reason)
                <p><strong>Lý do:</strong> {{ $schedule->cancellation_reason }}</p>
            @endif
            <p>🔄 Đừng lo lắng! Bạn có thể đặt lịch mới bất cứ lúc nào hoặc liên hệ trực tiếp với chúng tôi để được hỗ trợ
                tốt nhất.</p>
        @elseif($newStatus == 'Hoàn thành')
            <p>🙏 Cảm ơn bạn đã dành thời gian đến xem phòng hôm nay!</p>
            <p>😊 Chúng tôi hy vọng bạn có trải nghiệm tuyệt vời và tìm được căn phòng ưng ý. Nếu cần hỗ trợ thêm, đừng ngần
                ngại liên hệ nhé!</p>
        @else
            <p>📝 Chúng tôi đã nhận được thông tin lịch xem phòng của bạn và đang xem xét kỹ lưỡng.</p>
            <p>⏰ Chúng tôi sẽ phản hồi sớm nhất có thể. Cảm ơn bạn đã kiên nhẫn chờ đợi!</p>
        @endif

        <p>💬 Có bất kỳ thắc mắc nào? Đừng ngại liên hệ với chúng tôi qua email hoặc hotline. Chúng tôi luôn sẵn sàng hỗ
            trợ bạn!</p>

        <div class="footer">
            <p><strong>🏢 Hệ thống quản lý SGHood</strong></p>
            <div class="contact-footer">
                <p style="color:rgb(102, 0, 0);">📞 Hotline: 082 828 3169 | ✉️ Email: sghood@gmail.com</p>
                <p style="color:rgb(102, 0, 0);">🌐 Website: sghood.com.vn</p>
            </div>
        </div>
    </div>
</body>

</html>
