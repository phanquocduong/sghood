1. **Xem danh sách nhà trọ**:

    - Quản trị viên có thể xem danh sách tất cả các nhà trọ với thông tin:
        - **Số thứ tự (Stt)**.
        - **Ảnh chính** (hiển thị ảnh chính hoặc ảnh đầu tiên nếu không có ảnh chính, mặc định là ảnh placeholder nếu không có ảnh).
        - **Tên nhà trọ** (liên kết đến trang chi tiết).
        - **Địa chỉ**.
        - **Số lượng phòng** (bao gồm số phòng trống và đang sửa chữa, hiển thị dưới dạng badge).
        - **Trạng thái** (Hoạt động/Không hoạt động, với badge màu xanh/đỏ).
    - Danh sách hỗ trợ **phân trang** (mặc định 10 bản ghi/trang) với giao diện Bootstrap-5, dễ điều hướng.

2. **Lọc và tìm kiếm nhà trọ**:

    - Quản trị viên có thể lọc danh sách nhà trọ theo:
        - **Từ khóa tìm kiếm** (tên nhà trọ hoặc địa chỉ).
        - **Khu vực** (quận/huyện, chọn từ danh sách dropdown).
        - **Trạng thái** (Hoạt động/Không hoạt động).
        - **Sắp xếp** (theo tên A-Z/Z-A, ngày tạo mới nhất/cũ nhất).
    - Form lọc sử dụng **GET request**, giữ lại các tham số lọc khi chuyển trang.
    - Giao diện lọc trực quan với input tìm kiếm, dropdown khu vực/trạng thái, và nút "Lọc".

3. **Xem chi tiết nhà trọ**:

    - Quản trị viên có thể xem thông tin chi tiết của một nhà trọ bằng cách nhấp vào tên nhà trọ, bao gồm:
        - **Thông tin cơ bản**: Tên, địa chỉ, quận/huyện, trạng thái, mô tả.
        - **Chi phí dịch vụ**: Phí điện (VNĐ/kWh), nước (VNĐ/m³), giữ xe, rác, internet, dịch vụ (định dạng tiền tệ VNĐ, kèm icon trực quan).
        - **Tiện ích**: Danh sách tiện ích (như Wi-Fi, điều hòa) hiển thị dưới dạng badge với icon.
        - **Bản đồ**: Hiển thị bản đồ nhúng (iframe) từ URL Google Maps.
        - **Thư viện ảnh**: Hiển thị ảnh chính (với badge "Ảnh chính") và các ảnh phụ (hiển thị số thứ tự).
    - Giao diện chi tiết sử dụng layout chia cột, tối ưu với các section rõ ràng (thông tin, chi phí, tiện ích, bản đồ, ảnh).

4. **Thêm nhà trọ mới**:

    - Quản trị viên có thể tạo nhà trọ mới thông qua form với các trường:
        - **Tên nhà trọ** (bắt buộc, duy nhất).
        - **Địa chỉ**, **quận/huyện**, **URL nhúng bản đồ** (bắt buộc).
        - **Mô tả** (tùy chọn).
        - **Chi phí**: Phí điện, nước, giữ xe, rác, internet, dịch vụ (bắt buộc, không âm).
        - **Trạng thái** (Hoạt động/Không hoạt động, mặc định là Hoạt động).
        - **Tiện ích**: Chọn nhiều tiện ích từ danh sách checkbox (lọc theo loại "Nhà trọ").
        - **Hình ảnh**: Tải lên nhiều ảnh (tối đa 20, định dạng JPEG, PNG, GIF, WebP, dung lượng tối đa 2MB), chọn ảnh chính qua FilePond preview.
    - Hỗ trợ **FilePond** để tải và xem trước ảnh, với giao diện kéo-thả và chọn ảnh chính trực quan.
    - **Validation** phía server (thông qua `MotelRequest`) và client, hiển thị lỗi cụ thể nếu nhập sai.
    - Tạo **slug** tự động từ tên nhà trọ, đảm bảo duy nhất.

5. **Sửa thông tin nhà trọ**:

    - Quản trị viên có thể chỉnh sửa thông tin nhà trọ với form tương tự tạo mới, nhưng điền sẵn dữ liệu hiện tại.
    - Hỗ trợ:
        - **Cập nhật ảnh hiện tại**: Chọn ảnh chính từ các ảnh hiện có (radio button) hoặc xóa ảnh (nút xóa AJAX).
        - **Thêm ảnh mới**: Tải ảnh mới qua FilePond, chọn ảnh chính từ ảnh mới.
        - **Đồng bộ tiện ích**: Cập nhật danh sách tiện ích được chọn.
    - Kiểm tra tên nhà trọ duy nhất (bỏ qua ID hiện tại), tự động cập nhật slug nếu tên thay đổi.
    - Đảm bảo luôn có ít nhất một ảnh chính (nếu xóa hoặc không chọn ảnh chính).
    - Hiển thị thông báo thành công hoặc lỗi sau khi cập nhật.

6. **Xóa nhà trọ (soft delete)**:

    - Quản trị viên có thể xóa nhà trọ, chuyển vào **thùng rác** (soft delete).
    - Kiểm tra ràng buộc khóa ngoại: Không cho xóa nếu nhà trọ có phòng liên quan.
    - Yêu cầu xác nhận trước khi xóa (JavaScript confirm).
    - Hiển thị thông báo thành công/lỗi sau khi xóa.

7. **Quản lý thùng rác**:

    - Quản trị viên có thể xem danh sách nhà trọ đã xóa (trong thùng rác) với thông tin:
        - Số thứ tự, ảnh chính, tên, mô tả (giới hạn 50 ký tự), số lượng phòng, trạng thái.
    - Hỗ trợ **phân trang** và giao diện bảng tương tự danh sách nhà trọ chính.
    - Cho phép:
        - **Khôi phục nhà trọ**: Đưa nhà trọ về trạng thái hoạt động, yêu cầu xác nhận.
        - **Xóa vĩnh viễn**: Xóa hoàn toàn nhà trọ và các ảnh liên quan, yêu cầu xác nhận.
    - Hiển thị thông báo thành công/lỗi sau mỗi thao tác.

8. **Xóa ảnh nhà trọ**:

    - Quản trị viên có thể xóa từng ảnh của nhà trọ (trong form chỉnh sửa) thông qua nút xóa (AJAX).
    - Xóa file ảnh vật lý trên server và bản ghi trong cơ sở dữ liệu.
    - Nếu xóa ảnh chính, hệ thống tự động chọn ảnh khác làm ảnh chính (nếu còn ảnh).
    - Trả về phản hồi với thông báo thành công/lỗi.

9. **Quản lý tiện ích và khu vực**:

    - **Tiện ích**: Quản trị viên có thể chọn danh sách tiện ích khi tạo/sửa nhà trọ, chỉ hiển thị tiện ích thuộc loại "Nhà trọ".
    - **Khu vực**: Chọn quận/huyện từ danh sách dropdown, đảm bảo quận/huyện tồn tại trong cơ sở dữ liệu.
