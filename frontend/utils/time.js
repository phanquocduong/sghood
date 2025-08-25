import dayjs from 'dayjs'; // Nhập thư viện dayjs để xử lý thời gian
import relativeTime from 'dayjs/plugin/relativeTime'; // Plugin để định dạng thời gian tương đối (ví dụ: "5 phút trước")
import utc from 'dayjs/plugin/utc'; // Plugin để hỗ trợ xử lý thời gian UTC
import 'dayjs/locale/vi'; // Nhập ngôn ngữ tiếng Việt cho định dạng thời gian

// Kích hoạt plugin relativeTime để sử dụng hàm fromNow
dayjs.extend(relativeTime);
// Kích hoạt plugin UTC để xử lý thời gian theo múi giờ UTC
dayjs.extend(utc);
// Thiết lập ngôn ngữ mặc định là tiếng Việt
dayjs.locale('vi');

// Hàm định dạng thời gian thành dạng tương đối (ví dụ: "vài giây trước", "1 giờ trước")
export function formatTimeAgo(time) {
    const parsedTime = dayjs(time); // Chuyển đổi thời gian đầu vào thành đối tượng dayjs
    return parsedTime.fromNow(); // Trả về chuỗi thời gian tương đối so với hiện tại
}
