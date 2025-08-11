1. **Xem danh sách bài viết**

    - Quản trị viên truy cập trang chính để xem danh sách bài viết với thông tin: STT, Hình ảnh (thumbnail), Tiêu đề, Số lượng bình luận, Tác giả, Thể loại, Ngày đăng, và các nút hành động.
    - Hỗ trợ tìm kiếm theo tiêu đề hoặc tên tác giả.
    - Lọc theo trạng thái (Tất cả/Nháp/Đã xuất bản).
    - Sắp xếp theo: Ngày tạo (mới nhất/cũ nhất).
    - Phân trang với tùy chọn số lượng bài viết mỗi trang (mặc định 10).
    - Hiển thị số lượng bình luận và liên kết đến trang quản lý bình luận.

2. **Xem chi tiết bài viết**

    - Nhấp vào tiêu đề bài viết trong danh sách để xem chi tiết: Tiêu đề, Ngày đăng, và Nội dung đầy đủ (định dạng HTML).
    - Có nút quay lại danh sách hoặc chuyển sang chỉnh sửa bài viết.

3. **Thêm mới bài viết**

    - Truy cập form tạo bài viết để nhập: Tiêu đề (tối đa 255 ký tự), Nội dung, Hình ảnh (thumbnail, hỗ trợ jpg/jpeg/png/webp, tối đa 2MB), Thể loại (Tin tức/Hướng dẫn/Khuyến mãi/Pháp luật/Kinh nghiệm), Trạng thái (Nháp/Đã xuất bản).
    - Tác giả được tự động gán là quản trị viên đang đăng nhập.
    - Hỗ trợ xem trước hình ảnh thumbnail ngay khi chọn file.
    - Xác thực dữ liệu: Bắt buộc nhập tiêu đề, nội dung, hình ảnh, và kiểm tra định dạng file, kích thước. Hiển thị thông báo lỗi cụ thể nếu sai.
    - Hình ảnh được chuyển đổi sang định dạng webp để tối ưu kích thước.
    - Sau khi lưu, chuyển về danh sách bài viết với thông báo thành công.

4. **Chỉnh sửa bài viết**

    - Chọn bài viết từ danh sách để mở form chỉnh sửa với dữ liệu hiện tại: Tiêu đề, Nội dung, Hình ảnh, Trạng thái, Tác giả (mặc định là quản trị viên hiện tại).
    - Hỗ trợ thay đổi thumbnail với xem trước tức thời, nhưng không bắt buộc nếu không muốn cập nhật hình ảnh mới.
    - Xác thực dữ liệu tương tự tạo mới, đảm bảo tiêu đề và nội dung không để trống, hình ảnh hợp lệ nếu được tải lên.
    - Sau khi cập nhật, chuyển về danh sách với thông báo thành công.
    - Lợi ích: Linh hoạt chỉnh sửa nội dung, giữ nguyên hình ảnh cũ nếu không thay đổi, đảm bảo tính nhất quán và dễ sử dụng.

5. **Cập nhật thể loại bài viết trực tiếp từ danh sách**

    - Trong danh sách bài viết, chọn thể loại mới (Tin tức/Hướng dẫn/Khuyến mãi/Pháp luật/Kinh nghiệm) từ dropdown và xác nhận thay đổi.
    - Hiển thị thông báo thành công sau khi cập nhật.
    - Lợi ích: Nhanh chóng phân loại bài viết mà không cần vào form chỉnh sửa, tiện lợi cho quản lý nội dung theo danh mục.

6. **Xóa bài viết (xóa tạm thời)**

    - Từ danh sách, chọn nút xóa và xác nhận qua hộp thoại.
    - Bài viết được chuyển vào thùng rác (soft delete), không xóa vĩnh viễn ngay.
    - Hiển thị thông báo thành công sau khi xóa.

7. **Xem danh sách bài viết trong thùng rác**

    - Truy cập trang thùng rác để xem danh sách bài viết đã xóa: STT, Hình ảnh, Tiêu đề, Tác giả, Ngày đăng.
    - Hỗ trợ tìm kiếm theo tiêu đề.
    - Sắp xếp theo: Ngày tạo (mới nhất/cũ nhất).
    - Phân trang để quản lý danh sách dài.

8. **Khôi phục bài viết từ thùng rác**

    - Từ thùng rác, chọn nút khôi phục và xác nhận.
    - Bài viết được đưa về danh sách chính, giữ nguyên thông tin gốc.
    - Hiển thị thông báo thành công và quay về trang thùng rác.

9. **Xóa vĩnh viễn bài viết từ thùng rác**
    - Từ thùng rác, chọn nút xóa vĩnh viễn và xác nhận.
    - Bài viết bị xóa hoàn toàn khỏi cơ sở dữ liệu.
    - Hiển thị thông báo thành công.
