### 1. **Xem chi tiết hợp đồng**

-   Người dùng có thể truy cập trang quản lý hợp đồng để xem nội dung chi tiết hợp đồng (bao gồm ID hợp đồng, trạng thái, nội dung HTML, và các phần mở rộng nếu có).
-   Hỗ trợ xem trạng thái hiện tại (ví dụ: "Chờ xác nhận", "Chờ ký", "Hoạt động").

### 2. **Upload ảnh căn cước công dân (CCCD) để quét thông tin tự động**

-   Khi hợp đồng ở trạng thái "Chờ xác nhận", người dùng có thể upload đúng 2 ảnh CCCD (mặt trước và mặt sau) qua giao diện kéo-thả hoặc chọn file.
-   Hệ thống tự động quét và trích xuất thông tin (họ tên, năm sinh, số CCCD, ngày cấp, nơi cấp, địa chỉ thường trú) bằng Google Vision API.
-   Thông tin được điền tự động vào các trường input trong nội dung hợp đồng.

### 3. **Nhập thông tin CCCD thủ công nếu quét thất bại**

-   Nếu quét thất bại liên tục (từ 5 lần trở lên), hệ thống cho phép bypass: Người dùng nhập thủ công thông tin CCCD trực tiếp vào các trường input trong hợp đồng.
-   Hiển thị thông báo hướng dẫn và vô hiệu hóa upload ảnh nếu đã bypass.
-   Quản trị viên sẽ duyệt thủ công các thông tin này.

### 4. **Lưu hợp đồng sau khi điền thông tin**

-   Người dùng nhấn nút "Lưu hợp đồng" để cập nhật nội dung (bao gồm thông tin CCCD đã quét hoặc nhập thủ công).
-   Hệ thống gửi yêu cầu đến backend để lưu và thay đổi trạng thái thành "Chờ duyệt" (hoặc "Chờ duyệt thủ công" nếu bypass).

### 5. **Ký chữ ký điện tử**

-   Khi hợp đồng ở trạng thái "Chờ ký" (sau khi admin duyệt), người dùng có thể vẽ chữ ký trực tiếp trên canvas (hỗ trợ chuột hoặc cảm ứng).
-   Chức năng xóa chữ ký và xác nhận chữ ký trước khi ký.
-   Hệ thống tự động chèn chữ ký vào phần "Bên B" của hợp đồng.

### 6. **Xác nhận OTP để hoàn tất ký hợp đồng**

-   Sau khi vẽ chữ ký, người dùng nhận OTP qua số điện thoạ.
-   Nhập OTP vào modal popup để xác nhận và ký hợp đồng.
-   Sau ký thành công, trạng thái hợp đồng thay đổi thành "Chờ thanh toán tiền cọc".

### 7. **Thanh toán tiền cọc hóa đơn**

-   Sau khi ký, người dùng được chuyển hướng đến trang thanh toán hóa đơn đặt cọc.
-   Hỗ trợ 3 phương thức:
    -   Quét mã QR (tự động tạo QR với thông tin tài khoản, số tiền, nội dung chuyển khoản; hệ thống kiểm tra trạng thái thanh toán thời gian thực mỗi 2 giây).
    -   Chuyển khoản thủ công (copy thông tin tài khoản ngân hàng, số tiền, nội dung chuyển khoản).
    -   Thanh toán tiền mặt (hiển thị địa chỉ văn phòng, giờ làm việc, và mã hóa đơn để mang theo).
-   Hệ thống tự động cập nhật trạng thái "Đã trả" nếu thanh toán thành công (qua webhook SePay cho chuyển khoản).
-   Hợp đồng sẽ được kích hoạt sau khi thanh toán tiền cọc thành công, chuyển trạng thái thành "Hoạt động"

### 8. **GỬi thông báo**

-   Ở mỗi bước tiến trình làm hợp đồng, hệ thống đều sẽ tự động gửi thông báo email và thông báo đẩy về cho quản trị viên
