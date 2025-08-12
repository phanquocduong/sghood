### 1. Xem danh sách hóa đơn

-   **Mô tả**: Người dùng có thể xem toàn bộ danh sách các hóa đơn liên quan đến hợp đồng thuê phòng của mình.
-   **Chi tiết**:
    -   Giao diện hiển thị thông tin cơ bản của hóa đơn như: mã hóa đơn, loại, trạng thái, tổng tiền, ngày tạo.
    -   Nếu hóa đơn là hàng tháng, hiển thị tháng/năm tương ứng.
    -   Hỗ trợ phân trang để dễ dàng duyệt qua danh sách dài.
    -   Hiển thị thông báo “Chưa có hóa đơn nào” nếu danh sách trống.

### 2. Lọc và sắp xếp hóa đơn

-   **Mô tả**: Người dùng có thể lọc và sắp xếp danh sách hóa đơn để tìm kiếm nhanh chóng.
-   **Chi tiết**:
    -   Lọc theo tháng.
    -   Lọc theo năm.
    -   Lọc theo loại hóa đơn.
    -   Sắp xếp theo: mặc định (mới nhất), cũ nhất, mới nhất.
    -   Bộ lọc được áp dụng tự động khi thay đổi, và danh sách được cập nhật ngay lập tức.

### 3. Xem chi tiết hóa đơn

-   **Mô tả**: Người dùng có thể xem thông tin chi tiết của một hóa đơn cụ thể dưới dạng phiếu thu tiền.
-   **Chi tiết**:
    -   Có logo, thông tin công ty, mã hóa đơn, ngày tạo, thông tin khách thuê (tên, số điện thoại).
    -   Bảng chi tiết các khoản phí: tiền phòng, tiền điện (với chỉ số cũ/mới, tiêu thụ, đơn giá), tiền nước (tương tự), tiền gửi xe, tiền rác, tiền Internet.
    -   Tổng tiền bằng số và bằng chữ.
    -   Trạng thái thanh toán và ngày hoàn tiền nếu có.
    -   Có thông tin lưu ý về thanh toán đúng hạn hoặc mang hóa đơn đến văn phòng.
    -   Nếu hóa đơn chưa trả, hiển thị nút "Thanh toán" để chuyển đến trang thanh toán.

### 4. Thanh toán hóa đơn

-   **Mô tả**: Người dùng có thể thanh toán hóa đơn chưa trả qua các phương thức đa dạng.
-   **Chi tiết**:
    -   Hiển thị nút "Thanh toán" trong danh sách hóa đơn hoặc chi tiết hóa đơn nếu hóa đơn chưa trả.
    -   Các cách thanh toán:
        -   **Quét mã QR trực tiếp**: hoặc Người dùng có thể tải mã QR về máy.
        -   **Chuyển khoản thủ công**: Có hỗ trợ sao chép thông tin nhanh bằng nút copy.
        -   **Tiền mặt**: Hướng dẫn người dùng mang hóa đơn đến văn phòng, với địa chỉ, giờ làm việc, số tiền, và mã hóa đơn.
    -   Người dùng nhận thông báo thời gian thực về kết quả thanh toán. Hệ thống tự động kiểm tra trạng thái thanh toán mỗi 2 giây và thông báo "Thanh toán thành công" khi hoàn tất.
    -   Nếu là hóa đơn đặt cọc, sau thanh toán thành công, hợp đồng được kích hoạt và người dùng được chuyển hướng đến trang quản lý hợp đồng, thông báo thêm "Hợp đồng đã được kích hoạt."
    -   Nếu là hóa đơn hàng tháng, người dùng được chuyển hướng đến trang danh sách hóa đơn.
    -   Thông báo lỗi nếu có vấn đề

### 6. Xem trạng thái hoàn tiền

-   **Mô tả**: Người dùng có thể xem thông tin về hoàn tiền nếu hóa đơn đã được hoàn.
-   **Chi tiết**:
    -   Trong danh sách hóa đơn: Hiển thị trạng thái "Đã hoàn tiền" kèm ngày hoàn tiền.
    -   Trong chi tiết hóa đơn: Hiển thị ngày hoàn tiền nếu có.
