1. **Xem danh sách lịch xem trọ**:

    - Quản trị viên có thể xem danh sách tất cả lịch xem trọ với thông tin:
        - **Số thứ tự (STT)**: Hiển thị thứ tự bản ghi trong trang hiện tại.
        - **Người dùng**: Tên người dùng với liên kết để xem chi tiết thông tin (mở modal).
        - **Dãy trọ**: Tên nhà trọ với liên kết đến trang chi tiết nhà trọ (mở tab mới).
        - **Ngày xem phòng**: Thời gian đặt lịch.
        - **Lời nhắn của người dùng**: Nội dung lời nhắn (nếu có, hoặc "N/A").
        - **Trạng thái**: Hiển thị trạng thái ("Chờ xác nhận", "Đã xác nhận", "Từ chối", "Huỷ bỏ", "Hoàn thành") với badge màu tương ứng:
            - Chờ xác nhận: Xanh dương.
            - Đã xác nhận: Vàng.
            - Hoàn thành: Xanh lá.
            - Từ chối: Xám đậm.
            - Huỷ bỏ: Đỏ.
        - **Hành động**: Dropdown chọn trạng thái mới, tự động gửi yêu cầu khi thay đổi (trừ trạng thái "Từ chối", "Hoàn thành", "Huỷ bỏ" bị khóa).
    - Hỗ trợ **phân trang** (mặc định 10 bản ghi/trang).

2. **Lọc và tìm kiếm lịch xem trọ**:

    - Quản trị viên có thể lọc danh sách lịch xem trọ theo:
        - **Từ khóa tìm kiếm**: Tìm kiếm theo tên người dùng, email, lời nhắn, tên nhà trọ, hoặc mô tả nhà trọ.
        - **Trạng thái**: Lọc theo trạng thái ("Chờ xác nhận", "Đã xác nhận", "Từ chối", "Hoàn thành", "Huỷ bỏ") qua dropdown.
        - **Sắp xếp**: Sắp xếp theo ngày tạo (mới nhất/cũ nhất).
    - Form lọc sử dụng **GET request**, giữ lại các tham số lọc khi chuyển trang.
    - Giao diện lọc trực quan với input tìm kiếm, dropdown trạng thái/sắp xếp, và nút "Tìm" có biểu tượng kính lúp.

3. **Xem chi tiết thông tin người dùng**:

    - Quản trị viên có thể nhấp vào tên người dùng để xem thông tin chi tiết trong modal:
        - **Avatar**: Hình đại diện (hoặc ảnh mặc định nếu không có).
        - **ID**: Mã người dùng.
        - **Họ tên**: Tên đầy đủ.
        - **Email**: Địa chỉ email.
        - **Số điện thoại**: Số điện thoại (hoặc "Chưa cập nhật").
        - **Địa chỉ**: Địa chỉ (hoặc "Chưa cập nhật").
        - **Ngày tạo**: Thời gian tạo tài khoản (định dạng `d/m/Y H:i`).
    - Modal có giao diện đẹp với nền gradient, ảnh đại diện dạng tròn, và hiệu ứng hover trên liên kết người dùng.
    - Xử lý lỗi tải avatar bằng cách hiển thị ảnh mặc định.

4. **Cập nhật trạng thái lịch xem trọ**:

    - Quản trị viên có thể thay đổi trạng thái lịch xem trọ thông qua dropdown:
        - Từ **Chờ xác nhận** → "Đã xác nhận" hoặc "Từ chối".
        - Từ **Đã xác nhận** → "Hoàn thành".
        - Các trạng thái "Từ chối", "Hoàn thành", "Huỷ bỏ" sẽ khóa dropdown để không cho phép thay đổi thêm.
    - Khi chọn "Từ chối" hoặc "Huỷ bỏ", modal hiện ra yêu cầu nhập lý do (tối đa 500 ký tự).
    - Hiển thị lý do từ chối (nếu có) ngay trong cột trạng thái.
    - Hiển thị thông báo thành công/lỗi sau khi cập nhật trạng thái.
    - Hệ thống gửi thông báo email và thông báo đẩy đến người dùng.
