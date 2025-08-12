1. **Hủy hợp đồng**

    - Người dùng có thể hủy hợp đồng khi hợp đồng ở trạng thái “Chờ xác nhận”.
    - Sau khi hủy, trạng thái hợp đồng được cập nhật thành “Huỷ bỏ” và người dùng nhận thông báo “Hủy hợp đồng thành công”.
    - Quản trị viên được thông báo qua email và thông báo đẩy.

2. **Hoàn thiện thông tin hợp đồng**

    - Người dùng có thể cập nhật thông tin cá nhân (CCCD) của mình vào nội dung hợp đồng khi hợp đồng ở trạng thái “Chờ xác nhận” hoặc chỉnh sửa và gửi lại để duyệt sau khi admin đã chấp nhận yêu cầu đặt phòng và đã tạo bản hợp đồng mẫu.
    - Người dùng có thể tải lên 2 ảnh căn cước công dân (CCCD) để hệ thống trích xuất thông tin tự động (tên, số CCCD, ngày sinh, ngày cấp, nơi cấp, địa chỉ thường trú) hoặc chọn nhập thông tin và upload ảnh CCCD thủ công sau khi hệ thống quét lỗi nhiều lần.
    - Hình ảnh giấy tờ tuỳ thân sẽ được chuyển đổi sang định dạng WebP và mã hóa (encrypted) trước khi lưu trữ để đảm bảo bảo mật.
    - Các tài liệu định danh được lưu trong hệ thống dưới dạng an toàn, chỉ truy cập được bởi người dùng hoặc quản trị viên có quyền.
    - Sau khi cập nhật, hợp đồng chuyển sang trạng thái “Chờ duyệt” hoặc “Chờ duyệt thủ công” tùy thuộc vào phương thức nhập thông tin CCCD.
    - Quản trị viên được thông báo qua email và thông báo đẩy khi hợp đồng được gửi để duyệt.

3. **Ký hợp đồng**

    - Người dùng có thể ký hợp đồng khi hợp đồng ở trạng thái “Chờ ký” bằng cách cung cấp chữ ký số (lưu dưới dạng ảnh PNG).
    - Sau khi ký, hợp đồng chuyển sang trạng thái “Chờ thanh toán tiền cọc” và một hóa đơn tiền cọc được tạo tự động.
    - Người dùng nhận thông báo “Hợp đồng đã được ký thành công. Vui lòng thanh toán tiền cọc”, sau đó chuyển hướng sang trang thanh toán.
    - Quản trị viên được thông báo qua email và thông báo đẩy.

4. **Thanh toán tiền cọc**

    - Người dùng thanh toán tiền cọc thông qua cổng thanh toán Sepay.
    - Sau khi thanh toán thành công, hợp đồng chuyển sang trạng thái “Hoạt động”, phòng được cập nhật trạng thái thành “Đã thuê”, và người dùng được nâng cấp vai trò thành “Người thuê”.
    - Quản trị viên nhận thông báo qua email và thông báo đẩy khi tiền cọc được thanh toán.

5. **Xem danh sách hợp đồng**

    - Người dùng có thể xem danh sách tất cả các hợp đồng của mình.
    - Danh sách hiển thị thông tin chi tiết bao gồm: ID hợp đồng, tên phòng, tên nhà trọ, hình ảnh của phòng, ngày bắt đầu, ngày kết thúc, trạng thái hợp đồng, số tiền cọc, giá thuê, ngày ký hợp đồng, ngày kết thúc sớm (nếu có).

6. **Tải PDF hợp đồng**

    - Người dùng có thể tải file PDF của hợp đồng khi hợp đồng ở trạng thái “Hoạt động”.

7. **Xem chi tiết hợp đồng**

    - Người dùng có thể xem chi tiết một hợp đồng cụ thể qua nội dung của hợp đồng và danh sách các phụ lục gia hạn hợp đồng (nếu có) với thông tin như ngày kết thúc mới, giá thuê mới, nội dung phụ lục, và trạng thái.
    - Hệ thống đảm bảo người dùng chỉ xem được hợp đồng của chính mình.

8. **Kết thúc hợp đồng sớm**

    - Người dùng có thể yêu cầu kết thúc hợp đồng sớm khi hợp đồng ở trạng thái “Hoạt động”, miễn là hợp đồng chưa hết hạn, không có yêu cầu gia hạn hoặc trả phòng đang chờ duyệt (yêu cầu trả phòng chưa bị hủy), và tất cả hóa đơn đã được thanh toán đầy đủ.
    - Hệ thống kiểm tra thêm: Nếu ngày hiện tại > 5, phải có hóa đơn tháng hiện tại và đã thanh toán; nếu ngày hiện tại từ 1-5, phải có hóa đơn tháng trước và đã thanh toán. Nếu không thỏa mãn, người dùng nhận thông báo lỗi tương ứng (ví dụ: "Hóa đơn cho tháng hiện tại chưa được tạo. Vui lòng chờ đến khi hóa đơn được tạo và thanh toán thành công.").
    - Sau khi yêu cầu thành công, trạng thái hợp đồng được cập nhật thành “Kết thúc sớm”, thời gian kết thúc sớm được ghi nhận trong dữ liệu và người dùng nhận thông báo “Hợp đồng của bạn đã được kết thúc sớm.”.
    - Quản trị viên được thông báo qua email và thông báo đẩy.

9. **Gia hạn hợp đồng**

    - Người dùng có thể yêu cầu gia hạn hợp đồng khi hợp đồng ở trạng thái “Hoạt động” và trong vòng 15 ngày trước khi hợp đồng hết hạn.
    - Người dùng chọn số tháng gia hạn (tối thiểu 1 tháng), và hệ thống tạo phụ lục hợp đồng với ngày kết thúc mới và giá thuê phòng hiện tại.
    - Phụ lục hợp đồng được tạo ở trạng thái “Chờ duyệt”, và người dùng nhận thông báo “Yêu cầu gia hạn hợp đồng đã được gửi”.
    - Quản trị viên được thông báo qua email và thông báo đẩy.

10. **Xem danh sách yêu cầu gia hạn hợp đồng**

    - Người dùng có thể xem danh sách các yêu cầu gia hạn hợp đồng của mình, bao gồm: ID yêu cầu, ID hợp đồng, ngày kết thúc mới, giá thuê mới, nội dung phụ lục, trạng thái và lý do admin từ chối (nếu có).
    - Người dùng có thể lọc theo trạng thái và sắp xếp theo thời gian (mới nhất hoặc cũ nhất).

11. **Hủy yêu cầu gia hạn hợp đồng**

    - Người dùng có thể hủy yêu cầu gia hạn khi yêu cầu ở trạng thái “Chờ duyệt”.
    - Sau khi hủy, trạng thái yêu cầu được cập nhật thành “Huỷ bỏ” và người dùng nhận thông báo “Hủy gia hạn thành công”.
    - Quản trị viên được thông báo qua email và thông báo đẩy.

12. **Yêu cầu trả phòng**

    - Người dùng có thể gửi yêu cầu trả phòng cho hợp đồng ở trạng thái “Hoạt động”, miễn là hợp đồng có tiền cọc và không có yêu cầu gia hạn hoặc trả phòng đang chờ duyệt và trong vòng 15 ngày trước khi hợp đồng hết hạn.
    - Người dùng chọn ngày dự kiến trả phòng (trong vòng 30 ngày sau ngày kết thúc hợp đồng) và phương thức hoàn tiền (tiền mặt hoặc chuyển khoản). Nếu chọn chuyển khoản, người dùng cung cấp thông tin ngân hàng (tên ngân hàng, số tài khoản, tên chủ tài khoản).
    - Hệ thống sẽ tự động tạo mã QR để admin có thể dễ dàng chuyển khoản hoàn tiền (nếu chọn chuyển khoản) và yêu cầu trả phòng ở trạng thái “Chờ kiểm kê”.
    - Người dùng nhận thông báo “Yêu cầu trả phòng đã được gửi”, và quản trị viên được thông báo qua email và thông báo đẩy.

13. **Xem danh sách yêu cầu trả phòng**

    - Người dùng có thể xem danh sách các yêu cầu trả phòng của mình, bao gồm: ID yêu cầu, ID hợp đồng, ngày trả phòng, chi tiết kiểm kê, số tiền khấu trừ, số tiền hoàn cuối cùng, trạng thái kiểm kê (Chờ kiểm kê, Đồng ý, Từ chối), lý do từ chối, trạng thái rời phòng, thông tin ngân hàng, mã QR, trạng thái hoàn tiền, tên phòng, tên nhà trọ, hình ảnh phòng, và số tiền cọc của hợp đồng.

14. **Cập nhật thông tin hoàn tiền**

    - Người dùng có thể cập nhật lại thông tin tài khoản ngân hàng (khi đã chọn phương thức chuyển khoản) cho yêu cầu trả phòng ở trạng thái “Chờ xử lý”, hệ thống sẽ cập nhật lại mã QR chuyển khoản.
    - Người dùng nhận thông báo “Cập nhật thông tin hoàn tiền thành công”, và quản trị viên được thông báo qua email và thông báo đẩy.

15. **Hủy yêu cầu trả phòng**

    - Người dùng có thể hủy yêu cầu trả phòng, và trạng thái yêu cầu được cập nhật với thời gian hủy.
    - Người dùng nhận thông báo “Hủy yêu cầu trả phòng thành công”, và quản trị viên được thông báo qua email và thông báo đẩy.

16. **Xác nhận hoặc từ chối kết quả kiểm kê**

    - Người dùng có thể xác nhận (Đồng ý) hoặc từ chối kết quả kiểm kê của yêu cầu trả phòng, cung cấp lý do từ chối nếu cần.
    - Người dùng nhận thông báo “Xác nhận kiểm kê thành công” hoặc “Từ chối kiểm kê thành công”, và quản trị viên được thông báo qua email và thông báo đẩy.

17. **Xác nhận rời phòng**

    - Sau khi đồng ý với kết quả kiểm kê, người dùng có thể xác nhận đã rời phòng.
    - Người dùng nhận thông báo “Xác nhận đã rời phòng thành công”.
    - Quản trị viên được thông báo qua email và thông báo đẩy.
