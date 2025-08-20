<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
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
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 0 0 5px 5px;
        }
        .footer {
            margin-top: 20px;
            padding: 15px;
            background-color: #e9ecef;
            text-align: center;
            font-size: 0.9em;
            color: #6c757d;
            border-radius: 5px;
        }
        .message-content {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $subject }}</h1>
    </div>
    
    <div class="content">
        <p>Xin chào <strong>{{ $recipientName }}</strong>,</p>
        
        <div class="message-content">
            {!! nl2br(e($message)) !!}
        </div>
        
        <p>Trân trọng,<br>
        <strong>Hệ thống SGHood</strong></p>
    </div>
    
    <div class="footer">
        <p>Email này được gửi tự động từ hệ thống quản lý nhà trọ SGHood.</p>
        <p>Thời gian: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>