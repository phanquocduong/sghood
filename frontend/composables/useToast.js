// Composable cung cấp các phương thức hiển thị thông báo
export const useAppToast = () => {
    const { $toast } = useNuxtApp(); // Lấy đối tượng toast từ Nuxt plugin
    return {
        success: (message, options = {}) => $toast.success(message, options), // Hiển thị thông báo thành công
        error: (message, options = {}) => $toast.error(message, options), // Hiển thị thông báo lỗi
        info: (message, options = {}) => $toast.info(message, options), // Hiển thị thông báo thông tin
        warning: (message, options = {}) => $toast.warning(message, options) // Hiển thị thông báo cảnh báo
    };
};
