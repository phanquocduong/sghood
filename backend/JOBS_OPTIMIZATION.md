# Tối ưu hóa gửi Email và Thông báo với Jobs

## Mô tả
Đã tối ưu hóa việc gửi email và thông báo trong `CheckoutService` bằng cách sử dụng Laravel Jobs để xử lý bất đồng bộ, cải thiện hiệu suất và trải nghiệm người dùng.

## Những gì đã thực hiện

### 1. Tạo Jobs mới
- **SendCheckoutStatusUpdatedNotification**: Xử lý gửi thông báo khi trạng thái checkout được cập nhật thành "Đã kiểm kê"
- **SendCheckoutRefundNotification**: Xử lý gửi thông báo khi hoàn tiền được xác nhận

### 2. Cập nhật CheckoutService
- Thay thế việc gửi email và thông báo trực tiếp bằng dispatch Jobs
- Loại bỏ các import không cần thiết
- Giảm thời gian xử lý của các method chính

### 3. Cấu hình Queue
- Đã cập nhật `.env` để sử dụng `database` queue thay vì `sync`
- Sử dụng bảng `jobs` có sẵn trong database

## Lợi ích

### 1. Hiệu suất
- Các request API phản hồi nhanh hơn vì không phải chờ gửi email
- Giảm timeout risk cho các API endpoint

### 2. Độ tin cậy
- Jobs có thể retry tự động khi thất bại
- Error handling tốt hơn với method `failed()`
- Logging chi tiết cho việc debug

### 3. Khả năng mở rộng
- Có thể dễ dàng thêm nhiều loại thông báo khác
- Có thể scale bằng multiple queue workers

## Cách sử dụng

### 1. Chạy Queue Worker
```bash
php artisan queue:work --queue=default
```

### 2. Monitor Jobs
```bash
# Xem jobs đang chờ
php artisan queue:monitor

# Restart workers (sau khi deploy code mới)
php artisan queue:restart
```

### 3. Failed Jobs
```bash
# Xem jobs thất bại
php artisan queue:failed

# Retry job thất bại
php artisan queue:retry <job-id>

# Retry tất cả jobs thất bại
php artisan queue:retry all
```

## Cấu trúc Jobs

### SendCheckoutStatusUpdatedNotification
```php
// Dispatch job
SendCheckoutStatusUpdatedNotification::dispatch($checkout, $user, $room, $checkOutDate);
```

**Chức năng:**
- Gửi email thông báo trạng thái kiểm kê
- Tạo thông báo trong database
- Gửi FCM push notification

### SendCheckoutRefundNotification
```php
// Dispatch job
SendCheckoutRefundNotification::dispatch($checkout, $user, $room, $checkOutDate, $referenceCode);
```

**Chức năng:**
- Gửi email xác nhận hoàn tiền
- Tạo thông báo trong database
- Gửi FCM push notification

## Cấu hình Production

### 1. Supervisor (Linux)
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/app/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/app/storage/logs/worker.log
stopwaitsecs=3600
```

### 2. Cron Job (để restart workers định kỳ)
```bash
0 * * * * cd /path/to/app && php artisan queue:restart
```

## Monitoring và Troubleshooting

### 1. Logs
- Job execution logs: `storage/logs/laravel.log`
- Worker logs: Theo cấu hình supervisor

### 2. Database
- Kiểm tra bảng `jobs` để xem jobs đang chờ
- Kiểm tra bảng `failed_jobs` để xem jobs thất bại

### 3. Common Issues
- **Jobs không chạy**: Kiểm tra queue worker có đang chạy không
- **Jobs thất bại**: Xem logs và retry
- **Email không gửi**: Kiểm tra cấu hình SMTP trong `.env`
- **FCM không hoạt động**: Kiểm tra Firebase credentials

## Performance Tips

1. **Batch Processing**: Có thể group nhiều notifications thành 1 job
2. **Queue Priorities**: Sử dụng multiple queues với độ ưu tiên khác nhau
3. **Worker Scaling**: Tăng số lượng workers cho traffic cao
4. **Failed Job Cleanup**: Định kỳ xóa failed jobs cũ

## Backup Plan
Nếu cần quay lại gửi email đồng bộ, chỉ cần:
1. Đổi `QUEUE_CONNECTION=sync` trong `.env`
2. Jobs sẽ chạy ngay lập tức thay vì queue
