// Composable cung cấp các hàm tiện ích cho hợp đồng
export function useContractUtils() {
    const config = useState('configs'); // Lấy cấu hình từ state

    // Kiểm tra hợp đồng có gần hết hạn hay không
    const isNearExpiration = endDate => {
        const nearExpirationDays = Number(config.value.is_near_expiration); // Số ngày gần hết hạn từ config
        if (nearExpirationDays === -1) {
            return true; // Luôn trả về true nếu không giới hạn
        }
        const today = new Date();
        const end = new Date(endDate);
        const diffInDays = Math.ceil((end - today) / (1000 * 60 * 60 * 24)); // Tính số ngày còn lại
        return diffInDays <= nearExpirationDays; // Kiểm tra nếu số ngày còn lại nhỏ hơn ngưỡng
    };

    // Lấy class cho mục hợp đồng dựa trên trạng thái
    const getItemClass = status => {
        const statusMap = {
            'Chờ xác nhận': 'pending-booking',
            'Chờ duyệt': 'pending-booking',
            'Chờ ký': 'pending-booking',
            'Chờ thanh toán tiền cọc': 'pending-booking',
            'Hoạt động': 'approved-booking',
            'Kết thúc': 'canceled-booking',
            'Huỷ bỏ': 'canceled-booking',
            'Kết thúc sớm': 'canceled-booking'
        };
        return statusMap[status] || ''; // Trả về class tương ứng hoặc rỗng
    };

    // Lấy class cho trạng thái hợp đồng
    const getStatusClass = status => {
        const statusClass = 'booking-status';
        const statusMap = {
            'Chờ xác nhận': 'pending',
            'Chờ duyệt': 'pending',
            'Chờ ký': 'pending',
            'Hoạt động': 'approved',
            'Kết thúc': 'canceled',
            'Huỷ bỏ': 'canceled',
            'Kết thúc sớm': 'canceled'
        };
        return `${statusClass} ${statusMap[status] || ''}`; // Trả về class trạng thái
    };

    // Lấy văn bản hành động dựa trên trạng thái hợp đồng
    const getActText = status => {
        const statusMap = {
            'Chờ xác nhận': 'Hoàn thiện thông tin',
            'Chờ ký': 'Ký hợp đồng',
            'Chờ duyệt': 'Xem chi tiết',
            'Chờ thanh toán tiền cọc': 'Xem chi tiết',
            'Hoạt động': 'Xem chi tiết',
            'Kết thúc': 'Xem chi tiết',
            'Huỷ bỏ': 'Xem chi tiết',
            'Kết thúc sớm': 'Xem chi tiết'
        };
        return statusMap[status] || ''; // Trả về văn bản hành động
    };

    return {
        isNearExpiration,
        getItemClass,
        getStatusClass,
        getActText
    };
}
