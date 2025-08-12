1. **Truy cập trang đăng nhập**

    - Quản trị viên mở trình duyệt và truy cập trang login để xem form đăng nhập đơn giản với các trường: Tên đăng nhập (hỗ trợ email hoặc số điện thoại), Mật khẩu, và tùy chọn "Ghi nhớ tôi".
    - Hiển thị logo và tiêu đề ứng dụng (SGHood).
    - Hỗ trợ hiển thị thông báo lỗi từ phiên trước (nếu có).

2. **Đăng nhập vào hệ thống**

    - Nhập tên đăng nhập (email hoặc số điện thoại), mật khẩu, và chọn "Ghi nhớ tôi" nếu muốn giữ phiên đăng nhập lâu dài.
    - Hệ thống tự động xác thực, kiểm tra role (chỉ cho phép role Admin, từ chối nếu là Người đăng ký hoặc Người thuê), và kiểm tra trạng thái tài khoản (phải "Hoạt động").
    - Sử dụng AJAX để submit form, hiển thị thông báo thành công và chuyển hướng đến dashboard nếu hợp lệ.
    - Hỗ trợ validation: Bắt buộc nhập username và password, hiển thị lỗi cụ thể (ví dụ: "Tên đăng nhập là bắt buộc" hoặc "Thông tin đăng nhập không chính xác").

3. **Xử lý lỗi và thông báo khi đăng nhập thất bại**

    - Nếu đăng nhập sai, hiển thị thông báo lỗi ngay trên form (ví dụ: "Tài khoản không có quyền admin", "Tài khoản của bạn hiện không hoạt động", hoặc "Thông tin đăng nhập không chính xác").
    - Giữ nguyên dữ liệu đã nhập (trừ mật khẩu) để dễ thử lại.

4. **Tự động lưu FCM token sau đăng nhập thành công**

    - Sau khi đăng nhập hợp lệ, hệ thống tự động yêu cầu quyền thông báo từ trình duyệt, lấy FCM token từ Firebase, và gửi lên server để lưu vào tài khoản người dùng.
    - Xử lý không đồng bộ (async) để không làm gián đoạn quá trình đăng nhập.
    - Hỗ trợ làm mới CSRF token nếu cần để đảm bảo an toàn.

5. **Đăng xuất khỏi hệ thống**
    - Từ bất kỳ trang nào sau khi đăng nhập, chọn tùy chọn logout để kết thúc phiên.
    - Hệ thống tự động invalidate session, regenerate token, và chuyển hướng về trang login.
    - Không yêu cầu xác nhận thêm, nhưng đảm bảo an toàn bằng cách xóa tất cả dữ liệu phiên.
