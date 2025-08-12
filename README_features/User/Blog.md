1. **Xem danh sách bài viết với phân trang và lọc**

-   Người dùng có thể xem danh sách bài viết (blog) với thông tin như tiêu đề, hình ảnh đại diện (thumbnail), nội dung, và thời gian tạo.
-   Hỗ trợ phân trang kèm thông tin về trang hiện tại, tổng số trang, và tổng số bài viết.
-   Cho phép lọc bài viết theo danh mục và tìm kiếm theo từ khóa trong tiêu đề hoặc nội dung.
-   Hiển thị danh sách các danh mục bài viết để hỗ trợ người dùng chọn lọc nhanh.

2. **Xem chi tiết một bài viết**

-   Người dùng có thể xem chi tiết một bài viết bằng cách click vào 1 bài viết, bao gồm thông tin như tiêu đề, nội dung, hình ảnh đại diện, và thời gian tạo.
-   Nếu bài viết không tồn tại, hệ thống trả về thông báo lỗi rõ ràng.

3. **Xem bài viết liên quan**

-   Người dùng có thể xem danh sách các bài viết liên quan dựa trên danh mục của bài viết hiện tại, với giới hạn mặc định là 5 bài.
-   Các bài liên quan được sắp xếp theo thời gian tạo (mới nhất trước), không bao gồm bài viết hiện tại.

4. **Xem bài viết phổ biến**

-   Người dùng có thể xem danh sách các bài viết phổ biến nhất (dựa trên số lượt xem - views), với giới hạn mặc định là 5 bài.
-   Sắp xếp theo số lượt xem giảm dần, sau đó theo thời gian tạo (mới nhất trước).

5. **Tăng lượt xem bài viết**

-   Người dùng khi truy cập một bài viết sẽ tự động tăng lượt xem (views) của bài đó, giúp hệ thống theo dõi mức độ phổ biến.

6. **Xem danh sách bình luận của bài viết**

-   Người dùng có thể xem danh sách bình luận của một bài viết (theo slug), bao gồm bình luận gốc và các bình luận trả lời.
-   Mỗi bình luận hiển thị thông tin: nội dung, thời gian tạo/cập nhật, số lượt thích, không thích, thông tin người dùng (tên, avatar).
-   Hỗ trợ phân trang (mặc định 10 bình luận/trang) với thông tin về trang hiện tại, tổng số trang, và tổng bình luận.

7. **Gửi bình luận mới cho bài viết**

-   Người dùng đã đăng nhập có thể gửi bình luận mới cho một bài viết, với nội dung được xác thực (không để trống, phải là chuỗi).
-   Hệ thống chống spam bằng cách giới hạn tần suất bình luận (phải cách 30 giây so với bình luận trước).

8. **Trả lời bình luận**

-   Người dùng đã đăng nhập có thể trả lời một bình luận hiện có, với nội dung được xác thực và kiểm tra hợp lệ.
-   Cũng áp dụng chống spam (30 giây giữa các bình luận).

9. **Chỉnh sửa bình luận**

-   Người dùng có thể chỉnh sửa bình luận của chính mình, với nội dung mới được xác thực (không để trống, phải là chuỗi).
-   Chỉ người tạo bình luận mới có quyền chỉnh sửa, đảm bảo bảo mật.

10. **Xóa bình luận**

-   Người dùng có thể xóa bình luận của chính mình.

11. **Tương tác (thích/không thích) với bình luận**

-   Người dùng đã đăng nhập có thể thích (like) hoặc không thích (dislike) một bình luận, tăng số lượt thích hoặc không thích tương ứng.
-   Hệ thống kiểm tra loại phản hồi hợp lệ và yêu cầu đăng nhập.
