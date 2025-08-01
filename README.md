# SGHood - Hệ thống quản lý thuê trọ thông minh

Website quản lý thuê trọ thông minh tại TP.HCM với đầy đủ tính năng cho người thuê, chủ trọ và quản trị viên.

## Tổng quan hệ thống
SGHood là một nền tảng toàn diện giúp kết nối người thuê trọ với chủ trọ, đồng thời cung cấp các công cụ quản lý hiện đại và hiệu quả. Hệ thống được thiết kế để giải quyết các vấn đề phổ biến trong việc thuê trọ tại TP.HCM như: tìm kiếm phòng trọ phù hợp, quản lý hợp đồng, thanh toán, và dịch vụ hậu mãi.

## Cấu trúc dự án
```
troviet-platform/
├── backend/                 # Laravel API Backend
│   ├── app/
│   │   ├── Console/        # Commands & Scheduled Tasks
│   │   ├── Http/           # Controllers, Middleware, Requests
│   │   ├── Models/         # Eloquent Models
│   │   ├── Services/       # Business Logic Services
│   │   └── Jobs/           # Background Jobs
│   ├── database/           # Migrations, Seeders, Factories
│   ├── resources/          # Views, Email Templates
│   └── routes/             # API & Web Routes
└── frontend/               # Nuxt.js Frontend
    ├── components/         # Vue Components
    ├── pages/              # Page Components
    ├── layouts/            # Layout Templates
    ├── stores/             # Pinia State Management
    └── composables/        # Reusable Logic
```

- **backend**: Laravel API phục vụ cả người dùng và admin với RESTful API
- **frontend**: Nuxt.js application cho giao diện người thuê trọ

## Tính năng chính

### Cho người thuê trọ

- **Tìm kiếm thông minh**: Lọc theo khu vực, giá cả, tiện ích, đánh giá
- **Đặt lịch xem phòng**: Hệ thống đặt lịch tự động với xác nhận realtime
- **Quản lý hợp đồng**: Ký hợp đồng điện tử, gia hạn, trả phòng
- **Thanh toán trực tuyến**: Hỗ trợ nhiều phương thức thanh toán (VNPay, MoMo, Banking)
- **Theo dõi hóa đơn**: Xem lịch sử thanh toán, hóa đơn điện nước
- **Yêu cầu sửa chữa**: Gửi yêu cầu bảo trì với hình ảnh minh chứng
- **Thông báo realtime**: FCM notifications cho các cập nhật quan trọng

### Cho chủ trọ/quản lý

- **Quản lý nhà trọ**: Thêm, sửa, xóa thông tin nhà trọ và phòng
- **Quản lý người thuê**: Theo dõi thông tin, lịch sử thuê
- **Quản lý hợp đồng**: Tạo, duyệt, gia hạn hợp đồng
- **Quản lý tài chính**:
  - Ghi chỉ số điện nước hàng tháng
  - Tạo hóa đơn tự động
  - Theo dõi doanh thu, công nợ
  - Lịch sử giao dịch chi tiết
- **Xử lý yêu cầu**: Duyệt đặt phòng, xem phòng, sửa chữa
- **Báo cáo thống kê**: Dashboard với biểu đồ và báo cáo chi tiết
- **Kiểm kê trả phòng**: Hệ thống kiểm kê tài sản khi trả phòng

### Cho admin hệ thống

- **Quản lý người dùng**: Phân quyền, khóa/mở tài khoản
- **Quản lý nội dung**: Khu vực, tiện ích, cấu hình hệ thống
- **Giám sát hệ thống**: Logs, theo dõi hiệu suất
- **Quản lý thanh toán**: Xử lý giao dịch, hoàn tiền

## Công nghệ sử dụng
### Backend (Laravel 10)

- **Framework**: Laravel 10 với PHP 8.1+
- **Database**: MySQL 8.0
- **Queue**: Redis cho background jobs
- **Storage**: Local storage + AWS S3 (tùy chọn)
- **Email**: SMTP với queue jobs
- **Authentication**: Laravel Sanctum
- **Payment**: VNPay, MoMo integration
- **Push Notifications**: Firebase Cloud Messaging
- **PDF Generation**: DomPDF cho hợp đồng

### Frontend (Nuxt 3)

- **Framework**: Nuxt 3 với Vue 3
- **UI Framework**: Bootstrap 5 + Custom CSS
- **State Management**: Pinia
- **HTTP Client**: Axios với interceptors
- **Authentication**: JWT tokens
- **Maps**: Google Maps API
- **Real-time**: WebSocket cho notifications
- **PWA**: Progressive Web App support

### DevOps & Deployment

- **Backend**: Laravel Forge cho tự động triển khai
- **Frontend**: Vercel cho static hosting
- **CI/CD**: GitHub Actions
- **Monitoring**: Laravel Telescope, Sentry
- **Caching**: Redis, Laravel Cache

## Cài đặt & Phát triển

### Yêu cầu hệ thống
- PHP 8.1+
- Node.js 16+
- MySQL 8.0
- Redis
- Composer
- NPM/Yarn

### Backend Setup

1. **Clone repository**
   ```bash
   git clone https://github.com/phanquocduong/sghood.git
   cd troviet-platform/backend
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Storage setup**
   ```bash
   php artisan storage:link
   ```

6. **Queue worker (development)**
   ```bash
   php artisan queue:work
   ```

7. **Start development server**
   ```bash
   php artisan serve
   ```

### Frontend Setup

1. **Chuyển đến thư mục frontend**
   ```bash
   cd ../frontend
   ```

2. **Install dependencies**
   ```bash
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   ```

4. **Start development server**
   ```bash
   npm run dev
   ```

## Triển khai Production

### Backend (Laravel Forge)
**Deployment script**
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan queue:restart
```

### Frontend (Vercel)
**Build for production**
```bash
npm run build
npm run generate
```

## Kiến trúc hệ thống

### Database Schema

- **Users**: Người dùng (người thuê, chủ trọ)
- **Motels**: Nhà trọ
- **Rooms**: Phòng trọ
- **Contracts**: Hợp đồng thuê
- **Bookings**: Đặt phòng
- **Invoices**: Hóa đơn
- **Transactions**: Giao dịch thanh toán
- **MeterReadings**: Chỉ số điện nước
- **RepairRequests**: Yêu cầu sửa chữa
- **Notifications**: Thông báo

### API Architecture

- **RESTful API**: Chuẩn REST cho tất cả endpoints
- **Authentication**: Bearer token với Sanctum
- **Rate Limiting**: Giới hạn request theo user/IP
- **Validation**: Form Request validation
- **Error Handling**: Định dạng phản hồi lỗi thống nhất
- **Logging**: Ghi log toàn diện với Laravel Log

### Security Features

- **CSRF Protection**: Bảo vệ CSRF tích hợp Laravel
- **XSS Protection**: Làm sạch đầu vào
- **SQL Injection**: Bảo vệ với Eloquent ORM
- **File Upload**: Xác thực tệp tải lên
- **Authentication**: Hỗ trợ xác thực nhiều yếu tố
- **Authorization**: Kiểm soát truy cập dựa trên vai trò

## Tính năng nâng cao

### Background Jobs
- **Email Queue**: Gửi email không đồng bộ
- **Notification Queue**: Push notifications
- **Invoice Generation**: Tạo hóa đơn tự động
- **Data Processing**: Xử lý dữ liệu lớn
- **File Processing**: Upload và resize hình ảnh

### Real-time Features
- **Live Chat**: Tin nhắn realtime giữa người thuê và chủ trọ
- **Notifications**: Thông báo realtime
- **Status Updates**: Cập nhật trạng thái đơn đặt phòng
- **Payment Status**: Cập nhật trạng thái thanh toán

### Analytics & Reporting
- **Revenue Analytics**: Báo cáo doanh thu theo thời gian
- **Occupancy Rate**: Tỷ lệ lấp đầy phòng
- **User Behavior**: Phân tích hành vi người dùng
- **Performance Metrics**: Các chỉ số hiệu suất hệ thống

## Testing

### Backend Testing
**Run all tests**
```bash
php artisan test
```

**Run specific test suite**
```bash
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

**Generate coverage report**
```bash
php artisan test --coverage
```

### Frontend Testing
**Run unit tests**
```bash
npm run test
```

**Run e2e tests**
```bash
npm run test:e2e
```

**Run with coverage**
```bash
npm run test:coverage
```

## Tối ưu hiệu suất

### Backend
- **Query Optimization**: Tối ưu truy vấn Eloquent
- **Caching**: Redis caching cho API responses
- **Database Indexing**: Chỉ mục cơ sở dữ liệu phù hợp
- **Image Optimization**: Lazy loading và nén ảnh
- **CDN**: Tối ưu phân phối tài nguyên

### Frontend
- **Code Splitting**: Dynamic imports
- **Lazy Loading**: Lazy loading cho component và route
- **Image Optimization**: Định dạng WebP, hình ảnh responsive
- **Caching**: Chiến lược caching trình duyệt
- **Bundle Optimization**: Tree shaking, minification

## Monitoring & Debugging

### Development Tools
- **Laravel Telescope**: Giám sát truy vấn cơ sở dữ liệu, jobs, mail
- **Laravel Debugbar**: Debug hiệu suất
- **Vue DevTools**: Debug component frontend
- **Postman Collection**: Bộ sưu tập kiểm thử API

### Production Monitoring
- **Error Tracking**: Tích hợp Sentry
- **Performance Monitoring**: Các chỉ số hiệu suất ứng dụng
- **Log Management**: Quản lý logs tập trung
- **Uptime Monitoring**: Theo dõi tính khả dụng dịch vụ

## Tài liệu

### API Documentation
- **Swagger/OpenAPI**: Tài liệu API tự động tạo
- **Postman Collection**: Bộ sưu tập API sẵn sàng import
- **Response Examples**: Mẫu request/response

### Development Guide
- **Coding Standards**: PSR-12 cho PHP, ESLint cho JS
- **Git Workflow**: Quy trình làm việc theo nhánh tính năng
- **Code Review**: Quy trình pull request
- **Deployment Guide**: Hướng dẫn triển khai từng bước

## Đóng góp
1. Fork repository
2. Tạo nhánh tính năng (git checkout -b feature/amazing-feature)
3. Commit thay đổi (git commit -m 'Add amazing feature')
4. Push lên nhánh (git push origin feature/amazing-feature)
5. Tạo Pull Request

## Hỗ trợ & Liên hệ
- **Email**: sghood@gmail.com
- **Issues**: GitHub Issues

SGHood - Kết nối mọi nhu cầu thuê trọ tại TP.HCM 🏠✨