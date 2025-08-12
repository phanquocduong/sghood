### 1. Xem danh sách hợp đồng

-   **Mô tả**: Người dùng có thể truy cập trang Quản lý hợp đồng để xem toàn bộ danh sách các hợp đồng mà họ đã tạo hoặc đang tham gia.
-   **Chi tiết**:
    -   Hiển thị thông tin cơ bản của hợp đồng như: mã hợp đồng, tên phòng, tên nhà trọ, thời hạn hợp đồng, tiền cọc, giá thuê, trạng thái hợp đồng.
    -   Hiển thị hình ảnh phòng trọ kèm theo liên kết đến trang chi tiết nhà trọ.
    -   Hiển thị thông báo “Chưa có hợp đồng nào” nếu danh sách trống.

### 2. Xem chi tiết hợp đồng

-   Người dùng có thể nhấp vào nút xem chi tiết để xem thông tin chi tiết của một hợp đồng cụ thể: Xem nội dung hợp đồng, trạng thái và danh sách các phụ lục hợp đồng (nếu có), bao gồm ngày kết thúc mới và giá thuê mới.

### 3. Hủy hợp đồng

-   Người dùng có thể hủy hợp đồng khi hợp đồng ở trạng thái “Chờ xác nhận” thông qua nút “Hủy bỏ” trên giao diện hợp đồng.
-   **Chi tiết**:
    -   Có yêu cầu xác nhận qua popup để tránh thao tác nhầm.
    -   Sau khi hủy thành công, hệ thống thông báo “Hủy hợp đồng thành công” và cập nhật danh sách hợp đồng, đồng thời gửi thông báo email và thông báo đẩy đến các quản trị viên.

### 4. Gia hạn hợp đồng

-   **Mô tả**: Người dùng có thể yêu cầu gia hạn hợp đồng khi hợp đồng đang ở trạng thái “Hoạt động” và gần đến ngày hết hạn.
-   **Chi tiết**:
    -   Hiển thị nút “Gia hạn” khi hợp đồng còn trong khoảng thời gian cho phép gia hạn (mặc định là `15 ngày`).
    -   Người dùng chọn số tháng gia hạn (tối thiểu 1 tháng) qua một modal.
    -   Hệ thống gửi OTP đến số điện thoại của người dùng để xác minh trước khi gửi yêu cầu gia hạn.
    -   Sau khi xác minh OTP thành công, yêu cầu gia hạn được gửi và hiển thị thông báo “Yêu cầu gia hạn hợp đồng đã được gửi”. Hệ thống sẽ tự động gửi thông báo email và thông báo đẩy đến các quản trị viên.
    -   Người dùng có thể xem trạng thái yêu cầu gia hạn trong danh sách yêu cầu gia hạn.

### 5. Hủy yêu cầu gia hạn

-   **Mô tả**: Người dùng có thể hủy yêu cầu gia hạn hợp đồng nếu yêu cầu đang ở trạng thái “Chờ duyệt” thông qua nút "Huỷ bỏ" hiển thị trong yêu cầu.
-   **Chi tiết**:
    -   Có yêu cầu xác nhận qua popup để đảm bảo hành động có chủ ý.
    -   Sau khi hủy, hệ thống thông báo “Hủy gia hạn thành công” và cập nhật danh sách. Đồng thời gửi thông báo email và thông báo đẩy đến các quản trị viên

### 6. Tải hợp đồng dưới dạng PDF

-   **Mô tả**: Người dùng có thể tải hợp đồng dưới dạng PDF khi hợp đồng ở trạng thái “Hoạt động” thông qua nút “Tải hợp đồng”.
-   **Chi tiết**:
    -   Hệ thống tạo và cung cấp liên kết tải file PDF, mở trong tab mới.
    -   Nếu hợp đồng chưa có file PDF, hệ thống sẽ tự động tạo trước khi cung cấp.

### 7. Yêu cầu trả phòng

-   **Mô tả**: Người dùng có thể gửi yêu cầu trả phòng và hoàn tiền cọc khi hợp đồng đang ở trạng thái “Hoạt động” và gần đến ngày hết hạn.
-   **Chi tiết**:
    -   Nút “Trả phòng” sẽ hiển thị khi hợp đồng còn trong khoảng thời gian cho phép (mặc định là `15 ngày`).
    -   Người dùng điền thông tin trả phòng qua modal:
        -   Chọn ngày dự kiến rời phòng (từ ngày mai đến tối đa 30 ngày sau ngày kết thúc hợp đồng).
        -   Chọn phương thức hoàn tiền: tiền mặt hoặc chuyển khoản.
        -   Nếu chọn chuyển khoản, người dùng nhập thông tin ngân hàng (tên ngân hàng, số tài khoản, tên chủ tài khoản).
    -   Hệ thống gửi OTP để xác minh trước khi gửi yêu cầu trả phòng.
    -   Sau khi xác minh OTP, yêu cầu trả phòng được gửi và hiển thị thông báo “Yêu cầu trả phòng và hoàn tiền cọc đã được gửi”. Đồng thời gửi thông báo email và thông báo đẩy đến các quản trị viên.

### 8. Xem danh sách yêu cầu trả phòng

-   **Mô tả**: Người dùng có thể xem danh sách các yêu cầu trả phòng mà họ đã gửi.
-   **Chi tiết**:
    -   Trang danh sách yêu cầu trả phòng hiển thị thông tin yêu cầu trả phòng bao gồm: mã hợp đồng, tên phòng, tên nhà trọ, ngày rời phòng, trạng thái kiểm kê, trạng thái hoàn tiền, số tiền cọc, số tiền khấu trừ (nếu có), số tiền hoàn lại cuối cùng (nếu có).
    -   Hiển thị trạng thái rời phòng (Đã rời/Chưa rời) và lý do từ chối (nếu có).
    -   Hiển thị thông báo “Chưa có yêu cầu trả phòng nào” nếu danh sách trống.

### 9. Hủy yêu cầu trả phòng

-   **Mô tả**: Người dùng có thể hủy yêu cầu trả phòng nếu yêu cầu đang ở trạng thái “Chờ kiểm kê” thông qua nút "Huỷ bỏ".
-   **Chi tiết**:
    -   Có yêu cầu xác nhận qua popup để đảm bảo hành động có chủ ý.
    -   Sau khi hủy, hệ thống thông báo “Hủy yêu cầu trả phòng thành công” và cập nhật danh sách. Đồng thời gửi thông báo email và thông báo đẩy đến các quản trị viên.

### 10. Kiểm tra và xác nhận kết quả kiểm kê tài sản

-   **Mô tả**: Người dùng có thể xem và xác nhận/từ chối kết quả kiểm kê tài sản khi trả phòng.
-   **Chi tiết**:
    -   Khi yêu cầu trả phòng đạt trạng thái “Đã kiểm kê”, người dùng có thể xem chi tiết kiểm kê qua modal:
        -   Bảng danh sách tài sản với tên mục, tình trạng, và số tiền khấu hao (nếu có).
        -   Hình ảnh kiểm kê (nếu có).
        -   Thông tin tài chính: tiền cọc, số tiền khấu hao, số tiền hoàn lại cuối cùng.
    -   Người dùng có thể:
        -   **Đồng ý**: Xác nhận kết quả kiểm kê, chuyển sang trạng thái “Đồng ý”.
        -   **Từ chối**: Nhập lý do từ chối và gửi yêu cầu kiểm kê lại.
    -   Hệ thống thông báo “Xác nhận kiểm kê thành công” hoặc “Từ chối kiểm kê thành công” sau khi thực hiện. Đồng thời gửi thông báo email và thông báo đẩy đến các quản trị viên.

### 11. Xác nhận đã rời phòng

-   **Mô tả**: Người dùng có thể xác nhận đã rời phòng sau khi đồng ý với kết quả kiểm kê.
-   **Chi tiết**:
    -   Giao diện hiển thị nút “Xác nhận đã rời phòng” trong modal kiểm kê nếu trạng thái là “Đồng ý”.
    -   Sau khi xác nhận, hệ thống thông báo “Xác nhận đã rời phòng thành công” và cập nhật trạng thái. Đồng thời gửi thông báo email và thông báo đẩy đến các quản trị viên.

### 12. Kiểm tra và chỉnh sửa thông tin ngân hàng

-   **Mô tả**: Người dùng có thể kiểm tra và cập nhật thông tin ngân hàng cho yêu cầu trả phòng.
-   **Chi tiết**:
    -   Xem thông tin ngân hàng (nếu chọn chuyển khoản) qua modal, bao gồm mã QR để kiểm tra.
    -   Hiển thị nút “Chỉnh sửa thông tin chuyển khoản” để mở modal chỉnh sửa.
    -   Trong modal chỉnh sửa, người dùng có thể cập nhật: tên ngân hàng, số tài khoản, tên chủ tài khoản.
    -   Hệ thống thông báo “Cập nhật thông tin hoàn tiền thành công” sau khi lưu.

### 13. Kết thúc hợp đồng sớm

-   **Mô tả**: Người dùng có thể yêu cầu kết thúc hợp đồng sớm nếu hợp đồng đang ở trạng thái “Hoạt động” và không gần ngày hết hạn.
-   **Chi tiết**:
    -   Hiển thị nút “Kết thúc sớm” khi hợp đồng đáp ứng điều kiện.
    -   Hiển thị cảnh báo về các hậu quả:
        -   Tiền cọc không được hoàn lại.
        -   Phải rời phòng trong 3 ngày sau khi yêu cầu được xác nhận.
        -   Cần thanh toán tất cả hóa đơn chưa thanh toán.
        -   Có thể ảnh hưởng đến lịch sử thuê.
    -   Yêu cầu xác nhận qua popup và OTP để xác minh.
    -   Sau khi xác minh OTP, hệ thống thông báo “Yêu cầu kết thúc hợp đồng sớm đã được gửi” và cập nhật trạng thái hợp đồng. Đồng thời gửi thông báo email và thông báo đẩy đến các quản trị viên.

### 15. Xem danh sách phụ lục hợp đồng

-   **Mô tả**: Người dùng có thể xem danh sách các phụ lục hợp đồng (gia hạn) đang hoạt động liên quan đến một hợp đồng cụ thể.
-   **Chi tiết**:
    -   Danh sách hiển thị trong trang chi tiết hợp đồng, bao gồm: mã phụ lục, trạng thái, ngày kết thúc mới, giá thuê mới.
    -   Người dùng có thể xem chi tiết nội dung phụ lục qua popup.
