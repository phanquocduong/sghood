**1. Xem danh sách hợp đồng**

-   Quản trị viên có thể xem toàn bộ danh sách hợp đồng với thông tin chi tiết như số thứ tự, tên người thuê, tên phòng, ngày kết thúc, giá thuê, trạng thái hợp đồng và các hành động liên quan.
-   **Chi tiết**:
    -   Hiển thị danh sách hợp đồng dưới dạng bảng với giao diện trực quan, hỗ trợ phân trang (pagination).
    -   Hỗ trợ tìm kiếm hợp đồng theo tên người thuê hoặc tên phòng.
    -   Lọc hợp đồng theo trạng thái (ví dụ: Chờ xác nhận, Hoạt động, Kết thúc, v.v.).
    -   Sắp xếp hợp đồng theo thứ tự mới nhất hoặc cũ nhất.
    -   Nhấn vào tên người thuê để xem thông tin chi tiết (họ tên, email, số điện thoại, địa chỉ, ngày tạo tài khoản) qua modal.
    -   Nhấn vào tên phòng để chuyển hướng đến trang chi tiết phòng.

**2. Xem chi tiết hợp đồng**

-   Quản trị viên có thể xem thông tin chi tiết của một hợp đồng cụ thể.
-   **Chi tiết**:
    -   Hiển thị nội dung hợp đồng (HTML content).
    -   Hiển thị danh sách các phụ lục gia hạn hợp đồng (nếu có) với thông tin như ngày kết thúc mới, giá thuê mới, nội dung phụ lục và tệp đính kèm (nếu có).
    -   Xem hình ảnh căn cước công dân của người thuê qua carousel, hỗ trợ phóng to ảnh qua modal.
    -   Hiển thị trạng thái hiện tại của hợp đồng (với badge trực quan) và thời gian cập nhật gần nhất.
    -   Xem thông tin thời hạn hợp đồng (ngày ký, ngày hết hạn).

**3. Tải xuống hợp đồng dưới dạng PDF**

-   Quản trị viên có thể tải xuống tệp PDF của hợp đồng nếu đã được tạo.
-   **Chi tiết**:
    -   Nút tải PDF xuất hiện trong bảng danh sách hợp đồng và trang chi tiết hợp đồng, chỉ khả dụng nếu tệp PDF tồn tại.
    -   Hệ thống kiểm tra sự tồn tại của tệp PDF trước khi cho phép tải xuống.

**4. Cập nhật trạng thái hợp đồng**

-   Quản trị viên có thể thay đổi trạng thái của hợp đồng sang các trạng thái hợp lệ (Chờ xác nhận, Chờ duyệt, Chờ chỉnh sửa, Chờ ký, Hoạt động, Kết thúc, Huỷ bỏ).
-   **Chi tiết**:
    -   Cung cấp các nút trạng thái (status buttons) để thay đổi trạng thái trong trang chi tiết hợp đồng.
    -   Xác nhận trước khi thay đổi trạng thái để tránh thao tác nhầm.
    -   Tự động gửi thông báo email và thông báo đẩy đến người dùng khi trạng thái thay đổi (ví dụ: chuyển sang "Chờ ký" hoặc "Hoạt động").
    -   Khi trạng thái chuyển thành "Hoạt động", hệ thống tự động tạo tệp PDF cho hợp đồng.
    -   Khi trạng thái chuyển thành "Kết thúc", hệ thống:
        -   Xóa tài liệu căn cước công dân của người dùng khỏi hệ thống.
        -   Cập nhật trạng thái phòng thành "Sửa chữa".

**5. Yêu cầu chỉnh sửa hợp đồng**

-   Quản trị viên có thể yêu cầu người thuê chỉnh sửa thông tin của người dùng trong hợp đồng khi ở trạng thái "Chờ duyệt" hoặc "Chờ duyệt thủ công".
-   **Chi tiết**:
    -   Mở modal để nhập lý do yêu cầu chỉnh sửa.
    -   Gửi email thông báo yêu cầu chỉnh sửa đến người thuê (kèm lý do).
    -   Gửi thông báo email và thông báo đẩy đến người dùng.
    -   Cập nhật trạng thái hợp đồng thành "Chờ xác nhận".

**6. Chỉnh sửa thủ công nội dung hợp đồng**

-   Quản trị viên có thể chỉnh sửa trực tiếp thông tin người dùng trong nội dung hợp đồng khi ở trạng thái "Chờ duyệt" hoặc "Chờ duyệt thủ công".
-   **Chi tiết**:
    -   Kích hoạt chế độ chỉnh sửa thủ công qua nút "Chỉnh sửa thủ công".
    -   Cho phép chỉnh sửa các trường input trong nội dung hợp đồng (HTML).
    -   Cung cấp nút "Hủy bỏ" để hoàn tác và "Xác nhận" để lưu thay đổi.
    -   Lưu nội dung mới vào cơ sở dữ liệu và cập nhật trạng thái hợp đồng thành "Chờ ký".

**7. Kết thúc hợp đồng sớm**

-   Quản trị viên có thể kết thúc hợp đồng trước thời hạn khi hợp đồng ở trạng thái "Hoạt động".
-   **Chi tiết**:
    -   Mở modal để nhập lý do kết thúc sớm.
    -   Hiển thị các điều khoản kết thúc sớm.
    -   Gửi thông báo email và thông báo đẩy đến người thuê kèm lý do.
    -   Cập nhật trạng thái hợp đồng thành "Kết thúc sớm".
    -   Thực hiện các tác vụ:
        -   Xóa tài liệu căn cước công dân của người dùng.
        -   Cập nhật trạng thái phòng thành "Sửa chữa".

**8. Tự động kiểm tra và thông báo hợp đồng sắp hết hạn**

-   Hệ thống có cơ chế tự động kiểm tra các hợp đồng sắp hết hạn và gửi thông báo đến người thuê.
-   **Chi tiết**:
    -   Hệ thống chạy lịch trình hàng ngày lúc 8:00 sáng.
    -   Kiểm tra hợp đồng có trạng thái "Hoạt động" và ngày kết thúc trong vòng số ngày cấu hình (mặc định 15 ngày).
    -   Gửi thông báo qua email và thông báo đẩy với thông tin số ngày còn lại, tên phòng, tên nhà trọ, và liên kết đến chi tiết hợp đồng đến người dùng.

**12. Tự động kết thúc hợp đồng đã hoàn tất checkout**

-   Hệ thống có cơ chế tự động kết thúc các hợp đồng đã hết hạn hoặc có checkout hợp lệ.
-   **Chi tiết**:
    -   Hệ thống chạy lịch trình hàng ngày lúc 11:00 sáng.
    -   Kiểm tra các checkout có trạng thái kiểm kê là "Đã kiểm kê", trạng thái xác nhận của người dùng là "Đồng ý" và trạng thái hoàn tiền là "Đâ xử lý", và hợp đồng liên kết có trạng thái "Hoạt động" và đã hết hạn.
    -   Cập nhật trạng thái hợp đồng thành "Kết thúc".
    -   Cập nhật trạng thái phòng thành "Sửa chữa".
    -   Xóa tài liệu căn cước công dân của người dùng.
    -   Gửi thông báo email và thông báo đẩy đến người dùng
