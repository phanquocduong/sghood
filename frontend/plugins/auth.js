import { useAuthStore } from '~/stores/auth';

// Plugin khởi tạo xác thực
export default defineNuxtPlugin(async nuxtApp => {
    // Chỉ chạy ở client-side
    if (process.client) {
        const authStore = useAuthStore(); // Lấy store xác thực

        try {
            // Kiểm tra trạng thái xác thực của người dùng
            await authStore.checkAuth();
        } catch (error) {
            console.error('Auth initialization failed:', error);
            // Chỉ ghi log lỗi, không throw
        }
    }
});
