export const useFormatDate = () => {
    // Hàm định dạng ngày
    const formatDate = dateString => {
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
    };

    // Hàm định dạng giờ
    const formatTime = dateString => {
        const date = new Date(dateString);
        return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
    };

    // Hàm định dạng ngày giờ
    const formatDateTime = timestamp => {
        const date = new Date(timestamp);
        return date.toLocaleString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });
    };

    return {
        formatDate,
        formatTime,
        formatDateTime
    };
};
