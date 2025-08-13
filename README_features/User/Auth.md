1. **Đăng Ký Tài Khoản Mới (Registration)**:

    - Người dùng bắt đầu bằng việc nhập số điện thoại (hỗ trợ định dạng +84 hoặc bắt đầu bằng 0, theo sau là 9 chữ số hợp lệ như 03x, 05x, v.v.).
    - Hệ thống gửi mã OTP qua SMS (sử dụng Firebase Auth và reCAPTCHA để chống bot).
    - Người dùng nhập mã OTP để xác minh danh tính.
    - Sau xác minh, người dùng nhập thông tin cá nhân: họ tên, email, mật khẩu (yêu cầu mạnh: ít nhất 8 ký tự, bao gồm chữ hoa/thường, số, ký tự đặc biệt như @$!%\*?&.-), và xác nhận mật khẩu.
    - Hệ thống gửi email xác minh (với liên kết hết hạn sau 60 phút) để hoàn tất đăng ký.

2. **Đăng Nhập Vào Tài Khoản (Login)**:

    - Người dùng nhập username (có thể là số điện thoại hoặc email) và mật khẩu.
    - Nếu dùng số điện thoại, hệ thống tự động chuẩn hóa thành định dạng +84 (ví dụ: 0123456789 thành +84123456789).
    - Chỉ cho phép đăng nhập nếu email đã được xác minh (nếu chưa, hiển thị thông báo yêu cầu xác minh).
    - Sau đăng nhập thành công, người dùng được chuyển hướng về trang chính, và hệ thống lưu token bảo mật (sử dụng Laravel Sanctum).

3. **Quên Mật Khẩu Và Đặt Lại Mật Khẩu (Forgot Password & Reset Password)**:

    - Người dùng nhập số điện thoại để yêu cầu gửi OTP (tương tự đăng ký, với reCAPTCHA).
    - Nhập mã OTP để xác minh.
    - Sau xác minh, người dùng nhập mật khẩu mới (yêu cầu mạnh như đăng ký) và xác nhận mật khẩu.
    - Hệ thống cập nhật mật khẩu mới mà không cần mật khẩu cũ.

4. **Xác Minh Email (Email Verification)**:

    - Sau đăng ký, người dùng nhận email chứa liên kết xác minh (thiết kế đẹp với template HTML, bao gồm nút "Xác Minh Email Ngay" và thông tin bảo mật).
    - Click liên kết dẫn đến trang xác minh (xac-minh-email.vue), hiển thị thông báo thành công/thất bại, với đếm ngược 5 giây tự động chuyển về trang chủ.
    - Nếu liên kết hết hạn hoặc không hợp lệ, hiển thị lỗi và chuyển hướng.

5. **Đăng Xuất Khỏi Tài Khoản (Logout)**:
    - Người dùng click nút đăng xuất (tích hợp trong store auth).
    - Hệ thống xóa toàn bộ token, session, cookie (như sanctum_token, XSRF-TOKEN), và xóa dữ liệu lưu trữ (localStorage nếu dùng Pinia persisted).
    - Chuyển hướng về trang chủ với thông báo "Đăng xuất thành công".
