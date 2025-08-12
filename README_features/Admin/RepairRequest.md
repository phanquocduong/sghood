1. **Xem danh sách yêu cầu sửa chữa**:

    - Quản trị viên có thể xem danh sách tất cả các yêu cầu sửa chữa với thông tin:
        - **Số thứ tự (STT)**: Hiển thị vị trí bản ghi trong trang hiện tại.
        - **Người thuê**: Hiển thị tên người thuê từ hợp đồng.
        - **Phòng**: Hiển thị tên phòng từ hợp đồng.
        - **Tiêu đề**: Hiển thị tiêu đề yêu cầu, kèm nút chỉnh sửa ghi chú.
        - **Ghi chú**: Hiển thị ghi chú (nếu có) hoặc "Chưa có ghi chú nào", kèm nút chỉnh sửa/thêm ghi chú.
        - **Trạng thái**: Hiển thị trạng thái ("Chờ xác nhận", "Đang thực hiện", "Hoàn thành", "Huỷ bỏ") với badge màu tương ứng (xám, vàng, xanh, đỏ).
        - **Ngày sửa**: Hiển thị ngày sửa chữa hoặc "N/A" nếu chưa sửa.
        - **Hành động**: Nút xem chi tiết và dropdown cập nhật trạng thái (nếu trạng thái chưa phải "Hoàn thành" hoặc "Huỷ bỏ").
    - Danh sách hỗ trợ **phân trang** (mặc định 10 bản ghi/trang).
    - Hiển thị tổng số yêu cầu dưới dạng badge ở tiêu đề (ví dụ: "Số yêu cầu: 15").

2. **Lọc và tìm kiếm yêu cầu sửa chữa**:

    - Quản trị viên có thể lọc danh sách yêu cầu sửa chữa theo:
        - **Từ khóa tìm kiếm**: Tìm kiếm theo tiêu đề, mô tả, hoặc ghi chú (LIKE query).
        - **Trạng thái**: Lọc theo trạng thái ("Chờ xác nhận", "Đang thực hiện", "Hoàn thành", "Huỷ bỏ") qua dropdown.
        - **Sắp xếp**: Sắp xếp theo ngày tạo (mới nhất/cũ nhất), tiêu đề, hoặc trạng thái.
    - Form lọc sử dụng **GET request**, giữ lại các tham số lọc khi chuyển trang.
    - Giao diện lọc trực quan với input tìm kiếm, dropdown trạng thái/sắp xếp, và nút "Tìm" với biểu tượng kính lúp.

3. **Xem chi tiết yêu cầu sửa chữa**:

    - Quản trị viên có thể xem thông tin chi tiết của một yêu cầu sửa chữa bằng cách nhấp vào nút "Chi tiết", bao gồm:
        - **Người thuê**: Tên người thuê từ hợp đồng.
        - **Phòng**: Tên phòng từ hợp đồng.
        - **Tiêu đề**: Tiêu đề yêu cầu.
        - **Mô tả**: Mô tả chi tiết vấn đề cần sửa chữa.
        - **Hình ảnh**: Hiển thị danh sách ảnh (nếu có) dưới dạng thumbnail, nhấp để xem ảnh đầy đủ. Nếu không có ảnh, hiển thị "Không có hình ảnh".
        - **Trạng thái**: Hiển thị trạng thái với badge màu tương ứng.
        - **Lý do huỷ** (nếu có): Hiển thị nếu trạng thái là "Huỷ bỏ".
        - **Ghi chú**: Hiển thị ghi chú hoặc "Không có".
        - **Ngày sửa**: Ngày giờ sửa chữa hoặc "Chưa sửa".
        - **Ngày tạo**: Ngày giờ tạo yêu cầu.
    - Giao diện chi tiết sử dụng layout chia cột, rõ ràng với nhãn in đậm.
    - Có nút "Quay lại danh sách" để điều hướng dễ dàng.

4. **Cập nhật trạng thái yêu cầu sửa chữa**:

    - Quản trị viên có thể cập nhật trạng thái yêu cầu qua dropdown trong danh sách:
        - Từ **Chờ xác nhận**: Có thể chuyển sang "Chờ xác nhận", "Đang thực hiện", hoặc "Huỷ bỏ".
        - Từ **Đang thực hiện**: Có thể chuyển sang "Đang thực hiện", "Hoàn thành", hoặc "Huỷ bỏ".
        - Trạng thái **Hoàn thành** hoặc **Huỷ bỏ**: Không thể thay đổi (hiển thị badge trạng thái cố định).
    - Yêu cầu xác nhận trước khi thay đổi trạng thái (JavaScript confirm).
    - Tự động cập nhật **ngày sửa**:
        - Đặt thành thời gian hiện tại khi chuyển sang "Hoàn thành".
        - Xóa (`null`) khi chuyển sang "Huỷ bỏ" hoặc trạng thái khác.
    - Hiển thị thông báo thành công/lỗi sau khi cập nhật.

5. **Cập nhật ghi chú yêu cầu sửa chữa**:

    - Quản trị viên có thể thêm hoặc chỉnh sửa ghi chú cho yêu cầu qua modal:
        - Nhấp vào nút chỉnh sửa/thêm ghi chú (biểu tượng bút hoặc dấu cộng) trong cột "Ghi chú".
        - Modal hiển thị textarea với nội dung ghi chú hiện tại (nếu có), tối đa 1000 ký tự.
        - Gửi yêu cầu cập nhật qua AJAX (PUT) với CSRF token.
    - Giao diện cập nhật tức thì:
        - Cập nhật nội dung ghi chú trong bảng.
        - Thay đổi biểu tượng (bút nếu có ghi chú, dấu cộng nếu không có).
        - Cập nhật tooltip hiển thị nội dung ghi chú.
    - Hiển thị thông báo thành công/lỗi với hiệu ứng fade-in, tự động biến mất sau 5 giây.
    - Validation phía server đảm bảo ghi chú hợp lệ (nullable, string, tối đa 1000 ký tự).
