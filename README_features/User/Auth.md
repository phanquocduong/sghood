-   **Đăng ký tài khoản mới**: Người dùng có thể bắt đầu bằng cách nhập số điện thoại (định dạng Việt Nam, tự động chuẩn hóa thành +84 nếu bắt đầu bằng 0), nhận mã OTP qua SMS để xác minh danh tính, sau đó nhập mã OTP để tiếp tục. Tiếp theo, người dùng nhập họ và tên, địa chỉ email, mật khẩu (với yêu cầu bảo mật cao: ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt), và xác nhận mật khẩu. Sau khi hoàn tất, hệ thống gửi email xác minh để kích hoạt tài khoản.

-   **Xác minh email sau đăng ký**: Người dùng nhận email chứa liên kết xác minh (có thời hạn 60 phút), nhấn vào liên kết để xác nhận địa chỉ email là hợp lệ, đảm bảo tài khoản được kích hoạt và có thể đăng nhập.

-   **Đăng nhập vào hệ thống**: Người dùng có thể đăng nhập bằng số điện thoại (tự động chuẩn hóa định dạng +84) hoặc địa chỉ email, kết hợp với mật khẩu. Hệ thống kiểm tra xác thực và chỉ cho phép truy cập nếu email đã được xác minh.

-   **Quên mật khẩu và đặt lại mật khẩu**: Nếu quên mật khẩu, người dùng nhập số điện thoại để nhận mã OTP qua SMS, sau đó nhập mã OTP để xác minh. Tiếp theo, người dùng có thể đặt mật khẩu mới (với yêu cầu bảo mật tương tự đăng ký) và xác nhận mật khẩu để hoàn tất quá trình.

-   **Đăng xuất khỏi hệ thống**: Người dùng có thể đăng xuất an toàn, hệ thống sẽ xóa toàn bộ session, token xác thực và cookie liên quan, đảm bảo không còn truy cập trái phép.

-   **Bảo mật khi gửi OTP**: Người dùng thấy hệ thống sử dụng reCAPTCHA để chống bot và lạm dụng khi yêu cầu gửi mã OTP, giúp bảo vệ khỏi các cuộc tấn công tự động.
