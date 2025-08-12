1. **Đặt lịch xem nhà trọ**:

    - Người dùng có thể đặt lịch xem nhà trọ trực tiếp từ trang chi tiết nhà trọ thông qua form "Đặt lịch xem trọ".
    - **Chọn ngày xem**: Người dùng chọn ngày xem trọ thông qua bảng lịch. Ngày được chọn phải từ ngày mốt trở đi (hệ thống tự động giới hạn ngày tối thiểu là ngày hiện tại cộng thêm 2 ngày) để có khoảng thời gian quản trị viên kịp thời xác nhận yêu cầu.
    - **Chọn khung giờ**: Người dùng chọn khung giờ từ danh sách dropdown với các khung giờ được cấu hình sẵn.
    - **Gửi lời nhắn**: Người dùng có thể thêm lời nhắn (tối đa 255 ký tự, không bắt buộc) để cung cấp thông tin bổ sung hoặc yêu cầu đặc biệt.
    - **Xác thực người dùng**: Hệ thống yêu cầu người dùng đã đăng nhập trước mới được đặt lịch.
    - **Xác nhận đặt lịch**:
        - Nếu thiếu ngày hoặc khung giờ, hoặc có lỗi từ server, người dùng nhận thông báo lỗi cụ thể tương ứng.
        - Hệ thống kiểm tra và ngăn chặn việc đặt lịch trùng lặp (người dùng không thể đặt lịch mới cho cùng nhà trọ nếu đã có lịch chưa hoàn thành) hoặc xung đột khung giờ (trùng với lịch xem nhà trọ khác).
        - Nếu đặt lịch thành công, người dùng sẽ nhận được thông báo "Đặt lịch xem nhà trọ thành công!" và hệ thống sẽ tự động gửi thông báo email và thông báo đẩy về cho các quản trị viên.

2. **Quản lý lịch xem nhà trọ**:

    - Người dùng có thể truy cập trang "Lịch xem nhà trọ" trong khu vực quản lý cá nhân để xem danh sách tất cả các lịch xem đã đặt.
    - **Xem thông tin lịch xem**:
        - Mỗi lịch xem hiển thị thông tin chi tiết bao gồm:
            - Tên nhà trọ (có liên kết để xem nhanh chi tiết nhà trọ).
            - Hình ảnh chính của nhà trọ.
            - Địa chỉ nhà trọ.
            - Thời gian xem (định dạng ngày giờ rõ ràng).
            - Trạng thái lịch xem với màu sắc khác nhau để dễ phân biệt.
            - Lời nhắn (nếu có).
            - Lý do từ chối (nếu trạng thái là "Từ chối").
    - **Lọc lịch xem**:
        - Người dùng có thể lọc lịch xem theo trạng thái thông qua dropdown.
        - Sắp xếp lịch xem theo các tiêu chí: mặc định (mới nhất), cũ nhất, hoặc mới nhất.
    - **Chỉnh sửa lịch xem**:
        - Với lịch có trạng thái "Chờ xác nhận", người dùng có thể chỉnh sửa thông tin (ngày, khung giờ, lời nhắn) thông qua một modal popup.
        - Modal chỉnh sửa sử dụng lịch và dropdown khung giờ tương tự form đặt lịch, đảm bảo trải nghiệm nhất quán.
        - Hệ thống kiểm tra xung đột khung giờ khi chỉnh sửa và thông báo lỗi nếu có lịch khác trùng lặp.
        - Sau khi cập nhật thành công, người dùng nhận thông báo "Cập nhật lịch xem thành công!" và danh sách lịch xem được làm mới. Hệ thống sẽ tự động gửi thông báo email và thông báo đẩy về cho các quản trị viên.
    - **Hủy lịch xem**:
        - Người dùng có thể hủy lịch xem có trạng thái "Chờ xác nhận" thông qua nút "Hủy bỏ".
        - Hệ thống hiển thị popup xác nhận để người dùng xác nhận hành động hủy, tránh thao tác nhầm.
        - Sau khi hủy thành công, người dùng nhận thông báo "Hủy lịch xem nhà trọ thành công!" và danh sách lịch được cập nhật. Hệ thống sẽ tự động gửi thông báo email và thông báo đẩy về cho các quản trị viên.
