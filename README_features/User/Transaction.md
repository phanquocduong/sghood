### 1. Xem danh sách giao dịch

-   **Mô tả**: Người dùng có thể xem toàn bộ lịch sử giao dịch liên quan đến các hóa đơn của mình.
-   **Chi tiết**:
    -   Hiển thị thông tin mỗi giao dịch bao gồm: số tiền (với dấu "+" cho giao dịch thu, "-" cho giao dịch chi), thời gian giao dịch, mã hóa đơn liên quan, mã tham chiếu.
    -   Giao dịch được trình bày trong danh sách với biểu tượng ví tiền (wallet) để dễ nhận diện.
    -   Hỗ trợ phân trang để người dùng dễ dàng duyệt qua danh sách dài.
    -   Hiển thị thông báo “Chưa có giao dịch nào” nếu danh sách trống.

---

### 2. Lọc và sắp xếp giao dịch

-   **Mô tả**: Người dùng có thể lọc và sắp xếp danh sách giao dịch để tìm kiếm nhanh chóng.
-   **Chi tiết**:
    -   Lọc theo loại giao dịch:
        -   Tất cả (hiển thị cả giao dịch thu và chi).
        -   Giao dịch chi (transfer_type = 'in').
        -   Giao dịch thu (transfer_type = 'out').
    -   Sắp xếp theo:
        -   Mặc định (mới nhất theo thời gian tạo).
        -   Cũ nhất (theo thời gian giao dịch tăng dần).
        -   Mới nhất (theo thời gian giao dịch giảm dần).
    -   Bộ lọc được áp dụng tự động khi thay đổi, và danh sách giao dịch được cập nhật ngay lập tức.
