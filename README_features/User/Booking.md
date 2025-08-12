1. **Đặt phòng trọ**:

    - **Truy cập chức năng đặt phòng**: Người dùng có thể đặt phòng trọ từ trang "Lịch xem nhà trọ" khi lịch xem đạt trạng thái "Hoàn thành" bằng cách nhấp vào nút "Đặt phòng" trên danh sách lịch xem.
    - **Form đặt phòng**:
        - **Chọn phòng**: Người dùng chọn phòng từ danh sách các phòng trống của nhà trọ. Danh sách phòng được hiển thị trong dropdown với giao diện thân thiện, sử dụng thư viện Chosen để hỗ trợ tìm kiếm và chọn lọc dễ dàng.
        - **Chọn ngày bắt đầu**: Người dùng chọn ngày bắt đầu thuê thông qua lịch (sử dụng thư viện daterangepicker) với giao diện hỗ trợ tiếng Việt (ngày, tháng, nút xác nhận/hủy). Ngày bắt đầu phải từ ngày mai trở đi (hệ thống tự động giới hạn ngày tối thiểu là ngày hiện tại cộng thêm 2 ngày).
        - **Chọn thời gian thuê**: Người dùng chọn thời gian thuê từ danh sách các khoảng thời gian được cấu hình sẵn (ví dụ: "1 năm", "2 năm", "3 năm", "4 năm", "5 năm") thông qua dropdown.
        - **Thêm ghi chú**: Người dùng có thể thêm ghi chú (tối đa 500 ký tự, không bắt buộc) để cung cấp thông tin bổ sung hoặc yêu cầu đặc biệt (ví dụ: yêu cầu phòng ở tầng thấp).
        - **Xem điều khoản hợp đồng**: Trước khi đặt phòng, người dùng có thể nhấp vào liên kết "Xem điều khoản hợp đồng" để đọc các điều khoản liên quan, mở trong tab mới.
    - **Xác thực và kiểm tra hợp lệ**:
        - Hệ thống yêu cầu người dùng đăng nhập để sử dụng chức năng đặt phòng, đảm bảo chỉ người dùng đã xác thực mới thực hiện được.
        - Hệ thống kiểm tra và ngăn chặn:
            - Đặt phòng khi người dùng đã có hợp đồng thuê phòng còn hiệu lực (trạng thái "Hoạt động" và chưa hết hạn).
            - Đặt phòng khi người dùng đã có một đặt phòng khác ở trạng thái "Chờ xác nhận".
            - Nếu thiếu thông tin (phòng, ngày bắt đầu, thời gian thuê) hoặc thông tin không hợp lệ (ví dụ: định dạng ngày sai), hệ thống hiển thị thông báo lỗi cụ thể (ví dụ: "Vui lòng chọn phòng", "Định dạng ngày bắt đầu không hợp lệ").
    - **Xác nhận đặt phòng**:
        - Khi đặt phòng thành công, người dùng nhận thông báo (toast) "Đặt phòng thành công!" và được chuyển hướng đến trang "Quản lý đặt phòng" (`/quan-ly/dat-phong`).
        - Form đặt phòng được làm mới (xóa dữ liệu đã nhập) và modal đóng lại tự động.
        - Hệ thống tự động gửi thông báo đến quản trị viên (xem chi tiết ở mục thông báo).

2. **Quản lý đặt phòng**:

    - Người dùng có thể truy cập trang "Quản lý đặt phòng" trong khu vực quản lý cá nhân (`/quan-ly/dat-phong`) để xem danh sách tất cả các đặt phòng đã thực hiện.
    - **Xem thông tin đặt phòng**:
        - Mỗi đặt phòng hiển thị thông tin chi tiết bao gồm:
            - Tên phòng và tên nhà trọ (có liên kết để xem chi tiết nhà trọ).
            - Hình ảnh chính của phòng.
            - Ngày bắt đầu thuê (định dạng DD/MM/YYYY).
            - Ngày kết thúc thuê (tính tự động dựa trên ngày bắt đầu và thời gian thuê).
            - Thời gian thuê (tính bằng năm, ví dụ: "2 năm").
            - Ghi chú (nếu có).
            - Trạng thái đặt phòng (Chờ xác nhận, Chấp nhận, Từ chối, Huỷ bỏ) với màu sắc khác nhau để dễ phân biệt.
            - Lý do từ chối (nếu trạng thái là "Từ chối").
    - **Lọc đặt phòng**:
        - Người dùng có thể lọc đặt phòng theo trạng thái (Tất cả, Chờ xác nhận, Chấp nhận, Từ chối, Huỷ bỏ) thông qua dropdown.
        - Sắp xếp đặt phòng theo các tiêu chí: mặc định (mới nhất), cũ nhất, hoặc mới nhất.
        - Dropdown sử dụng thư viện Chosen để cải thiện trải nghiệm chọn lọc.
    - **Hủy đặt phòng**:
        - Với đặt phòng có trạng thái "Chờ xác nhận", người dùng có thể hủy bằng cách nhấp vào nút "Hủy bỏ".
        - Hệ thống hiển thị popup xác nhận để người dùng xác nhận hành động hủy, tránh thao tác nhầm.
        - Sau khi hủy thành công, người dùng nhận thông báo "Hủy đặt phòng thành công!" và danh sách đặt phòng được cập nhật.

3. **Xem hợp đồng**:

    - Khi đặt phòng được chấp nhận (trạng thái "Chấp nhận"), người dùng có thể nhấp vào nút "Xem hợp đồng" để chuyển hướng đến trang chi tiết hợp đồng (`/quan-ly/hop-dong/{contract_id}`).
    - Liên kết này cho phép người dùng xem thông tin hợp đồng chi tiết liên quan đến đặt phòng đã được phê duyệt.

4. **Thông báo và tích hợp quản trị**:

    - **Thông báo cho quản trị viên**:
        - Khi người dùng đặt phòng hoặc hủy đặt phòng, hệ thống tự động gửi email thông báo đến các quản trị viên (vai trò Quản trị viên hoặc Super admin).
        - Email chứa thông tin chi tiết: mã đặt phòng, tên người dùng, tên phòng, tên nhà trọ, ngày bắt đầu, thời gian thuê, ghi chú (nếu có), và trạng thái.
        - Quản trị viên cũng nhận thông báo đẩy với tiêu đề, nội dung, và liên kết đến trang quản lý đặt phòng (`/bookings`).
    - **Lưu trữ thông báo**: Mỗi hành động (đặt phòng, hủy) được lưu vào bảng thông báo (`Notification`) để quản trị viên dễ dàng theo dõi.
