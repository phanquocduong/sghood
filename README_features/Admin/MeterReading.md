1. **Xem danh sách chỉ số điện nước đã ghi**:

    - Quản trị viên có thể xem danh sách tất cả các chỉ số điện nước đã được ghi, bao gồm:
        - **Tên phòng** liên kết với chỉ số.
        - **Tháng/năm** của chỉ số.
        - **Chỉ số điện (kWh)** và **nước (m³)**, định dạng số thập phân (2 chữ số).
        - **Ngày ghi chỉ số**
    - Danh sách hỗ trợ phân trang (mặc định 10 bản ghi/trang) để dễ dàng quản lý dữ liệu lớn.
    - Các bản ghi được hiển thị trong bảng với giao diện trực quan, có cột "Thao tác" cho biết trạng thái (hiện tại là "Đã cập nhật", không cho phép chỉnh sửa).

2. **Lọc và tìm kiếm chỉ số điện nước**:

    - Quản trị viên có thể lọc danh sách chỉ số theo:
        - **Tên phòng hoặc nhà trọ** (qua ô tìm kiếm).
        - **Tháng** (từ 1 đến 12).
        - **Năm** (tùy chọn từ năm sớm nhất có trong dữ liệu đến năm hiện tại).
    - Sắp xếp kết quả theo nhiều tiêu chí:
        - Tên phòng (A-Z hoặc Z-A).
        - Tháng (mới nhất hoặc cũ nhất).
        - Ngày tạo (mới nhất hoặc cũ nhất).
    - Tính năng lọc được thực hiện qua AJAX, cập nhật kết quả ngay lập tức mà không cần tải lại trang, cải thiện trải nghiệm người dùng.

3. **Xem danh sách phòng cần nhập chỉ số trong thời gian quy định**:

    - Trong **kỳ nhập chỉ số (từ ngày 28 đến ngày 5 tháng sau)**, quản trị viên có thể xem danh sách các phòng cần nhập chỉ số điện nước:
        - Phòng được nhóm theo **nhà trọ** để dễ quản lý.
        - Hiển thị thông tin: tên phòng, tháng/năm cần nhập, hợp đồng hiện tại (ngày hết hạn).
        - Số lượng phòng cần nhập được hiển thị trong thông báo (ví dụ: "Có X phòng cần nhập chỉ số").
    - Giao diện sử dụng **accordion** để hiển thị danh sách phòng theo từng nhà trọ, với khả năng thu gọn/mở rộng để tiết kiệm không gian.

4. **Xem danh sách phòng có hợp đồng sắp hết hạn**:

    - Ngoài kỳ nhập chỉ số, quản trị viên có thể xem danh sách các phòng có hợp đồng **sắp hết hạn trong 3 ngày** và chưa nhập chỉ số cho tháng hiện tại.
    - Thông tin hiển thị:
        - Tên phòng, tháng/năm hiện tại, ngày hết hạn hợp đồng, và số ngày còn lại (hiển thị dạng badge).
    - Thông báo cảnh báo (alert) cho biết số lượng phòng cần nhập chỉ số, giúp quản trị viên ưu tiên xử lý trước khi hợp đồng hết hạn.

5. **Nhập chỉ số điện nước cho nhiều phòng cùng lúc**:

    - Quản trị viên có thể nhập chỉ số điện (kWh) và nước (m³) cho nhiều phòng trong một nhà trọ thông qua **modal**:
        - Modal hiển thị tên nhà trọ, kỳ nhập (tháng/năm), và danh sách các phòng cần nhập.
        - Phòng được chia thành nhóm (mỗi nhóm tối đa 10 phòng) với giao diện accordion để dễ quản lý khi số lượng phòng lớn.
        - Hỗ trợ nhập số thập phân (step 0.01) cho chỉ số điện và nước.
    - Sau khi nhập, hệ thống tự động tạo **hóa đơn** tương ứng cho từng phòng.

6. **Tự động tạo hóa đơn từ chỉ số điện nước**:

    - Khi chỉ số được lưu, hệ thống tự động tạo hóa đơn hàng tháng với các khoản phí:
        - **Phí phòng**: Dựa trên giá phòng (tính tỷ lệ nếu là tháng đầu/cuối của hợp đồng).
        - **Phí điện/nước**: Tính dựa trên lượng tiêu thụ (chỉ số hiện tại trừ chỉ số tháng trước) và đơn giá từ nhà trọ.
        - **Phí dịch vụ**: Bao gồm phí đỗ xe, vệ sinh, internet, dịch vụ (tính tỷ lệ nếu cần).
    - Hóa đơn bao gồm:
        - Mã hóa đơn duy nhất.
        - Tổng số tiền và trạng thái (mặc định "Chưa trả").
    - Thông báo thành công hiển thị số lượng phòng đã cập nhật và mã hóa đơn được tạo.

7. **Gửi thông báo hóa đơn cho người dùng**:

    - Sau khi tạo hóa đơn, hệ thống tự động:
        - Gửi thông báo email và thông báo đẩy đến người dùng liên quan, thông báo về hóa đơn mới với chi tiết.
        - Tạo bản ghi thông báo trong cơ sở dữ liệu với trạng thái "Chưa đọc".
    - Các thông báo này giúp người dùng nắm bắt kịp thời nghĩa vụ thanh toán.

8. **Kiểm tra thời gian nhập chỉ số**:

    - Hệ thống giới hạn thời gian nhập chỉ số trong khoảng **từ ngày 28 đến ngày 5 tháng sau**, đảm bảo tính nhất quán trong quản lý.
    - Ngoài khoảng thời gian này, chỉ hiển thị phòng có hợp đồng sắp hết hạn (trong 3 ngày).

9. **Xử lý lỗi và xác thực dữ liệu**:

    - Kiểm tra đầu vào nghiêm ngặt:
        - Phòng phải tồn tại và có hợp đồng hoạt động.
        - Chỉ số không được trùng lặp cho cùng phòng, tháng, năm.
        - Chỉ số điện/nước phải là số dương và trong giới hạn cho phép.
    - Nếu nhập sai, modal sẽ mở lại với dữ liệu đã nhập trước đó, hiển thị thông báo lỗi cụ thể (bằng tiếng Việt) cho từng trường.

10. **Giao diện thân thiện và tối ưu**:

    - Giao diện sử dụng **card** và **accordion** để hiển thị danh sách phòng theo nhà trọ, dễ dàng mở rộng khi có nhiều dữ liệu.
    - Modal nhập chỉ số hỗ trợ cuộn (scrollable) khi có nhiều phòng, với các trường nhập được nhóm gọn gàng.
    - Thông báo thành công/lỗi hiển thị dưới dạng **alert** với biểu tượng và nút đóng, dễ nhận biết.
    - Hỗ trợ **AJAX** cho lọc và gửi dữ liệu, giảm thời gian tải trang và cải thiện trải nghiệm.

11. **Quản lý hợp đồng và tính phí tỷ lệ**:

    - Hệ thống tự động tính phí tỷ lệ cho tháng đầu tiên hoặc cuối cùng của hợp đồng dựa trên số ngày sử dụng (ví dụ: nếu hợp đồng bắt đầu giữa tháng, chỉ tính phí từ ngày bắt đầu).
    - Các phí (phòng, đỗ xe, vệ sinh, internet, dịch vụ) được điều chỉnh theo tỷ lệ, đảm bảo công bằng.
