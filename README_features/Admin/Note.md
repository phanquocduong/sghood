1. **Xem danh sách ghi chú**:

    - Quản trị viên có thể xem danh sách tất cả các ghi chú với thông tin:
        - **Số thứ tự (STT)**: Hiển thị vị trí bản ghi trong trang hiện tại.
        - **Nội dung**: Hiển thị nội dung ghi chú kèm biểu tượng sticky note.
        - **Loại ghi chú**: Hiển thị dưới dạng badge (ví dụ: "Ghi chú cá nhân").
        - **Người viết**: Hiển thị tên người dùng tạo ghi chú, in đậm với màu nổi bật.
        - **Ngày tạo**: Hiển thị ngày và giờ tạo (theo múi giờ Asia/Ho_Chi_Minh, định dạng d/m/Y và H:i).
    - Danh sách hỗ trợ **phân trang** (mặc định 10 bản ghi/trang), dễ điều hướng.
    - Hiển thị tổng số ghi chú dưới dạng badge ở tiêu đề (ví dụ: "10 ghi chú").

2. **Lọc và tìm kiếm ghi chú**:

    - Quản trị viên có thể lọc danh sách ghi chú theo:
        - **Từ khóa tìm kiếm**: Tìm kiếm theo nội dung ghi chú (LIKE query).
        - **Người dùng**: Lọc theo người tạo ghi chú, chọn từ danh sách dropdown (chỉ hiển thị người dùng có trạng thái "Hoạt động" và có ghi chú).
        - **Loại ghi chú**: Lọc theo loại ghi chú, chọn từ danh sách dropdown (các loại ghi chú hiện có).
        - **Sắp xếp**: Sắp xếp theo nội dung (A-Z/Z-A), loại ghi chú (A-Z/Z-A), tên người dùng (A-Z/Z-A), hoặc ngày tạo (mới nhất/cũ nhất).
    - Form lọc sử dụng **GET request**, giữ lại các tham số lọc khi chuyển trang.
    - Giao diện lọc trực quan với input tìm kiếm, dropdown người dùng/loại/sắp xếp, tự động gửi form khi chọn bộ lọc.

3. **Xem chi tiết ghi chú**:

    - Quản trị viên có thể xem chi tiết một ghi chú bằng cách nhấp vào bản ghi, bao gồm:
        - **Nội dung ghi chú**, **loại ghi chú**, **người viết**, và **thời gian tạo**.
        - Liên kết với thông tin người dùng (tên, trạng thái).
    - Kiểm tra quyền truy cập: Chỉ cho phép quản trị viên xem ghi chú của chính mình.
    - Hiển thị thông báo lỗi nếu ghi chú không tồn tại hoặc không có quyền truy cập.

4. **Thêm ghi chú mới**:

    - Quản trị viên có thể tạo ghi chú mới thông qua modal với các trường:
        - **Nội dung**: Bắt buộc, tối đa 255 ký tự, hiển thị textarea với placeholder.
        - **Loại ghi chú**: Chọn từ danh sách loại hiện có hoặc nhập loại mới (tối đa 50 ký tự).
    - Hỗ trợ nhập loại ghi chú tùy chỉnh thông qua input text (hiển thị khi chọn "Khác" trong dropdown).
    - **Validation** phía client và server:
        - Kiểm tra nội dung và loại không được để trống, đúng định dạng, và giới hạn ký tự.
        - Hiển thị thông báo lỗi cụ thể nếu nhập sai (ví dụ: "Nội dung ghi chú không được vượt quá 255 ký tự").
    - Ghi chú tự động gán với quản trị viên đang đăng nhập.
    - Hiển thị thông báo thành công sau khi tạo, quay lại danh sách ghi chú.

5. **Xóa ghi chú**:

    - Quản trị viên có thể xóa ghi chú thông qua nút "Xóa" trong danh sách.
    - Yêu cầu xác nhận trước khi xóa (JavaScript confirm).
    - Kiểm tra quyền xóa: Chỉ cho phép xóa ghi chú của chính quản trị viên.
    - Hiển thị thông báo thành công/lỗi sau khi xóa.
