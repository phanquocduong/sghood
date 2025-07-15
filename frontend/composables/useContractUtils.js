export function useContractUtils() {
    const isNearExpiration = endDate => {
        const today = new Date();
        const end = new Date(endDate);
        const diffInDays = Math.ceil((end - today) / (1000 * 60 * 60 * 24));
        return diffInDays <= 15 && diffInDays >= 0;
    };

    const getItemClass = status => {
        const statusMap = {
            'Chờ xác nhận': 'pending-booking',
            'Chờ duyệt': 'pending-booking',
            'Chờ chỉnh sửa': 'pending-booking',
            'Chờ ký': 'pending-booking',
            'Chờ thanh toán tiền cọc': 'pending-booking',
            'Hoạt động': 'approved-booking',
            'Kết thúc': 'canceled-booking',
            'Huỷ bỏ': 'canceled-booking'
        };
        return statusMap[status] || '';
    };

    const getStatusClass = status => {
        const statusClass = 'booking-status';
        const statusMap = {
            'Chờ xác nhận': 'pending',
            'Chờ duyệt': 'pending',
            'Chờ chỉnh sửa': 'pending',
            'Chờ ký': 'pending',
            'Hoạt động': 'approved',
            'Kết thúc': 'canceled',
            'Huỷ bỏ': 'canceled'
        };
        return `${statusClass} ${statusMap[status] || ''}`;
    };

    const getExtensionStatusClass = extensionStatus => {
        const statusClass = 'booking-status';
        const statusMap = {
            'Chờ duyệt': 'pending extension-status',
            'Hoạt động': 'approved',
            'Từ chối': 'canceled extension-status'
        };
        return `${statusClass} ${statusMap[extensionStatus] || ''}`;
    };

    const getCheckoutStatusClass = checkoutStatus => {
        const statusClass = 'booking-status';
        const statusMap = {
            'Chờ kiểm kê': 'pending checkout-status',
            'Đã kiểm kê': 'approved'
        };
        return `${statusClass} ${statusMap[checkoutStatus] || ''}`;
    };

    const formatExtensionStatus = status => {
        const statusMap = {
            'Chờ duyệt': 'Chờ duyệt gia hạn',
            'Hoạt động': 'Đã gia hạn',
            'Từ chối': 'Từ chối gia hạn'
        };
        return statusMap[status] || status;
    };

    const formatCheckoutStatus = status => {
        const statusMap = {
            'Chờ kiểm kê': 'Chờ kiểm kê trả phòng',
            'Đã kiểm kê': 'Đã kiểm kê'
        };
        return statusMap[status] || status;
    };

    const getActText = status => {
        const statusMap = {
            'Chờ xác nhận': 'Hoàn thiện thông tin',
            'Chờ chỉnh sửa': 'Chỉnh sửa thông tin',
            'Chờ ký': 'Ký hợp đồng',
            'Chờ duyệt': 'Xem chi tiết',
            'Chờ thanh toán tiền cọc': 'Xem chi tiết',
            'Hoạt động': 'Xem chi tiết',
            'Kết thúc': 'Xem chi tiết'
        };
        return statusMap[status] || '';
    };

    return {
        isNearExpiration,
        getItemClass,
        getStatusClass,
        getExtensionStatusClass,
        getCheckoutStatusClass,
        formatExtensionStatus,
        formatCheckoutStatus,
        getActText
    };
}
