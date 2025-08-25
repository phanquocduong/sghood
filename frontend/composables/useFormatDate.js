// Composable cung cấp các hàm định dạng ngày giờ
export const useFormatDate = () => {
    // Hàm định dạng ngày
    const formatDate = dateString => {
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' }); // Định dạng ngày theo chuẩn Việt Nam
    };

    // Hàm định dạng giờ
    const formatTime = dateString => {
        const date = new Date(dateString);
        return date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' }); // Định dạng giờ theo chuẩn Việt Nam
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
        }); // Định dạng ngày giờ theo chuẩn Việt Nam
    };

    return {
        formatDate,
        formatTime,
        formatDateTime
    };
};
