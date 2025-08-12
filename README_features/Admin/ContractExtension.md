**1. Xem danh sách yêu cầu gia hạn hợp đồng**

-   Quản trị viên có thể xem toàn bộ danh sách các yêu cầu gia hạn hợp đồng với thông tin chi tiết.
-   **Chi tiết**:
    -   Hiển thị danh sách dưới dạng bảng với các cột: STT, Mã hợp đồng (HD), Ngày kết thúc mới, Giá thuê mới, Chi tiết nội dung, Lý do từ chối (nếu có), và Trạng thái.
    -   Hỗ trợ **tìm kiếm** theo mã hợp đồng (hỗ trợ tìm kiếm chính xác với tiền tố "HD" hoặc số hợp đồng).
    -   **Lọc** theo trạng thái (Tất cả, Chờ duyệt, Hoạt động, Từ chối, Huỷ bỏ).
    -   **Sắp xếp** theo thời gian tạo (Mới nhất hoặc Cũ nhất).
    -   Hiển thị tổng số yêu cầu gia hạn bằng badge trực quan.
    -   Hỗ trợ **phân trang** (mỗi trang 15 bản ghi) với giao diện Bootstrap 5.
    -   Nhấn vào mã hợp đồng để chuyển hướng đến trang chi tiết hợp đồng.
    -   Nhấn vào "Xem chi tiết" để mở modal hiển thị nội dung yêu cầu gia hạn.

**2. Cập nhật trạng thái yêu cầu gia hạn hợp đồng**

-   Quản trị viên có thể thay đổi trạng thái của yêu cầu gia hạn hợp đồng sang "Hoạt động" hoặc "Từ chối".
-   **Chi tiết**:
    -   **Chấp nhận (Hoạt động)**:
        -   Cập nhật trạng thái yêu cầu gia hạn thành "Hoạt động".
        -   Tự động cập nhật hợp đồng gốc với ngày kết thúc mới (`new_end_date`) và giá thuê mới (`new_rental_price`) cho hợp đồng.
        -   Gửi thông báo qua email và thông báo đẩy đến người thuê với nội dung "Yêu cầu gia hạn hợp đồng đã được phê duyệt".
        -   Xác nhận hành động qua hộp thoại xác nhận để tránh thao tác nhầm.
    -   **Từ chối**:
        -   Mở modal để nhập lý do từ chối (bắt buộc, tối đa 255 ký tự).
        -   Cập nhật trạng thái yêu cầu gia hạn thành "Từ chối" và lưu lý do từ chối.
        -   Gửithông báo qua email và thông báo đẩy đến người thuê với nội dung "Yêu cầu gia hạn hợp đồng bị từ chối" kèm lý do.
    -   Giao diện hiển thị trạng thái bằng badge màu (Chờ duyệt: vàng, Hoạt động: xanh, Từ chối/Huỷ bỏ: đỏ).
