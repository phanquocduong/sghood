1. **Xem danh sách hóa đơn**:

    - Quản trị viên có thể xem danh sách tất cả các hóa đơn với thông tin:
        - **Mã hóa đơn**.
        - **Tổng tiền** (định dạng tiền tệ VND, ví dụ: 1,500,000 VND).
        - **Trạng thái** (Chưa trả, Đã trả, Quá hạn, Đã hoàn tiền, Đã hủy) với badge màu sắc tương ứng (đỏ cho quá hạn, xanh cho đã trả, v.v.).
        - **Tháng/năm** của hóa đơn.
        - **Ngày hoàn tiền** (nếu có) hoặc hành động khả dụng (ví dụ: cập nhật trạng thái).
    - Danh sách hỗ trợ **phân trang** (mặc định 15 bản ghi/trang) để quản lý dữ liệu lớn.
    - Giao diện bảng trực quan với các cột được căn giữa, dễ đọc.

2. **Lọc và tìm kiếm hóa đơn**:

    - Quản trị viên có thể lọc hóa đơn theo:
        - **Mã hóa đơn** (tìm kiếm theo từ khóa).
        - **Tháng** (từ 1 đến 12).
        - **Năm** (dựa trên danh sách năm có trong dữ liệu).
        - **Trạng thái** (Chưa trả, Đã trả).
    - Form lọc tự động gửi yêu cầu khi chọn tháng/năm/trạng thái, sử dụng **AJAX** để cập nhật kết quả mà không cần tải lại trang.
    - Hiển thị thông tin bộ lọc hiện tại (ví dụ: "Tháng 8/2025 | Trạng thái: Chưa trả | Tìm kiếm: INV123").

3. **Xem thống kê hóa đơn**:

    - Hệ thống hiển thị các **thẻ thống kê** (card) trực quan, bao gồm:
        - **Tổng số hóa đơn** và tổng số tiền.
        - **Số hóa đơn đã trả** và tổng số tiền đã trả.
        - **Số hóa đơn chưa trả** và tổng số tiền chưa trả.
        - **Số hóa đơn đã hoàn tiền** và tổng số tiền đã hoàn.
    - Các thẻ sử dụng màu sắc nổi bật (xanh, vàng, v.v.) và biểu tượng (icon) để dễ nhận biết.

4. **Xem chi tiết hóa đơn**:

    - Quản trị viên có thể xem chi tiết hóa đơn thông qua **modal** bằng cách nhấn nút "Xem chi tiết":
        - **Thông tin hóa đơn**: Mã hóa đơn, loại (Hàng tháng), kỳ (tháng/năm), trạng thái, ngày tạo.
        - **Thông tin khách hàng**: Tên, email, số điện thoại, phòng, nhà trọ.
        - **Chỉ số điện nước**:
            - Số điện/nước tiêu thụ (kWh/m³).
            - Chỉ số điện/nước hiện tại và tháng trước.
        - **Chi tiết chi phí**:
            - Phí phòng, điện, nước, giữ xe, rác, internet, dịch vụ.
            - Tổng số tiền (định dạng tiền tệ VND).
    - Modal hiển thị thông tin qua **fetch API**, với loading spinner trong khi tải dữ liệu, đảm bảo trải nghiệm mượt mà.

5. **Cập nhật trạng thái hóa đơn**:

    - Quản trị viên có thể xử lý trường hợp người dùng trả tiền mặt thông qua nút "Người dùng thanh toán tiền mặt":
        - Yêu cầu xác nhận trước khi cập nhật.
        - Tạo **giao dịch (transaction)** tự động với:
            - Mã giao dịch (định dạng `CASH_<invoice_id>_<timestamp>`).
            - Số tiền giao dịch bằng tổng số tiền hóa đơn.
            - Nội dung giao dịch (ví dụ: "Thanh toán hóa đơn INV123 cho phòng A101").
    - Hoá đơn chuyển trạng thái sang "Đã trả".
    - Hiển thị thông báo thành công (alert) sau khi cập nhật trạng thái.
    - Nếu hóa đơn đã ở trạng thái **Đã trả**, hiển thị thông tin ngày hoàn tiền (nếu có) hoặc thông báo "Hóa đơn đã được thanh toán".

6. **Quản lý hóa đơn quá hạn**:

    - Hệ thống tự động kiểm tra hóa đơn quá hạn (sau ngày 10 hàng tháng) thông qua **cron job** chạy hàng ngày lúc 9:00:
        - Tìm các hóa đơn có trạng thái **Chưa trả** và ngày tạo trước ngày 10 của tháng hiện tại.
        - Gửi **thông báo quá hạn** đến người dùng email và thông báo đẩy với thông báo chi tiết (số tiền, số ngày quá hạn, phòng, nhà trọ), lưu thông báo vào cơ sở dữ liệu với trạng thái "Chưa đọc".

7. **Tích hợp với chỉ số điện nước**:

    - Hóa đơn được tạo tự động từ chỉ số điện nước, với các khoản phí:
        - **Phí điện/nước**: Tính dựa trên lượng tiêu thụ (chỉ số hiện tại trừ chỉ số trước) và đơn giá từ nhà trọ.
        - **Phí phòng/dịch vụ**: Tính tỷ lệ nếu là tháng đầu/cuối của hợp đồng.
    - Gửi thông báo email và thông báo đẩy về thông tin hoá đơn cần thanh toán đến người dùng.
