1.  **Gửi thông tin liên hệ qua biểu mẫu**

-   Người dùng có thể gửi thông tin liên hệ đến hệ thống bằng cách điền biểu mẫu gồm: họ tên, email, chủ đề, và nội dung tin nhắn.
-   Hệ thống kiểm tra dữ liệu đầu vào để đảm bảo:
    -   Họ tên: bắt buộc, là chuỗi, tối đa 255 ký tự.
    -   Email: bắt buộc, đúng định dạng email.
    -   Chủ đề: bắt buộc, là chuỗi, tối đa 255 ký tự.
    -   Nội dung: bắt buộc, là chuỗi.
-   Sau khi gửi thành công, email được gửi đến địa chỉ `sghoodvn@gmail.com` với nội dung từ biểu mẫu
-   Người dùng nhận được thông báo xác nhận "Liên hệ của bạn đã được gửi."

2. **Nhận thông báo lỗi nếu nhập sai thông tin**

-   Nếu người dùng nhập thiếu hoặc sai định dạng dữ liệu (ví dụ: email không hợp lệ, để trống nội dung), hệ thống trả về thông báo lỗi chi tiết.
-   Các lỗi được liệt kê rõ ràng, ví dụ: "Nội dung không được để trống", "Email phải là định dạng email hợp lệ".
