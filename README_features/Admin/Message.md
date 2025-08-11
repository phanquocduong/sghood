1. **Xem danh sách người dùng có tin nhắn**:

    - Quản trị viên có thể xem danh sách tất cả người dùng đã từng nhắn tin với mình.
    - Mỗi người dùng được hiển thị kèm theo:
        - **Tên người dùng** và **avatar**.
        - **Số lượng tin nhắn chưa đọc** (hiển thị dưới dạng badge màu đỏ), giúp quản trị viên dễ dàng nhận biết các cuộc trò chuyện cần chú ý.

2. **Xem tổng số tin nhắn chưa đọc**:

    - Hệ thống hiển thị tổng số tin nhắn chưa đọc từ tất cả người dùng, giúp quản trị viên nắm bắt nhanh mức độ công việc cần xử lý.

3. **Xem nội dung cuộc trò chuyện với một người dùng**:

    - Quản trị viên có thể chọn một người dùng từ danh sách để xem toàn bộ lịch sử tin nhắn với người đó.
    - Tin nhắn hiển thị theo thứ tự thời gian (từ cũ đến mới).
    - Giao diện phân biệt rõ ràng giữa:
        - Tin nhắn do quản trị viên gửi (nền xanh, căn phải).
        - Tin nhắn nhận được từ người dùng (nền xám, căn trái).
    - Hỗ trợ hiển thị cả tin nhắn văn bản và hình ảnh (nếu có).

4. **Gửi tin nhắn cho người dùng**:

    - Quản trị viên có thể gửi tin nhắn văn bản (tối đa 1000 ký tự) cho bất kỳ người dùng nào được chọn.
    - Tính năng gửi tin nhắn được tích hợp với kiểm tra đầu vào (validation):
        - Đảm bảo người nhận tồn tại trong hệ thống.
        - Tin nhắn không được để trống và không vượt quá giới hạn ký tự.
        - Thông báo lỗi rõ ràng (bằng tiếng Việt) nếu dữ liệu nhập không hợp lệ.

5. **Đánh dấu tin nhắn đã đọc**:

    - Khi quản trị viên chọn một người dùng để xem cuộc trò chuyện, hệ thống tự động đánh dấu các tin nhắn từ người dùng đó là **đã đọc**.

6. **Xem thông báo tin nhắn chưa đọc ở header**:

    - Quản trị viên có thể xem nhanh:
        - **Số lượng tin nhắn chưa đọc** tổng cộng.
        - **Danh sách 3 tin nhắn chưa đọc gần nhất** (bao gồm nội dung ngắn gọn, thời gian gửi, và liên kết để xem chi tiết).
    - Thời gian gửi tin nhắn được hiển thị dưới dạng tương đối (ví dụ: "5 phút trước") để dễ hiểu.

7. **Tích hợp Firebase để lưu trữ và đồng bộ tin nhắn**:

    - Tất cả tin nhắn (bao gồm văn bản và hình ảnh) được lưu trữ trên **Firestore**, đảm bảo tính bền vững và khả năng mở rộng.
    - Dữ liệu tin nhắn được đồng bộ theo thời gian thực, hỗ trợ quản trị viên nhận thông báo hoặc cập nhật tin nhắn ngay lập tức.

8. **Giao diện thân thiện và dễ sử dụng**:

    - Giao diện được chia thành hai phần:
        - **Danh sách người dùng** (bên trái): Hiển thị danh sách người dùng với badge số tin nhắn chưa đọc.
        - **Khung chat** (bên phải): Hiển thị lịch sử tin nhắn và form gửi tin nhắn.
    - Giao diện có khả năng cuộn (scrollable) để xem danh sách người dùng dài hoặc lịch sử tin nhắn dài.
    - Hỗ trợ thông báo thành công/lỗi (success/error) khi gửi tin nhắn hoặc gặp vấn đề (ví dụ: file ảnh quá lớn).
