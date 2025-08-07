export function useContractUtils() {
    const config = useState('configs');
    const isNearExpiration = endDate => {
        const nearExpirationDays = Number(config.value.is_near_expiration);
        if (nearExpirationDays === -1) {
            return true;
        }
        const today = new Date();
        const end = new Date(endDate);
        const diffInDays = Math.ceil((end - today) / (1000 * 60 * 60 * 24));
        return diffInDays <= nearExpirationDays;
    };

    const getItemClass = status => {
        const statusMap = {
            'Chờ xác nhận': 'pending-booking',
            'Chờ duyệt': 'pending-booking',
            'Chờ duyệt thủ công': 'pending-booking',
            'Chờ ký': 'pending-booking',
            'Chờ thanh toán tiền cọc': 'pending-booking',
            'Hoạt động': 'approved-booking',
            'Kết thúc': 'canceled-booking',
            'Huỷ bỏ': 'canceled-booking',
            'Kết thúc sớm': 'canceled-booking'
        };
        return statusMap[status] || '';
    };

    const getStatusClass = status => {
        const statusClass = 'booking-status';
        const statusMap = {
            'Chờ xác nhận': 'pending',
            'Chờ duyệt': 'pending',
            'Chờ duyệt thủ công': 'pending',
            'Chờ ký': 'pending',
            'Hoạt động': 'approved',
            'Kết thúc': 'canceled',
            'Huỷ bỏ': 'canceled',
            'Kết thúc sớm': 'canceled'
        };
        return `${statusClass} ${statusMap[status] || ''}`;
    };

    const getActText = status => {
        const statusMap = {
            'Chờ xác nhận': 'Hoàn thiện thông tin',
            'Chờ ký': 'Ký hợp đồng',
            'Chờ duyệt': 'Xem chi tiết',
            'Chờ duyệt thủ công': 'Xem chi tiết',
            'Chờ thanh toán tiền cọc': 'Xem chi tiết',
            'Hoạt động': 'Xem chi tiết',
            'Kết thúc': 'Xem chi tiết',
            'Huỷ bỏ': 'Xem chi tiết',
            'Kết thúc sớm': 'Xem chi tiết'
        };
        return statusMap[status] || '';
    };

    return {
        isNearExpiration,
        getItemClass,
        getStatusClass,
        getActText
    };
}
