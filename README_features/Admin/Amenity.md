1. **Xem danh sách tiện ích đang hoạt động**

    - Truy cập trang chính để xem toàn bộ danh sách tiện ích với thông tin: STT, Tên, Loại (Nhà trọ/Phòng trọ), Thứ tự sắp xếp, Trạng thái (Hoạt động/Không hoạt động).
    - Hỗ trợ tìm kiếm theo tên tiện ích.
    - Lọc theo loại tiện ích (Tất cả/Nhà trọ/Phòng trọ).
    - Sắp xếp theo các tiêu chí: Tên (A-Z/Z-A), Trạng thái (Hoạt động trước/Không hoạt động trước), Thứ tự (tăng/giảm), Ngày tạo (cũ nhất/mới nhất).
    - Phân trang để dễ quản lý danh sách dài.

2. **Thêm mới tiện ích**

    - Truy cập form tạo mới để nhập thông tin: Tên tiện ích (bắt buộc, duy nhất), Loại (Nhà trọ/Phòng trọ), Trạng thái (Hoạt động/Không hoạt động).
    - Thứ tự sắp xếp được tự động gán (lớn nhất +1 trong loại tương ứng).
    - Xác thực dữ liệu (ví dụ: tên không quá 100 ký tự, loại phải hợp lệ) và hiển thị thông báo lỗi nếu sai.
    - Sau khi lưu, quay về danh sách với thông báo thành công.

3. **Chỉnh sửa thông tin tiện ích**

    - Chọn tiện ích từ danh sách để mở form chỉnh sửa, hiển thị dữ liệu hiện tại.
    - Cập nhật: Tên, Loại, Thứ tự sắp xếp (thủ công, tối thiểu 1), Trạng thái.
    - Nếu thay đổi loại hoặc order, hệ thống tự động điều chỉnh order của các tiện ích khác để đảm bảo thứ tự liên tục và không trùng.
    - Xác thực dữ liệu tương tự tạo mới, hiển thị lỗi nếu cần.
    - Sau cập nhật, quay về danh sách với thông báo thành công.

4. **Xóa tiện ích (xóa tạm thời)**

    - Từ danh sách, chọn nút xóa và xác nhận qua hộp thoại.
    - Kiểm tra ràng buộc: Không cho xóa nếu tiện ích đang được sử dụng trong nhà trọ hoặc phòng (hiển thị thông báo lỗi chi tiết).
    - Sau xóa, tiện ích chuyển vào thùng rác, và order của các tiện ích còn lại được tự động sắp xếp lại.
    - Hiển thị thông báo thành công.

5. **Xem danh sách tiện ích trong thùng rác**

    - Truy cập trang thùng rác để xem danh sách tiện ích đã xóa: STT, Tên, Loại, Thứ tự, Trạng thái.
    - Hỗ trợ tìm kiếm theo tên.
    - Sắp xếp theo: Tên (A-Z/Z-A), Ngày tạo (cũ nhất/mới nhất).
    - Phân trang cho danh sách dài.

6. **Khôi phục tiện ích từ thùng rác**

    - Từ thùng rác, chọn nút khôi phục và xác nhận.
    - Tiện ích được đưa về danh sách hoạt động, với order tự động gán (lớn nhất +1 trong loại tương ứng).
    - Hiển thị thông báo thành công và quay về trang thùng rác.

7. **Xóa vĩnh viễn tiện ích từ thùng rác**

    - Từ thùng rác, chọn nút xóa vĩnh viễn và xác nhận.
    - Dữ liệu bị xóa hoàn toàn khỏi cơ sở dữ liệu.
    - Hiển thị thông báo thành công.

8. **Thay đổi thứ tự sắp xếp tiện ích**
    - Truy cập trang riêng để xem danh sách tiện ích nhóm theo loại (Nhà trọ/Phòng trọ).
    - Hỗ trợ lọc theo loại và tìm kiếm theo tên.
    - Sử dụng kéo-thả (drag and drop) để thay đổi thứ tự hiển thị trong từng loại.
    - Thứ tự được lưu tự động sau khi thả, với thông báo thành công (sử dụng Ajax để không tải lại trang).
    - Hiển thị thứ tự hiện tại và cập nhật ngay lập tức sau thay đổi.
