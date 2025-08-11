1.  **Xem danh sách các yêu cầu sửa chữa của bản thân**:

    -   Người dùng có thể truy cập để xem toàn bộ lịch sử yêu cầu sửa chữa liên quan đến hợp đồng thuê phòng đang hoạt động của mình.
    -   Trong danh sách, mỗi yêu cầu sẽ hiển thị thông tin như: tiêu đề, mô tả, hình ảnh (nếu có, dưới dạng list), trạng thái và các thông tin liên quan từ hợp đồng (như phòng thuê, nhà trọ).

2.  **Tạo mới một yêu cầu sửa chữa**:

    -   Người dùng có thể gửi yêu cầu sửa chữa mới bằng cách nhập tiêu đề (bắt buộc, tối đa 255 ký tự), mô tả (bắt buộc, tối đa 1000 ký tự), và tùy chọn upload tối đa 5 hình ảnh minh họa (định dạng jpg, jpeg, png, webp; mỗi ảnh tối đa 2MB).
    -   Hệ thống tự động kiểm tra hợp đồng thuê phòng đang hoạt động của người dùng để liên kết yêu cầu.
    -   Hình ảnh sẽ được tự động chuyển đổi sang định dạng webp để tối ưu lưu trữ và lưu vào storage công khai (URL có thể xem trực tiếp).
    -   Sau khi tạo thành công, yêu cầu được đặt trạng thái "Chờ xác nhận" và gửi thông báo email và thông báo đẩy đến quản trị viên để xử lý.

3.  **Hủy một yêu cầu sửa chữa**:
    -   Người dùng có thể hủy yêu cầu sửa chữa của mình.
    -   Điều kiện: Chỉ hủy được nếu yêu cầu chưa bị hủy trước đó, chưa ở trạng thái "Đang thực hiện" hoặc "Hoàn thành" (hệ thống kiểm tra tự động để tránh lạm dụng).
    -   Sau khi hủy, trạng thái cập nhật thành "Huỷ bỏ" và gửi thông báo email và thông báo đẩy đến quản trị viên để cập nhật.
