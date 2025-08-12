1. **Xem danh sách thông báo**:

    - Quản trị viên có thể xem danh sách tất cả các thông báo dành cho mình (lọc theo vai trò "Quản trị viên") với thông tin:
        - **Số thứ tự (STT)**: Hiển thị vị trí bản ghi.
        - **Tiêu đề**: Hiển thị tiêu đề thông báo, in đậm với màu nổi bật.
        - **Nội dung**: Hiển thị nội dung rút gọn (giới hạn 50 ký tự).
        - **Thời gian**: Hiển thị ngày và giờ tạo.
    - Danh sách hỗ trợ **phân trang** (mặc định 10 bản ghi/trang).
    - Thông báo chưa đọc được làm nổi bật với màu nền khác (màu xanh nhạt).

2. **Lọc và tìm kiếm thông báo**:

    - Quản trị viên có thể lọc danh sách thông báo theo:
        - **Từ khóa tìm kiếm**: Tìm kiếm theo tiêu đề (LIKE query).
        - **Trạng thái**: Lọc theo trạng thái "Chưa đọc" hoặc "Đã đọc" qua dropdown.
        - **Sắp xếp**: Sắp xếp theo thời gian tạo (mới nhất hoặc cũ nhất).
    - Form lọc sử dụng **GET request**, giữ lại các tham số lọc khi chuyển trang.
    - Giao diện lọc trực quan với input tìm kiếm và dropdown trạng thái/sắp xếp, nút "Lọc" dạng rounded-pill.

3. **Xem chi tiết thông báo**:

    - Quản trị viên có thể xem chi tiết thông báo bằng cách nhấp vào một hàng trong danh sách, mở modal hiển thị:
        - **Tiêu đề**: Hiển thị đầy đủ.
        - **Nội dung**: Hiển thị toàn bộ nội dung thông báo.
    - Modal có giao diện đẹp với tiêu đề màu xanh, nút đóng, và nội dung rõ ràng.

4. **Đánh dấu thông báo là đã đọc**:

    - Khi nhấp vào thông báo chưa đọc, hệ thống tự động đánh dấu trạng thái từ "Chưa đọc" sang "Đã đọc".
    - Giao diện cập nhật tức thì:
        - Loại bỏ màu nền của thông báo chưa đọc.
        - Thay đổi trạng thái hiển thị thành "Đã đọc".

5. **Xem thông báo mới nhất ở header**:

    - Quản trị viên có thể xem:
        - **Số lượng thông báo chưa đọc**
        - **Danh sách 3 thông báo mới nhất**: Bao gồm tiêu đề, thời gian tạo, và trạng thái.
    - Nhấp vào thông báo trong header dẫn đến trang danh sách thông báo.
