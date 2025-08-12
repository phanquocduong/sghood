1. **Xem danh sách giao dịch**:

    - Quản trị viên có thể xem danh sách tất cả các giao dịch với thông tin:
        - **STT**: Số thứ tự bản ghi trong trang hiện tại.
        - **Mã hóa đơn**: Mã hóa đơn liên kết (hiển thị chữ đậm màu xanh nếu có, hoặc "N/A" nếu không liên kết).
        - **Số tiền**: Số tiền giao dịch, hiển thị dấu "+" (xanh lá) cho tiền vào (`in`) và dấu "−" (đỏ) cho tiền ra (`out`), định dạng VNĐ (ví dụ: +2.500.000 VND).
        - **Mã tham chiếu**: Mã tham chiếu của giao dịch (hoặc "N/A").
        - **Thời gian giao dịch**: Ngày và giờ giao dịch.
        - **Chi tiết**: Nút "Xem chi tiết" (biểu tượng con mắt) để mở modal thông tin chi tiết.
    - Hỗ trợ **phân trang** (mặc định 15 bản ghi/trang).
    - Hiển thị tổng số bản ghi (badge màu trắng/xanh) và liên kết đến hệ thống Sepay (`https://my.sepay.vn/transactions`) để xem chi tiết bên thứ ba.

2. **Lọc và tìm kiếm giao dịch**:

    - Quản trị viên có thể lọc danh sách giao dịch theo:
        - **Tìm kiếm**: Tìm kiếm theo nội dung giao dịch (`content`) hoặc mã hóa đơn (`code`).
        - **Loại giao dịch**: Lọc theo tiền vào (`in`) hoặc tiền ra (`out`) qua dropdown.
        - **Tháng**: Lọc theo tháng cụ thể (1-12) qua dropdown (ví dụ: "Tháng 8").
        - **Năm**: Lọc theo năm cụ thể (từ năm hiện tại đến 5 năm trước) qua dropdown.
    - Form lọc sử dụng **GET request**, giữ lại các tham số lọc khi chuyển trang.
    - Giao diện lọc trực quan với input tìm kiếm, dropdown tháng/năm/loại giao dịch, và nút "Lọc" có biểu tượng bộ lọc.
    - **Tự động gửi form** khi thay đổi dropdown (tháng, năm, loại giao dịch) nhờ JavaScript, cải thiện trải nghiệm người dùng.
    - Hiển thị thông tin bộ lọc đang áp dụng (ví dụ: "Tháng 8/2025 | Loại giao dịch: Tiền vào | Tìm kiếm: ABC123").

3. **Xem thống kê giao dịch**:

    - Quản trị viên có thể xem thống kê giao dịch trong các card thống kê:
        - **Tổng giao dịch**: Số lượng giao dịch và tổng số tiền (định dạng VNĐ).
        - **Tiền vào (IN)**: Số giao dịch vào và tổng số tiền vào (màu xanh lam).
        - **Tiền ra (OUT)**: Số giao dịch ra và tổng số tiền ra (màu vàng).
        - **Số dư**: Số tiền vào trừ số tiền ra, hiển thị màu đỏ nếu âm.
    - Thống kê được cập nhật động theo bộ lọc áp dụng (tìm kiếm, tháng, năm, loại giao dịch).

4. **Xem chi tiết giao dịch**:

    - Quản trị viên có thể nhấp vào nút "Xem chi tiết" để mở modal hiển thị thông tin chi tiết giao dịch qua **AJAX**:
        - **Thông tin giao dịch**:
            - ID giao dịch.
            - Nội dung giao dịch (hoặc "N/A").
            - Loại giao dịch (badge xanh lá cho `in`, vàng cho `out`).
            - Số tiền (màu xanh lá cho `in`, đỏ cho `out`, định dạng VNĐ).
            - Mã tham chiếu (hoặc "N/A").
            - Ngày tạo.
        - **Thông tin hóa đơn** (nếu liên kết):
            - Mã hóa đơn.
            - Trạng thái hóa đơn.
            - Tổng tiền hóa đơn (định dạng VNĐ).
            - Tháng/Năm của hóa đơn.
            - Nếu không có hóa đơn, hiển thị thông báo "Không liên kết với hóa đơn".
    - Modal có giao diện đẹp với header xanh, nội dung chia 2 cột (thông tin giao dịch và hóa đơn), và nút "Đóng".
    - Hiển thị spinner loading trong khi tải dữ liệu qua AJAX, đảm bảo trải nghiệm mượt mà.
    - Modal tự động reset nội dung khi đóng để tránh hiển thị dữ liệu cũ.
