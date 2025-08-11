1. **Xem danh sách các yêu cầu trả phòng (Checkout)**

-   Admin có thể xem toàn bộ danh sách checkout với thông tin chính: Mã hợp đồng (HD), Tên người dùng, Ngày checkout, Trạng thái hoàn tiền (Đã xử lý/Chờ xử lý/Hủy bỏ), Trạng thái kiểm kê (Chờ kiểm kê/Kiểm kê lại/Đã kiểm kê), Trạng thái xác nhận của người dùng (Chưa xác nhận/Đồng ý/Từ chối).
-   Hỗ trợ tìm kiếm theo mã hợp đồng hoặc tên người dùng, lọc theo trạng thái kiểm kê, và sắp xếp theo thứ tự mới/cũ (sort_order).
-   Phân trang (pagination) với số lượng mặc định 10 mục/trang, và hiển thị tổng số yêu cầu (badge).
-   Hiển thị thông báo thành công/lỗi sau các hành động như cập nhật hoặc xác nhận.

2. **Xem chi tiết một yêu cầu trả phòng**

-   Admin có thể click nút "Xem" để mở modal chi tiết, hiển thị: Tên phòng, Ngày checkout, Trạng thái rời đi (Chưa/Đã rời đi), Trạng thái kiểm kê, Trạng thái xác nhận người dùng (với lý do từ chối nếu có), Số tiền khấu trừ, Tiền cọc, Số tiền hoàn lại.
-   Xem chi tiết kiểm kê: Danh sách các mục inventory (tên, tình trạng, chi phí).
-   Xem hình ảnh kiểm kê: Hiển thị các ảnh đã upload.
-   Nếu đã kiểm kê và người dùng đồng ý: Hiển thị thông tin hoàn tiền (QR code cho chuyển khoản nếu có thông tin ngân hàng của người dùng, hoặc thông báo nhận tiền mặt).
-   Nếu người dùng từ chối: Hiển thị lý do từ chối.

3. **Xem thông tin chi tiết người dùng liên quan đến checkout**

-   Admin có thể click vào tên người dùng trong danh sách để mở modal xem chi tiết: Họ tên, Email, Số điện thoại, Địa chỉ, Giới tính, Ngày sinh, Ngày tạo tài khoản, và Ảnh đại diện (avatar).

4. **Kiểm kê (Cập nhật thông tin kiểm kê cho một yêu cầu)**

-   Admin có thể click nút "Kiểm kê" (chỉ hiển thị nếu trạng thái là Chờ kiểm kê hoặc Kiểm kê lại) để mở modal chỉnh sửa.
-   Thêm/sửa các mục kiểm kê: Tên, Tình trạng, Khấu hao (Đền bù) – hỗ trợ thêm nhiều mục động bằng nút "Thêm mục".
-   Upload hình ảnh mới, preview ảnh, và xóa ảnh hiện có.
-   Tự động tính toán: Tổng khấu trừ, Số tiền hoàn trả cuối cùng.
-   Bắt buộc trạng thái (status: Chờ kiểm kê/Đã kiểm kê), kiểm tra định dạng ảnh (jpeg/png/gif/jpg, max 2MB), Khấu hao (Đền bù) >=0.
-   Sau cập nhật: Gửi thông báo email và thông báo đẩy đến người dùng nếu chuyển sang "Đã kiểm kê".

5. **Kiểm kê lại (Re-inventory)**

-   Admin có thể click nút "Kiểm kê lại" nếu người dùng từ chối (user_confirmation_status = Từ chối) và trạng thái là Đã kiểm kê.
-   Chuyển trạng thái thành "Kiểm kê lại", xóa lý do từ chối, và cập nhật user_confirmation_status thành "Chưa xác nhận".
-   Chỉ cho phép nếu trạng thái hiện tại là Đã kiểm kê.

6. **Xác nhận hoàn tiền (Confirm Refund)**

-   Admin có thể xác nhận hoàn tiền nếu trạng thái kiểm kê là Đã kiểm kê và người dùng đồng ý.
-   Nếu người dùng nhận chuyển khoản: Admin chuyển khoản hoàn tiền -> Mở modal nhập mã tham chiếu, tạo giao dịch, cập nhật thời gian hoàn tiền cho hoá đơn đặt cọc và gửi thông báo email và thông báo đẩy đến người dùng.
-   Nếu nhận tiền mặt: Sau khi người dùng đến văn phòng nhận tiền mặt -> Admin xác nhận trực tiếp, tạo giao dịch mà không cần mã tham chiếu.
-   Sau xác nhận: Cập nhật trạng thái hoàn tiền thành Đã xử lý, kiểm tra nếu thời gian hoàn tiền > ngày kết thúc của hợp đồng thì tự động kết thúc hợp đồng, chuyển trạng thái của phòng thành Sửa chữa và xóa dữ liệu giấy tờ tuỳ thân của người dùng.

7. **Buộc xác nhận thay người dùng (Force Confirm User)**

-   Admin có thể click nút "Xác nhận" nếu trạng thái kiểm kê là Đã kiểm kê, người dùng chưa xác nhận quá 7 ngày kể từ lần cập nhật cuối cùng.
-   Chuyển trạng thái xác nhận của người dùng thành Đồng ý, gửi thông báo email và thông báo đẩy đến người dùng.

8. **Xác nhận người dùng đã rời phòng (Confirm Left)**

-   Admin có thể click nút "Xác nhận rời đi" cho trạng thái rời đi của người dùng nếu trạng thái hoàn tiền là Đã xử lý, trạng thái kiểm kê và Đã kiểm kê và người dùng chưa xác nhận rời phòng.

9. **Tự động xác nhận kiểm kê (Auto-Confirm via Cron Job)**

-   Hệ thống có cơ chế tự động chạy hàng ngày để kiểm tra các checkout quá hạn 7 ngày.
-   Nếu đủ điều kiện (Đã kiểm kê + Người dùng chưa xác nhận): Tự động chuyển thành trạng thái xác nhận của người dùng thành Đồng ý và gửi thông báo email và thông báo đẩy đến người dùng.
