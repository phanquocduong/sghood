1. **Chức năng liên quan đến Khu vực (District)**

-   **Xem danh sách khu vực nổi bật**: Người dùng có thể xem các khu vực nổi bật trên trang chủ, mỗi khu vực hiển thị tên, hình ảnh minh họa, và số lượng nhà trọ hiện có. Các khu vực này được sắp xếp theo số lượng nhà trọ (nhiều nhất được ưu tiên hiển thị).
-   **Tìm kiếm nhà trọ theo khu vực**: Người dùng có thể chọn một khu vực cụ thể từ danh sách khu vực nổi bật hoặc bộ lọc để xem danh sách các nhà trọ thuộc khu vực đó. Khi chọn khu vực, hệ thống tự động chuyển hướng đến trang danh sách nhà trọ với kết quả được lọc theo khu vực đã chọn.

2. **Chức năng liên quan đến Nhà trọ (Motel)**

-   **Tìm kiếm nhà trọ với bộ lọc chi tiết**:
    -   Người dùng có thể tìm kiếm nhà trọ bằng cách nhập từ khóa (tên, địa chỉ, mô tả, hoặc tiện ích) vào thanh tìm kiếm trên trang chủ hoặc trang danh sách nhà trọ.
    -   Lọc nhà trọ theo **khu vực** (chọn từ danh sách các quận/huyện).
    -   Lọc theo **khoảng giá** (ví dụ: 1-2 triệu, 2-3 triệu, v.v.) dựa trên cấu hình từ hệ thống.
    -   Lọc theo **diện tích phòng** (ví dụ: 10-20m², 20-30m², v.v.).
    -   Lọc theo **tiện ích** (ví dụ: wifi, điều hòa, nhà vệ sinh khép kín, v.v.) bằng cách chọn nhiều tiện ích cùng lúc từ danh sách checkbox.
    -   Sắp xếp kết quả theo các tiêu chí: mặc định, nổi bật nhất (dựa trên số tiện ích và phòng trống), mới nhất, hoặc cũ nhất.
-   **Xem danh sách nhà trọ nổi bật**: Trên trang chủ, người dùng có thể xem danh sách các nhà trọ nổi bật được hiển thị dưới dạng carousel (trượt ngang), bao gồm hình ảnh chính, tên nhà trọ, địa chỉ, khu vực, số phòng trống, và giá thấp nhất. Carousel tự động điều chỉnh giao diện phù hợp với thiết bị (máy tính hoặc điện thoại).
-   **Xem danh sách nhà trọ theo kết quả tìm kiếm**: Trên trang danh sách nhà trọ, người dùng thấy các nhà trọ phù hợp với bộ lọc, hiển thị dưới dạng lưới (grid) với thông tin tóm tắt: hình ảnh chính, tên nhà trọ, địa chỉ, khu vực, số phòng trống, và giá thấp nhất. Hệ thống hỗ trợ phân trang (mỗi trang 6 nhà trọ) để dễ dàng duyệt qua các kết quả.
-   **Xem chi tiết nhà trọ**:
    -   Người dùng có thể nhấp vào một nhà trọ để xem chi tiết, bao gồm:
        -   Tên nhà trọ, địa chỉ, khu vực, và mô tả chi tiết.
        -   Bộ sưu tập hình ảnh của nhà trọ (gallery).
        -   Danh sách tiện ích đi kèm (ví dụ: wifi, điều hòa, v.v.).
        -   Bản đồ nhúng (iframe) hiển thị vị trí nhà trọ.
        -   Thông tin các khoản phí hàng tháng (điện, nước, giữ xe, rác, internet, dịch vụ) với đơn vị cụ thể (kWh, m³, tháng).
        -   Danh sách các phòng trống với thông tin chi tiết (xem chi tiết bên dưới).

3. **Chức năng liên quan đến Phòng trọ (Room)**

-   **Xem danh sách phòng trống**: Trong trang chi tiết nhà trọ, người dùng có thể xem danh sách các phòng trống, mỗi phòng hiển thị hình ảnh chính, tên phòng, trạng thái (Trống, Đã thuê, Sửa chữa), giá thuê/tháng, và diện tích.
-   **Xem chi tiết phòng trọ**:
    -   Người dùng có thể nhấp vào một phòng trọ để mở modal chi tiết, hiển thị:
        -   Tên phòng, giá thuê/tháng, diện tích, trạng thái, và mô tả.
        -   Danh sách tiện ích của phòng (ví dụ: điều hòa, giường, tủ, v.v.).
        -   Slider ảnh của phòng, cho phép chuyển qua lại giữa các hình ảnh bằng nút điều hướng (nếu có nhiều ảnh).
    -   Modal có thể đóng dễ dàng bằng nút đóng hoặc nhấp ra ngoài vùng nội dung.
-   **Định dạng giá và phí rõ ràng**: Giá phòng và các khoản phí (điện, nước, v.v.) được định dạng theo đơn vị tiền tệ Việt Nam (VND) với dấu phân cách hàng nghìn, giúp người dùng dễ đọc.
