1. **Xem danh sách người dùng**:

    - Quản trị viên có thể xem danh sách tất cả người dùng với thông tin:
        - **STT**: Số thứ tự bản ghi trong trang hiện tại.
        - **Họ tên**: Tên người dùng (liên kết để xem chi tiết qua modal).
        - **Số điện thoại**: Số điện thoại của người dùng.
        - **Email**: Email của người dùng.
        - **Ngày đăng ký**: Ngày tạo tài khoản.
        - **Vai trò**: Vai trò của người dùng (Người đăng ký, Người thuê, Quản trị viên, Super admin), hiển thị dưới dạng dropdown để chỉnh sửa.
        - **Trạng thái**: Trạng thái của người dùng (Hoạt động, Khoá), hiển thị dưới dạng dropdown hoặc badge (xanh lá cho Hoạt động, đỏ cho Khoá).
    - Hỗ trợ **phân trang** (mặc định 25 bản ghi/trang).
    - Thông báo "Không tìm thấy người dùng phù hợp" nếu không có kết quả.

2. **Lọc và tìm kiếm người dùng**:

    - Quản trị viên có thể lọc danh sách người dùng theo:
        - **Tìm kiếm**: Tìm kiếm theo tên (`name`) hoặc email (`email`) .
        - **Vai trò**: Lọc theo vai trò (Người đăng ký, Người thuê, Quản trị viên) qua dropdown.
        - **Trạng thái**: Lọc theo trạng thái (Hoạt động, Khoá) qua dropdown.
        - **Sắp xếp**: Sắp xếp theo:
            - Tên (A → Z hoặc Z → A).
            - Ngày tạo (tăng dần hoặc giảm dần).
            - Mặc định: Sắp xếp theo ngày tạo giảm dần.
    - Form lọc sử dụng **GET request**, giữ lại các tham số lọc khi chuyển trang.
    - Giao diện lọc trực quan với input tìm kiếm (có biểu tượng kính lúp), dropdown vai trò/trạng thái/sắp xếp, và nút "Tìm" có hiệu ứng hover.

3. **Xem chi tiết người dùng**:

    - Quản trị viên có thể nhấp vào tên người dùng để mở modal hiển thị thông tin chi tiết qua **AJAX**:
        - **Avatar**: Hình đại diện (nếu có, hoặc ảnh mặc định).
        - **Họ tên**: Tên người dùng với định dạng gradient màu.
        - **Email**, **Số điện thoại**, **Vai trò**, **Trạng thái**, **Ngày tạo**, **Địa chỉ**, **Ngày sinh**, **Giới tính**.
    - Modal có giao diện đẹp với header gradient, avatar tròn có viền trắng và trạng thái online (chấm xanh), và thông tin được trình bày trong các card với hiệu ứng hover.
    - Hiển thị spinner loading trong khi tải dữ liệu qua AJAX, đảm bảo trải nghiệm mượt mà.

4. **Cập nhật vai trò người dùng**:

    - Quản trị viên có thể thay đổi vai trò của người dùng trực tiếp từ danh sách (qua dropdown):
        - Các vai trò: Người đăng ký, Người thuê, Quản trị viên (Super admin chỉ hiển thị nếu người dùng đã là Super admin).
        - **Quy tắc**:
            - Quản trị viên không thể tự sửa vai trò của chính mình.
            - Quản trị viên không thể sửa vai trò của Quản trị viên hoặc Super admin.
            - Quản trị viên không thể gán vai trò Super admin.
            - Người thuê chỉ có thể chuyển thành Quản trị viên.
            - Quản trị viên chỉ có thể chuyển thành Người đăng ký.
        - Xác nhận thay đổi vai trò bằng hộp thoại xác nhận (JavaScript `confirm`).
    - Hiển thị thông báo thành công hoặc lỗi sau khi cập nhật.

5. **Cập nhật trạng thái người dùng**:

    - Quản trị viên có thể thay đổi trạng thái của người dùng (Hoạt động, Khoá) từ danh sách (qua dropdown):
        - **Quy tắc**:
            - Quản trị viên không thể tự khóa chính mình.
            - Quản trị viên thường không thể khóa Quản trị viên hoặc Super admin.
            - Super admin có toàn quyền thay đổi trạng thái của bất kỳ người dùng nào.
        - Xác nhận thay đổi trạng thái bằng hộp thoại xác nhận (JavaScript `confirm`).
    - Nếu không có quyền chỉnh sửa, trạng thái hiển thị dưới dạng badge (xanh lá cho Hoạt động, đỏ cho Khoá).
    - Hiển thị thông báo thành công hoặc lỗi sau khi cập nhật.
