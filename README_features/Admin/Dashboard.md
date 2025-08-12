**1. Xem ghi chú quan trọng**

-   Quản trị viên có thể xem danh sách các ghi chú quan trọng để theo dõi thông tin cần chú ý.
-   **Chi tiết**:
    -   Hiển thị tối đa **4 ghi chú mới nhất** trong danh sách.
    -   Mỗi ghi chú bao gồm nội dung, loại ghi chú, tên người tạo, và thời gian tạo.
    -   Hỗ trợ nút **"Xem tất cả"** để chuyển hướng đến trang danh sách ghi chú chi tiết.
    -   Nếu không có ghi chú, hiển thị thông báo "Chưa có ghi chú nào" với biểu tượng thông tin.
    -   Nếu có lỗi khi tải, hiển thị thông báo lỗi cụ thể từ session.

**2. Xem yêu cầu sửa chữa cần xử lý và đang thực hiện**

-   Quản trị viên có thể xem danh sách các yêu cầu sửa chữa ở trạng thái "Chờ xác nhận" và "Đang thực hiện" để theo dõi và xử lý kịp thời.
-   **Chi tiết**:
    -   Hiển thị tối đa **5 yêu cầu mới nhất** cho mỗi trạng thái trong bảng.
    -   Thông tin hiển thị: Tên phòng, tên khách hàng, ngày tạo, mô tả (giới hạn 30 ký tự), trạng thái (với badge màu tương ứng), và nút hành động.
    -   Trạng thái hiển thị bằng badge màu (Chờ xác nhận: vàng, Đang thực hiện: xanh dương, Hoàn thành: xanh lá, Đã hủy: đỏ).
    -   Nút **"Xem chi tiết"** dẫn đến trang chi tiết yêu cầu sửa chữa.
    -   Nếu không có yêu cầu, hiển thị thông báo "Không có yêu cầu sửa chữa nào" với biểu tượng thông tin.
    -   Nếu lỗi tải dữ liệu, hiển thị thông báo lỗi "Không thể tải dữ liệu yêu cầu sửa chữa".

**3. Xem hợp đồng vừa ký**

-   Quản trị viên có thể xem danh sách các hợp đồng vừa được ký trong ngày hiện tại.
-   **Chi tiết**:
    -   Hiển thị tối đa **3 hợp đồng mới nhất** được ký trong ngày (dựa trên `signed_at`).
    -   Thông tin hiển thị: Tên khách hàng, tên phòng, và ngày bắt đầu hợp đồng.
    -   Nếu không có hợp đồng, hiển thị thông báo "Không có hợp đồng nào vừa ký" với biểu tượng thông tin.
    -   Hỗ trợ nút **"Xem tất cả"** để chuyển hướng đến danh sách hợp đồng.

**4. Xem hợp đồng sắp hết hạn**

-   Quản trị viên có thể xem danh sách các hợp đồng sắp hết hạn trong vòng 1 tháng tới để chuẩn bị xử lý.
-   **Chi tiết**:
    -   Hiển thị tối đa **3 hợp đồng sắp hết hạn** (trong khoảng từ ngày hiện tại đến 1 tháng sau, trạng thái "Hoạt động").
    -   Thông tin hiển thị: Tên khách hàng, tên phòng, và ngày hết hạn.
    -   Nếu không có hợp đồng, hiển thị thông báo "Không có hợp đồng nào sắp hết hạn" với biểu tượng thông tin.
    -   Hỗ trợ nút **"Xem tất cả"** để chuyển hướng đến danh sách hợp đồng.

**5. Xem yêu cầu trả phòng**

-   Quản trị viên có thể xem danh sách các yêu cầu trả phòng ở trạng thái "Chờ kiểm kê".
-   **Chi tiết**:
    -   Hiển thị tối đa **3 yêu cầu trả phòng mới nhất** (trạng thái "Chờ kiểm kê").
    -   Thông tin hiển thị: Tên khách hàng, tên phòng, và thời gian tạo yêu cầu.
    -   Nếu không có yêu cầu, hiển thị thông báo "Không có yêu cầu trả phòng nào".
    -   Hỗ trợ nút **"Xem tất cả"** để chuyển hướng đến trang danh sách yêu cầu trả phòng.

**6. Xem phòng đang sửa chữa và xác nhận hoàn thành sửa chữa**

-   Quản trị viên có thể xem danh sách các phòng đang sửa chữa và xác nhận khi sửa chữa hoàn tất để chuyển trạng thái thành "Trống".
-   **Chi tiết**:
    -   Hiển thị danh sách các phòng có trạng thái "Sửa chữa".
    -   Thông tin hiển thị: Tên phòng, trạng thái (hiển thị bằng màu đỏ), và nút "Xác nhận".
    -   Nút **"Xác nhận"** cho phép chuyển trạng thái phòng sang "Trống" với xác nhận JavaScript (`confirm`) để tránh thao tác nhầm.
    -   Nếu không có phòng đang sửa chữa, hiển thị thông báo "Không có phòng nào đang sửa chữa" với biểu tượng thông tin.

**7. Xem tin nhắn mới**

-   Quản trị viên có thể xem các tin nhắn chưa đọc để theo dõi liên lạc từ người dùng.
-   **Chi tiết**:
    -   Hiển thị tối đa **3 tin nhắn chưa đọc mới nhất**.
    -   Thông tin hiển thị: Tên người gửi, nội dung tin nhắn (giới hạn ký tự), và thời gian gửi.
    -   Nếu không có tin nhắn, hiển thị thông báo "Không có tin nhắn nào".
    -   Hỗ trợ nút **"Xem tất cả"** để chuyển hướng đến trang quản lý tin nhắn.

**8. Xem lịch xem trọ sắp tới**

-   Quản trị viên có thể xem danh sách các lịch xem phòng sắp tới để chuẩn bị sắp xếp.
-   **Chi tiết**:
    -   Hiển thị tối đa **3 lịch trọ mới nhất**.
    -   Thông tin hiển thị: Tên khách hàng, ngày giờ của lịch, và tên nhà trọ.
    -   Nếu không có lịch, hiển thị thông báo "Không có lịch xem trọ sắp tới" với biểu tượng thông tin.

**9. Xem yêu cầu gia hạn hợp đồng**

-   Quản trị viên có thể xem danh sách các yêu cầu gia hạn hợp đồng ở trạng thái "Chờ duyệt".
-   **Chi tiết**:
    -   Hiển thị tối đa **3 yêu cầu gia hạn mới nhất** (trạng thái "Chờ duyệt").
    -   Thông tin hiển thị: Tên khách hàng, tên phòng, và ngày kết thúc mới.
    -   Nếu không có yêu cầu, hiển thị thông báo "Không có yêu cầu gia hạn hợp đồng nào" với biểu tượng thông tin.
    -   Hỗ trợ nút **"Xem tất cả"** để chuyển hướng đến danh sách yêu cầu gia hạn.
