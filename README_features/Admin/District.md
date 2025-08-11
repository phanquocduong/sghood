**1. Xem danh sách khu vực**

-   Quản trị viên có thể xem danh sách tất cả các khu vực (quận/huyện) hiện có trong hệ thống.
-   **Chi tiết**:
    -   Hiển thị danh sách khu vực với thông tin: tên khu vực, hình ảnh, số lượng nhà trọ liên quan.
    -   Hỗ trợ **phân trang**.
    -   Hỗ trợ **tìm kiếm** theo tên khu vực.
    -   Hỗ trợ **sắp xếp** theo các tiêu chí:
        -   Tên khu vực (A-Z, Z-A).
        -   Ngày tạo (tăng dần, giảm dần).
        -   Số lượng nhà trọ.
    -   Nếu không có khu vực nào, hiển thị thông báo lỗi: "Đã xảy ra lỗi khi lấy danh sách khu vực".

**2. Thêm khu vực mới**

-   Quản trị viên có thể tạo một khu vực mới với tên và hình ảnh đại diện.
-   **Chi tiết**:
    -   Nhập thông tin: **Tên khu vực** (bắt buộc, tối đa 100 ký tự) và **hình ảnh** (bắt buộc, định dạng jpeg, png, jpg, webp, tối đa 2MB).
    -   Hình ảnh được chuyển thành định dạng **webp** (chất lượng 85%) trước khi lưu.
    -   Nếu tạo thành công, hiển thị thông báo: "Khu vực đã được tạo thành công!" và chuyển hướng đến danh sách khu vực.
    -   Nếu lỗi (ví dụ: tải ảnh thất bại hoặc lỗi hệ thống), hiển thị thông báo lỗi cụ thể và giữ lại dữ liệu đã nhập.

**3. Sửa thông tin khu vực**

-   Quản trị viên có thể chỉnh sửa thông tin của một khu vực hiện có.
-   **Chi tiết**:
    -   Truy cập trang chỉnh sửa với thông tin khu vực được điền sẵn (tên và hình ảnh hiện tại).
    -   Có thể cập nhật: **Tên khu vực** (tùy chọn, tối đa 100 ký tự) và **hình ảnh** (tùy chọn, định dạng jpeg, png, jpg, webp, tối đa 2MB).
    -   Nếu cập nhật hình ảnh, hình ảnh cũ sẽ bị xóa khỏi hệ thống lưu trữ và thay bằng hình ảnh mới (chuyển thành webp).
    -   Nếu cập nhật thành công, hiển thị thông báo: "Khu vực đã được cập nhật thành công!" và chuyển hướng đến danh sách khu vực.
    -   Nếu lỗi (ví dụ: khu vực không tồn tại, tải ảnh thất bại), hiển thị thông báo lỗi và giữ lại dữ liệu đã nhập.

**4. Xóa khu vực (xóa mềm)**

-   Quản trị viên có thể xóa một khu vực, chuyển khu vực đó vào thùng rác.
-   **Chi tiết**:
    -   Kiểm tra khóa ngoại: Nếu khu vực có nhà trọ liên quan, hiển thị thông báo lỗi: "Không thể xóa khu vực này vì có nhà trọ liên quan".
    -   Nếu xóa thành công, hiển thị thông báo: "Khu vực đã được xóa thành công!" và chuyển hướng đến danh sách khu vực.
    -   Nếu khu vực không tồn tại, hiển thị thông báo lỗi: "Khu vực không tìm thấy".

**5. Xem danh sách khu vực trong thùng rác**

-   Quản trị viên có thể xem danh sách các khu vực đã bị xóa.
-   **Chi tiết**:
    -   Hiển thị danh sách khu vực trong thùng rác với thông tin: tên khu vực, hình ảnh, số lượng nhà trọ liên quan.
    -   Hỗ trợ **phân trang**.
    -   Hỗ trợ **tìm kiếm** theo tên khu vực và **sắp xếp** tương tự danh sách khu vực thông thường.
    -   Nếu không có khu vực nào trong thùng rác, hiển thị thông báo lỗi: "Đã xảy ra lỗi khi lấy danh sách khu vực".

**6. Xem chi tiết khu vực trong thùng rác**

-   Quản trị viên có thể xem thông tin chi tiết của một khu vực trong thùng rác.
-   **Chi tiết**:
    -   Hiển thị thông tin: tên khu vực, hình ảnh, số lượng nhà trọ liên quan.
    -   Nếu khu vực không tồn tại trong thùng rác, hiển thị thông báo lỗi: "Khu vực không tìm thấy trong thùng rác".

**7. Khôi phục khu vực từ thùng rác**

-   Quản trị viên có thể khôi phục một khu vực đã bị xóa để đưa nó trở lại danh sách khu vực hoạt động.
-   **Chi tiết**:
    -   Nếu khôi phục thành công, hiển thị thông báo: "Khu vực đã được khôi phục thành công!" và chuyển hướng đến danh sách thùng rác.
    -   Nếu khu vực không tồn tại trong thùng rác, hiển thị thông báo lỗi: "Khu vực không tìm thấy trong thùng rác".

**8. Xóa vĩnh viễn khu vực từ thùng rác**

-   Quản trị viên có thể xóa vĩnh viễn một khu vực trong thùng rác, xóa cả hình ảnh liên quan.
-   **Chi tiết**:
    -   Xóa hình ảnh của khu vực khỏi hệ thống lưu trữ trước khi xóa vĩnh viễn bản ghi.
    -   Nếu xóa thành công, hiển thị thông báo: "Khu vực đã được xóa vĩnh viễn!" và chuyển hướng đến danh sách thùng rác.
    -   Nếu khu vực không tồn tại trong thùng rác, hiển thị thông báo lỗi: "Khu vực không tìm thấy trong thùng rác".
