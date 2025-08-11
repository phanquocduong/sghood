1. **Xem tổng quan thống kê hệ thống**:

    - Quản trị viên có thể xem dashboard thống kê với các thông tin chính:
        - **Doanh thu hôm nay**: Tổng số tiền giao dịch vào trong ngày hiện tại, định dạng VNĐ (ví dụ: 2.500.000 VNĐ).
        - **Doanh thu tháng này**: Tổng số tiền giao dịch vào trong tháng hiện tại, định dạng VNĐ.
        - **Phòng đang thuê**: Số phòng có trạng thái "Đã thuê" so với tổng số phòng (ví dụ: 10/50).
        - **Phòng trống**: Số phòng trống (tổng số phòng trừ số phòng đang thuê).
        - **Khách thuê hôm nay**: Số người dùng bắt đầu hợp đồng trong ngày hiện tại.
        - **Khách thuê tháng này**: Số người dùng bắt đầu hợp đồng trong tháng hiện tại.

2. **Xem biểu đồ doanh thu theo tháng**:

    - Quản trị viên có thể xem biểu đồ đường (line chart) thể hiện doanh thu hàng tháng trong năm hiện tại:
        - **Trục X**: Các tháng (T1, T2, ..., T12).
        - **Trục Y**: Doanh thu (VNĐ, định dạng gọn gàng như 2.5M).
        - **Dữ liệu**: Tổng số tiền giao dịch vào mỗi tháng.
        - **Tùy chỉnh**: Biểu đồ có màu xanh tím (#667eea), hiệu ứng tô nền, và tooltip hiển thị doanh thu định dạng tiền tệ

3. **Xem số lượng phòng trống theo dãy trọ**:

    - Quản trị viên có thể xem số lượng phòng trống theo từng nhà trọ:
        - **Hiển thị**: Tên nhà trọ và số phòng trống với badge màu:
            - Xanh lá (success): > 4 phòng trống.
            - Vàng (warning): 1-4 phòng trống.
            - Đỏ (danger): 0 phòng trống.
        - **Tổng phòng trống**: Hiển thị tổng số phòng trống trên toàn hệ thống.
        - **Ẩn/hiện**: Nếu có hơn 8 nhà trọ, các nhà trọ thừa được ẩn và có nút "Xem thêm"/"Thu gọn" để hiển thị/ẩn.

4. **Xem tình trạng người thuê**:

    - Quản trị viên có thể xem danh sách người thuê theo trạng thái hợp đồng:
        - **Đang thuê (Còn hạn)**: Hợp đồng có trạng thái "Hoạt động" và ngày kết thúc (`end_date`) còn xa hơn 7 ngày.
        - **Sắp hết hạn**: Hợp đồng "Hoạt động" với ngày kết thúc trong vòng 7 ngày (hoặc cấu hình `is_near_expiration`, mặc định 30 ngày).
        - **Đã hết hạn**: Hợp đồng có trạng thái "Kết thúc" hoặc ngày kết thúc trước ngày hiện tại.
    - Mỗi mục hiển thị:
        - Tên người dùng và tên phòng trọ.
        - Badge trạng thái (xanh lá, vàng, đỏ) tương ứng với tình trạng.
    - Hiển thị tối đa 3 người thuê mỗi danh sách, với nút "Xem tất cả" dẫn đến trang danh sách hợp đồng (lọc theo trạng thái "Hoạt động" hoặc "Kết thúc").
