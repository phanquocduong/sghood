1. **Đăng ký tài khoản**

    - Người dùng có thể tạo tài khoản mới bằng cách cung cấp thông tin như số điện thoại, tên, email và mật khẩu.
    - Hệ thống kiểm tra tính hợp lệ của thông tin (ví dụ: email và số điện thoại chưa được sử dụng, mật khẩu phải có ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt).
    - Sau khi đăng ký thành công, người dùng nhận được email xác minh để kích hoạt tài khoản.

2. **Xác minh email**

    - Người dùng nhận được một liên kết xác minh qua email sau khi đăng ký.
    - Nhấp vào liên kết để xác minh email, kích hoạt tài khoản và cho phép đăng nhập.
    - Hệ thống thông báo nếu liên kết không hợp lệ hoặc email đã được xác minh trước đó.

3. **Đăng nhập**

    - Người dùng có thể đăng nhập bằng email hoặc số điện thoại cùng với mật khẩu.
    - Hệ thống kiểm tra thông tin đăng nhập và yêu cầu email phải được xác minh trước khi cho phép truy cập.
    - Sau khi đăng nhập thành công, người dùng nhận được một mã token để duy trì phiên đăng nhập an toàn.
    - Người dùng sẽ được lưu một mã FCM (Firebase Cloud Messaging) token để hỗ trợ nhận thông báo đẩy từ ứng dụng.
    - Hệ thống cập nhật mã FCM token vào thông tin người dùng, đảm bảo ứng dụng có thể gửi thông báo chính xác.

4. **Đăng xuất**

    - Người dùng có thể đăng xuất khỏi hệ thống.
    - Hệ thống xóa toàn bộ phiên đăng nhập và token liên quan, đảm bảo bảo mật khi người dùng rời khỏi ứng dụng.

5. **Đặt lại mật khẩu**

    - Người dùng có thể đặt lại mật khẩu bằng cách cung cấp số điện thoại và mật khẩu mới.
    - Mật khẩu mới phải tuân thủ các quy tắc bảo mật (ít nhất 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt).
    - Hệ thống xác minh số điện thoại và cập nhật mật khẩu mới sau khi thông tin được xác thực.
