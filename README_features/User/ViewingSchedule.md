1. **Đặt lịch xem nhà trọ**:

    - Người dùng có thể đặt lịch xem nhà trọ trực tiếp từ trang chi tiết nhà trọ thông qua form "Đặt lịch xem trọ" với giao diện thân thiện.
    - **Chọn ngày xem**: Người dùng chọn ngày xem trọ thông qua lịch (sử dụng thư viện daterangepicker) với giao diện hỗ trợ tiếng Việt (ngày, tháng, nút xác nhận/hủy). Ngày được chọn phải từ ngày mai trở đi (hệ thống tự động giới hạn ngày tối thiểu là ngày hiện tại cộng thêm 2 ngày).
    - **Chọn khung giờ**: người dùng chọn khung giờ từ danh sách dropdown với các khung giờ được cấu hình sẵn (ví dụ: "8:00 sáng - 8:30 sáng", "14:00 chiều - 14:30 chiều"). Dropdown tự động đóng khi nhấp ra ngoài, cải thiện trải nghiệm người dùng.
    - **Gửi lời nhắn**: Người dùng có thể thêm lời nhắn (tối đa 255 ký tự, không bắt buộc) để cung cấp thông tin bổ sung hoặc yêu cầu đặc biệt (ví dụ: muốn xem phòng cụ thể).
    - **Xác thực người dùng**: Hệ thống yêu cầu người dùng phải đăng nhập trước khi đặt lịch, đảm bảo chỉ người dùng đã xác thực mới sử dụng được chức năng này.
    - **Xác nhận đặt lịch**:
        - Nếu đặt lịch thành công, người dùng nhận thông báo (toast) "Đặt lịch xem nhà trọ thành công!" và form được làm mới (xóa dữ liệu đã nhập).
        - Hệ thống kiểm tra và ngăn chặn việc đặt lịch trùng lặp (người dùng không thể đặt lịch mới cho cùng nhà trọ nếu đã có lịch chưa hoàn thành) hoặc xung đột khung giờ (trùng với lịch xem nhà trọ khác).
        - Nếu thiếu ngày hoặc khung giờ, hoặc có lỗi từ server, người dùng nhận thông báo lỗi cụ thể (ví dụ: "Vui lòng chọn ngày và khung giờ!" hoặc "Bạn đã có một lịch xem nhà trọ khác trong khung giờ này.").

2. **Quản lý lịch xem nhà trọ**:

    - Người dùng có thể truy cập trang "Lịch xem nhà trọ" trong khu vực quản lý cá nhân để xem danh sách tất cả các lịch xem đã đặt.
    - **Xem thông tin lịch xem**:
        - Mỗi lịch xem hiển thị thông tin chi tiết bao gồm:
            - Tên nhà trọ (có liên kết để xem chi tiết nhà trọ).
            - Hình ảnh chính của nhà trọ.
            - Địa chỉ nhà trọ.
            - Thời gian xem (định dạng ngày giờ rõ ràng, ví dụ: "15/08/2025 14:00").
            - Trạng thái lịch xem (Chờ xác nhận, Đã xác nhận, Hoàn thành, Từ chối, Huỷ bỏ) với màu sắc khác nhau để dễ phân biệt.
            - Lời nhắn (nếu có).
            - Lý do từ chối (nếu trạng thái là "Từ chối").
    - **Lọc lịch xem**:
        - Người dùng có thể lọc lịch xem theo trạng thái (Tất cả, Chờ xác nhận, Đã xác nhận, Từ chối, Hoàn thành, Huỷ bỏ) thông qua dropdown.
        - Sắp xếp lịch xem theo các tiêu chí: mặc định (mới nhất), cũ nhất, hoặc mới nhất.
    - **Chỉnh sửa lịch xem**:
        - Với lịch có trạng thái "Chờ xác nhận", người dùng có thể chỉnh sửa thông tin (ngày, khung giờ, lời nhắn) thông qua một modal popup.
        - Modal chỉnh sửa sử dụng lịch (daterangepicker) và dropdown khung giờ tương tự form đặt lịch, đảm bảo trải nghiệm nhất quán.
        - Hệ thống kiểm tra xung đột khung giờ khi chỉnh sửa và thông báo lỗi nếu có lịch khác trùng lặp.
        - Sau khi cập nhật thành công, người dùng nhận thông báo "Cập nhật lịch xem thành công!" và danh sách lịch xem được làm mới.
    - **Hủy lịch xem**:
        - Người dùng có thể hủy lịch xem có trạng thái "Chờ xác nhận" thông qua nút "Hủy bỏ".
        - Hệ thống hiển thị popup xác nhận (sử dụng SweetAlert2) để người dùng xác nhận hành động hủy, tránh thao tác nhầm.
        - Sau khi hủy thành công, người dùng nhận thông báo "Hủy lịch xem nhà trọ thành công!" và danh sách lịch được cập nhật.

3. **Thông báo và tích hợp quản trị**:

    - **Thông báo cho quản trị viên**:
        - Khi người dùng đặt lịch, chỉnh sửa lịch, hoặc hủy lịch, hệ thống tự động gửi email thông báo đến các quản trị viên (vai trò Quản trị viên hoặc Super admin).
        - Email chứa thông tin chi tiết: mã lịch, tên người dùng, email, tên nhà trọ, địa chỉ, thời gian xem, trạng thái, lời nhắn (nếu có), và thời gian cập nhật cuối cùng.
        - Quản trị viên cũng nhận thông báo push qua Firebase Cloud Messaging (nếu có fcm_token) với tiêu đề, nội dung, và liên kết đến trang quản lý lịch xem.
    - **Lưu trữ thông báo**: Mỗi hành động (đặt, chỉnh sửa, hủy) được lưu vào bảng thông báo (Notification) để quản trị viên dễ dàng theo dõi.
