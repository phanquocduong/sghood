1. **Xem danh sách hóa đơn với phân trang và lọc**

-   Người dùng có thể xem toàn bộ danh sách hóa đơn cá nhân, bao gồm thông tin cơ bản như mã hóa đơn, loại hoá đơn, tháng/năm, tổng tiền, trạng thái, và thời gian tạo.
-   Hỗ trợ lọc theo loại hóa đơn, tháng và năm.
-   Hỗ trợ sắp xếp theo thời gian: cũ nhất hoặc mới nhất.
-   Hỗ trợ phân trang với thông tin tổng số trang, tổng hóa đơn, trang hiện tại.

2. **Xem chi tiết một hóa đơn cụ thể**

-   Người dùng có thể xem thông tin chi tiết của một hóa đơn bao gồm: loại hóa đơn, tháng/năm, các khoản phí (phòng, điện, nước, đỗ xe, rác, internet, dịch vụ), tổng tiền, trạng thái, thời gian tạo/hoàn tiền (nếu có).
-   Tích hợp thông tin liên quan: hợp đồng (ngày bắt đầu/kết thúc, tiền cọc), phòng (tên phòng, phí mặc định từ nhà trọ), người dùng (tên, email, phone), chỉ số đồng hồ (điện/nước hiện tại).
-   Đặc biệt với hóa đơn "Hàng tháng": Hiển thị chỉ số đồng hồ tháng trước để tính toán chênh lệch.

3. **Thanh toán hóa đơn và cập nhật tự động qua webhook**

-   Người dùng có thể thanh toán hóa đơn (bao gồm hóa đơn đặt cọc) qua tích hợp SePay. Hệ thống tự động xử lý webhook từ SePay để cập nhật trạng thái hóa đơn từ "Chưa trả" sang "Đã trả" khi thanh toán thành công.
-   Kiểm tra khớp số tiền và mã hóa đơn để tránh lỗi.
-   Với hóa đơn "Đặt cọc": Sau thanh toán, hệ thống tự động kích hoạt hợp đồng, cập nhật trạng thái phòng, và vai trò người dùng. Đồng thời gửi thông báo email và thông báo đẩy cho admin.
