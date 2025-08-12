1. **Xem danh sách phòng trọ**:

    - Quản trị viên có thể xem danh sách phòng trọ thuộc một nhà trọ cụ thể với thông tin:
        - **Số thứ tự (STT)**: Hiển thị vị trí bản ghi trong trang hiện tại.
        - **Ảnh chính**: Hiển thị ảnh chính của phòng (hoặc ảnh mặc định nếu không có), với hiệu ứng phóng to khi hover.
        - **Diện tích/Giá**: Hiển thị diện tích (m²) và giá (VNĐ, định dạng số có dấu chấm).
        - **Tên phòng**: Liên kết đến trang chi tiết phòng.
        - **Ghi chú**: Hiển thị ghi chú (giới hạn 50 ký tự) hoặc "Không có ghi chú".
        - **Trạng thái**: Hiển thị trạng thái ("Trống", "Đã thuê", "Sửa chữa", "Ẩn") với badge màu tương ứng (xanh, xanh dương, vàng, xám).
        - **Thao tác**: Nút "Sửa" và "Xóa" (không hiển thị cho phòng "Đã thuê").
    - Hỗ trợ **phân trang** (mặc định 10 bản ghi/trang).
    - Hiển thị tên nhà trọ trong tiêu đề và breadcrumb để dễ điều hướng.

2. **Lọc và tìm kiếm phòng trọ**:

    - Quản trị viên có thể lọc danh sách phòng trọ theo:
        - **Từ khóa tìm kiếm**: Tìm kiếm theo tên phòng.
        - **Trạng thái**: Lọc theo trạng thái ("Trống", "Đã thuê", "Sửa chữa", "Ẩn") qua dropdown, tự động gửi form khi thay đổi.
        - **Sắp xếp**: Sắp xếp theo tên (A-Z/Z-A) hoặc ngày tạo (mới nhất/cũ nhất).
    - Form lọc sử dụng **GET request**, giữ lại các tham số lọc khi chuyển trang.
    - Giao diện lọc trực quan với input tìm kiếm kèm biểu tượng kính lúp và dropdown trạng thái/sắp xếp.

3. **Xem chi tiết phòng trọ**:

    - Quản trị viên có thể xem thông tin chi tiết của một phòng trọ bằng cách nhấp vào tên phòng, bao gồm:
        - **Tên phòng**: Hiển thị tiêu đề lớn.
        - **Nhà trọ**: Tên nhà trọ với liên kết đến trang chi tiết nhà trọ.
        - **Giá phòng**: Định dạng số VNĐ (ví dụ: 2.500.000 VNĐ).
        - **Diện tích**: Hiển thị số m².
        - **Trạng thái**: Hiển thị trạng thái với badge màu tương ứng.
        - **Mô tả**: Hiển thị mô tả hoặc "Không có mô tả".
        - **Ghi chú**: Hiển thị ghi chú hoặc "Không có ghi chú".
        - **Tiện nghi**: Danh sách tiện nghi (checkbox) với biểu tượng check và tên tiện nghi.
        - **Thư viện ảnh**: Hiển thị ảnh chính (với badge "Ảnh chính") và các ảnh phụ trong grid, kèm số thứ tự ảnh phụ.
    - Giao diện chi tiết sử dụng layout chia cột, rõ ràng với nhãn in đậm.
    - Có nút "Quay lại", "Sửa", và "Xóa" (không hiển thị "Sửa"/"Xóa" nếu trạng thái là "Đã thuê").

4. **Tạo phòng trọ mới**:

    - Quản trị viên có thể tạo phòng mới cho một nhà trọ cụ thể qua form:
        - **Nhà trọ**: Hiển thị tên nhà trọ (chỉ đọc).
        - **Tên phòng**: Nhập tên, yêu cầu duy nhất trong cùng nhà trọ.
        - **Giá phòng**: Nhập số nguyên (VNĐ, tối thiểu 0).
        - **Diện tích**: Nhập số thập phân (tối đa 999.99 m², định dạng chuẩn hóa dấu phẩy/thập phân).
        - **Mô tả**: Nhập mô tả (tối đa 1000 ký tự, không bắt buộc).
        - **Trạng thái**: Chọn từ "Trống", "Đã thuê", "Sửa chữa", "Ẩn".
        - **Ghi chú**: Nhập ghi chú (tối đa 255 ký tự, không bắt buộc).
        - **Tiện nghi**: Chọn nhiều tiện nghi từ danh sách checkbox (lọc các tiện nghi "Hoạt động" và loại "Phòng trọ").
        - **Hình ảnh**: Tải lên nhiều ảnh (JPG, PNG, GIF, WebP, tối đa 2MB/ảnh), chọn ảnh chính qua giao diện FilePond với preview.
    - Validation phía client và server (thông báo lỗi chi tiết, giữ lại dữ liệu khi lỗi).
    - Tự động đặt ảnh đầu tiên làm ảnh chính nếu không chọn.
    - Hiển thị thông báo thành công/lỗi sau khi tạo.

5. **Cập nhật thông tin phòng trọ**:

    - Quản trị viên có thể chỉnh sửa thông tin phòng qua form:
        - Các trường tương tự form tạo, nhưng điền sẵn dữ liệu hiện tại.
        - **Nhà trọ**: Có thể thay đổi nhà trọ (chọn từ danh sách nhà trọ "Hoạt động").
        - **Hình ảnh hiện tại**: Hiển thị danh sách ảnh với radio button để chọn ảnh chính, nút xóa từng ảnh (AJAX).
        - **Hình ảnh mới**: Thêm ảnh mới qua FilePond, chọn ảnh chính từ ảnh mới.
    - Validation đảm bảo tên phòng duy nhất trong nhà trọ, diện tích hợp lệ, và ảnh đúng định dạng.
    - Tự động cập nhật ảnh chính (đặt ảnh đầu tiên nếu không còn ảnh chính sau khi xóa).
    - Hiển thị thông báo thành công/lỗi sau khi cập nhật.

6. **Xóa phòng trọ**:

    - Quản trị viên có thể xóa phòng (soft delete) với xác nhận JavaScript.
    - Không cho phép xóa phòng có trạng thái "Đã thuê" để đảm bảo tính toàn vẹn dữ liệu.
    - Hiển thị thông báo thành công/lỗi sau khi xóa.
    - Phòng đã xóa được chuyển vào thùng rác.

7. **Quản lý phòng trọ trong thùng rác**:

    - Quản trị viên có thể xem danh sách phòng trọ đã xóa (thuộc một nhà trọ cụ thể) với thông tin:
        - **Số thứ tự (STT)**, **Ảnh chính**, **Tên phòng**, **Ghi chú**, **Trạng thái**.
        - **Thao tác**: Nút "Khôi phục" và "Xóa vĩnh viễn" với xác nhận JavaScript.
    - Hỗ trợ lọc và tìm kiếm tương tự danh sách phòng chính (tìm theo tên, trạng thái, sắp xếp).
    - Hiển thị thông báo thành công/lỗi sau khi khôi phục hoặc xóa vĩnh viễn.

8. **Khôi phục phòng trọ từ thùng rác**:

    - Quản trị viên có thể khôi phục phòng đã xóa với xác nhận.
    - Phòng được khôi phục giữ nguyên thông tin ban đầu (tên, giá, tiện nghi, ảnh, v.v.).
    - Hiển thị thông báo thành công/lỗi sau khi khôi phục.

9. **Xóa vĩnh viễn phòng trọ**:

    - Quản trị viên có thể xóa vĩnh viễn phòng từ thùng rác với xác nhận.
    - Xóa tất cả ảnh liên quan (file vật lý và bản ghi) và tiện nghi liên kết.
    - Hiển thị thông báo thành công/lỗi sau khi xóa.

10. **Xóa ảnh phòng trọ**:

    - Quản trị viên có thể xóa từng ảnh của phòng trong form chỉnh sửa (gửi yêu cầu AJAX).
    - Tự động đặt ảnh đầu tiên còn lại làm ảnh chính nếu xóa ảnh chính.
