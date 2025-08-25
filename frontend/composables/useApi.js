// Hàm composable useApi cung cấp các tiện ích để xử lý API
export function useApi() {
    // Hàm xử lý lỗi từ backend
    const handleBackendError = (error, toast) => {
        const data = error.response?._data; // Lấy dữ liệu từ response lỗi
        if (data?.error) {
            toast.error(data.error); // Hiển thị thông báo lỗi nếu có error trong response
            return;
        }
        if (data?.errors) {
            // Hiển thị từng lỗi nếu response chứa mảng errors
            Object.values(data.errors).forEach(err => toast.error(err[0]));
            return;
        }
        // Hiển thị thông báo lỗi mặc định nếu không có thông tin lỗi cụ thể
        toast.error('Đã có lỗi xảy ra. Vui lòng thử lại.');
    };

    // Trả về hàm xử lý lỗi để sử dụng trong các composable khác
    return { handleBackendError };
}
