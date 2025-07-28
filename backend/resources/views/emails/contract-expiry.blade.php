@php
    use App\Models\Config;

    // Lấy thông tin config từ database
    $contactConfigs = Config::getContactConfigs();
    $companyConfigs = Config::getCompanyConfigs();
    $emailConfigs = Config::getEmailConfigs();
@endphp

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Thông báo hợp đồng sắp hết hạn</title>
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
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .content {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }

        .contract-info {
            background-color: #fff3cd;
            padding: 15px;
            border-left: 4px solid #ffc107;
            margin: 20px 0;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 14px;
            color: #6c757d;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>

</head>

<body>
    <div class="header">
        <h2>🏠 {{ $companyConfigs['company_name'] ?? 'Hệ Thống Quản Lý Nhà Trọ' }}</h2>
        <p>Thông báo quan trọng về hợp đồng của bạn</p>
    </div>

    <div class="content">
        <h3>Kính gửi {{ $contract->user->name ?? 'Quý khách' }},</h3>

        <p>Chúng tôi xin thông báo rằng hợp đồng thuê phòng của bạn sắp hết hạn:</p>

        {{-- Thông tin hợp đồng --}}
        <div class="contract-info">
            <h4>📋 Thông tin hợp đồng:</h4>
            <ul>
                <li><strong>Mã hợp đồng:</strong> #{{ $contract->id }}</li>
                <li><strong>Nhà trọ:</strong> {{ $contract->room->motel->name ?? 'N/A' }}</li>
                <li><strong>Phòng:</strong> {{ $contract->room->name ?? 'N/A' }}</li>
                <li><strong>Địa chỉ:</strong> {{ $contract->room->motel->address ?? 'N/A' }}</li>
                <li><strong>Ngày bắt đầu:</strong> {{ \Carbon\Carbon::parse($contract->start_date)->format('d/m/Y') }}
                </li>
                <li><strong>Ngày kết thúc:</strong>
                    <span style="color: #dc3545; font-weight: bold;">
                        {{ \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y') }}
                    </span>
                </li>
                <li><strong>Số ngày còn lại:</strong>
                    @php
                        $endDate = \Carbon\Carbon::parse($contract->end_date);
                        $today = \Carbon\Carbon::today();

                        if ($endDate->isToday()) {
                            $daysText = 'Hết hạn hôm nay';
                            $color = '#dc3545';
                        } elseif ($endDate->isFuture()) {
                            $daysRemaining = $today->diffInDays($endDate);
                            $daysText = $daysRemaining . ' ngày';
                            $color = $daysRemaining <= 3 ? '#dc3545' : '#ffc107';
                        } else {
                            $daysOverdue = $endDate->diffInDays($today);
                            $daysText = 'Đã quá hạn ' . $daysOverdue . ' ngày';
                            $color = '#dc3545';
                        }
                    @endphp
                    <span style="color: {{ $color }}; font-weight: bold;">
                        {{ $daysText }}
                    </span>
                </li>
            </ul>
        </div>

        <p>Để đảm bảo quyền lợi của bạn và tránh gián đoạn trong việc thuê phòng, vui lòng:</p>

        <ul>
            <li>Truy cập hệ thống sghood để thực hiện gia hạn hợp đồng</li>
            <li>Hoặc thực hiện kết thúc hợp đồng</li>
            <li>Hoàn tất các thủ tục cần thiết trước ngày hết hạn</li>
        </ul>


        {{-- Nút liên hệ --}}
        <div style="text-align: center; margin: 20px 0;">
            @if(isset($contactConfigs['contact_phone']))
                <a href="tel:{{ $contactConfigs['contact_phone'] }}" class="btn">📞 Liên hệ ngay</a>
            @endif
            @if(isset($companyConfigs['company_website']))
                <a href="{{ $companyConfigs['company_website'] }}" class="btn" style="background-color: #28a745;">🌐 Truy
                    cập website</a>
            @endif
        </div>

        {{-- Thông tin liên hệ --}}
        <p>Nếu bạn có bất kỳ câu hỏi nào, vui lòng liên hệ với chúng tôi qua:</p>
        <ul>
            @if(isset($contactConfigs['contact_phone']))
                <li>📞 Điện thoại: {{ $contactConfigs['contact_phone'] }}</li>
            @endif
            @if(isset($contactConfigs['contact_email']))
                <li>📧 Email: {{ $contactConfigs['contact_email'] }}</li>
            @endif
            @if(isset($contactConfigs['contact_address']))
                <li>📍 Địa chỉ: {{ $contactConfigs['contact_address'] }}</li>
            @endif
            @if(isset($companyConfigs['company_website']))
                <li>🌐 Website: <a
                        href="{{ $companyConfigs['company_website'] }}">{{ $companyConfigs['company_website'] }}</a></li>
            @endif
        </ul>
    </div>

    <div class="footer">
        <p>Trân trọng,<br>
            <strong>{{ $companyConfigs['company_name'] ?? config('app.name', 'Hệ thống quản lý nhà trọ') }}</strong>
        </p>
        <p><small>{{ $emailConfigs['email_signature'] ?? 'Đây là email tự động, vui lòng không reply lại email này.' }}</small>
        </p>
        @if(isset($emailConfigs['email_footer_text']))
            <p><em>{{ $emailConfigs['email_footer_text'] }}</em></p>
        @endif
    </div>
</body>

</html>