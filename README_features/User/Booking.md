1. **Đặt phòng trọ**:

    - Người dùng có thể đặt phòng trọ từ trang "Lịch xem nhà trọ" khi lịch xem đạt trạng thái "Hoàn thành" bằng cách nhấp vào nút "Đặt phòng" trên danh sách lịch xem.
    - **Form đặt phòng**:
        - **Chọn phòng**: Người dùng chọn phòng từ danh sách các phòng trống của nhà trọ.
        - **Chọn ngày bắt đầu**: Người dùng chọn ngày bắt đầu thuê thông qua bảng lịch. Ngày bắt đầu phải từ ngày mai trở đi (hệ thống tự động giới hạn ngày tối thiểu là ngày hiện tại cộng thêm 2 ngày).
        - **Chọn thời gian thuê**: Người dùng chọn thời gian thuê từ danh sách các khoảng thời gian được cấu hình sẵn.
        - **Thêm ghi chú**: Người dùng có thể thêm ghi chú (tối đa 500 ký tự, không bắt buộc) để cung cấp thông tin bổ sung hoặc yêu cầu đặc biệt.
        - **Xem điều khoản hợp đồng**: Trước khi đặt phòng, người dùng có thể nhấp vào liên kết "Xem điều khoản hợp đồng" để đọc các điều khoản liên quan.
    - **Xác thực và kiểm tra hợp lệ**:
        - Hệ thống kiểm tra và ngăn chặn:
            - Đặt phòng khi người dùng đã có hợp đồng thuê phòng còn hiệu lực.
            - Đặt phòng khi người dùng đã có một đặt phòng khác ở trạng thái "Chờ xác nhận".
            - Nếu thiếu thông tin (phòng, ngày bắt đầu, thời gian thuê), hệ thống sẽ hiển thị thông báo lỗi cụ thể tương ứng.
    - **Xác nhận đặt phòng**:
        - Khi đặt phòng thành công, người dùng nhận thông báo "Đặt phòng thành công!" và được chuyển hướng đến trang "Quản lý đặt phòng".
        - Hệ thống tự động gửi thông báo email và thông báo đẩy đến các quản trị viên.

2. **Quản lý đặt phòng**:

    - Người dùng có thể truy cập trang "Quản lý đặt phòng" trong khu vực quản lý cá nhân để xem danh sách tất cả các đặt phòng đã thực hiện.
    - **Xem thông tin đặt phòng**:
        - Mỗi đặt phòng hiển thị thông tin chi tiết bao gồm:
            - Tên phòng và tên nhà trọ (có liên kết để xem nhanh chi tiết nhà trọ).
            - Hình ảnh chính của phòng.
            - Ngày bắt đầu thuê.
            - Ngày kết thúc thuê.
            - Thời gian thuê.
            - Ghi chú (nếu có).
            - Trạng thái đặt phòng với màu sắc khác nhau để dễ phân biệt.
            - Lý do từ chối (nếu trạng thái là "Từ chối").
    - **Lọc đặt phòng**:
        - Người dùng có thể lọc đặt phòng theo trạng thái.
        - Sắp xếp đặt phòng theo các tiêu chí: mặc định (mới nhất), cũ nhất, hoặc mới nhất.
    - **Hủy đặt phòng**:
        - Với đặt phòng có trạng thái "Chờ xác nhận", người dùng có thể hủy bằng cách nhấp vào nút "Hủy bỏ".
        - Hệ thống hiển thị popup xác nhận để người dùng xác nhận hành động hủy, tránh thao tác nhầm.
        - Sau khi hủy thành công, người dùng nhận thông báo "Hủy đặt phòng thành công!" và danh sách đặt phòng được cập nhật. Hệ thống tự động gửi thông báo email và thông báo đẩy đến các quản trị viên.

3. **Xem hợp đồng**:

    - Khi đặt phòng được chấp nhận, người dùng có thể nhấp vào nút "Xem hợp đồng" để chuyển hướng đến trang chi tiết hợp đồng.
    - Liên kết này cho phép người dùng xem thông tin hợp đồng chi tiết liên quan đến đặt phòng đã được phê duyệt.
