### 2. **Upload ảnh căn cước công dân (CCCD) để quét thông tin tự động**

-   Để kích hoạt hợp đồng, người dùng sẽ truy cập vào trang chi tiết hợp đồng để xem nội dung chi tiết (bao gồm ID hợp đồng, trạng thái, nội dung hợp đồng).
-   Khi hợp đồng ở trạng thái "Chờ xác nhận", người dùng có thể upload đúng 2 ảnh CCCD (mặt trước và mặt sau) qua giao diện kéo-thả hoặc chọn file.
-   Hệ thống tự động quét và trích xuất thông tin (họ tên, năm sinh, số CCCD, ngày cấp, nơi cấp, địa chỉ thường trú) bằng Google Vision API.
-   Thông tin được điền tự động vào các trường thông tin cá nhân trong nội dung hợp đồng.

### 3. **Nhập thông tin CCCD thủ công nếu quét thất bại**

-   Nếu quét thất bại liên tục (từ 5 lần trở lên) (lỗi do hệ thống quét lỗi), hệ thống sẽ cho phép bypass (hiển thị thông báo hướng dẫn).
-   Người dùng nhập thủ công thông tin CCCD trực tiếp vào các trường trong hợp đồng và upload thủ công hình ảnh CCCD.
-   Quản trị viên sẽ duyệt thủ công các thông tin này.

### 4. **Lưu hợp đồng sau khi điền thông tin**

-   Người dùng nhấn nút "Lưu hợp đồng" để cập nhật nội dung (bao gồm thông tin CCCD đã quét hoặc nhập thủ công).
-   Hệ thống gửi yêu cầu đến backend để lưu và thay đổi trạng thái thành "Chờ duyệt" (hoặc "Chờ duyệt thủ công" nếu bypass).
-   Hệ thống tự động gửi thông báo email và thông báo đẩy đến các quản trị viên

### 5. **Ký chữ ký điện tử**

-   Khi hợp đồng ở trạng thái "Chờ ký" (sau khi admin đã duyệt), người dùng có thể vẽ chữ ký trực tiếp trên canvas (hỗ trợ chuột hoặc cảm ứng).
-   Có hỗ trợ xóa chữ ký và xác nhận chữ ký trước khi ký.

### 6. **Xác nhận OTP để hoàn tất ký hợp đồng**

-   Sau khi xác nhận chữ ký, người dùng nhận OTP qua số điện thoại.
-   Nhập OTP vào modal popup để xác nhận và ký hợp đồng.
-   Sau ký thành công, trạng thái hợp đồng thay đổi thành "Chờ thanh toán tiền cọc".
-   Hệ thống tự động chèn chữ ký vào phần "Bên B" của hợp đồng. Hợp đồng lúc này đã có nội dung đầy đủ.
-   Hệ thống tự động gửi thông báo email và thông báo đẩy đến các quản trị viên.

### 7. **Thanh toán tiền cọc hóa đơn**

-   Sau khi ký, người dùng sẽ được chuyển hướng đến trang thanh toán hóa đơn đặt cọc.
-   Hệ thống hỗ trợ 3 phương thức:
    -   Quét mã QR.
    -   Chuyển khoản thủ công.
    -   Thanh toán tiền mặt.
-   Hệ thống tự động cập nhật trạng thái "Đã trả" nếu thanh toán thành công (thông qua webhook bên thứ 3 hỗ trợ là SePay).
-   Hợp đồng sẽ được kích hoạt sau khi thanh toán tiền cọc thành công, chuyển trạng thái thành "Hoạt động".
-   Hệ thống tự động gửi thông báo email và thông báo đẩy đến các quản trị viên.
