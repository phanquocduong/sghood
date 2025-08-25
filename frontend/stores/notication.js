import { defineStore } from 'pinia';
import { useAuthStore } from './auth';
import { useAppToast } from '~/composables/useToast';

// Định nghĩa store 'notification' để quản lý thông báo
export const useNotificationStore = defineStore('notification', () => {
    // Khởi tạo các biến reactive
    const notifications = ref([]); // Danh sách thông báo
    const loading = ref(false); // Trạng thái loading khi lấy dữ liệu
    const { $api } = useNuxtApp(); // Lấy instance API từ Nuxt
    const toast = useAppToast(); // Hàm hiển thị thông báo
    const authStore = useAuthStore(); // Store quản lý thông tin người dùng
    const totalPages = ref(1); // Tổng số trang thông báo
    const currentPage = ref(1); // Trang hiện tại

    // Hàm lấy danh sách thông báo từ API
    const fetchNotifications = async (page = 1) => {
        const userId = authStore.user?.id; // Lấy ID người dùng từ authStore
        if (!userId) return; // Thoát nếu không có userId
        loading.value = true; // Bật trạng thái loading
        try {
            // Gửi yêu cầu GET để lấy thông báo của người dùng
            const res = await $api(`/notifications/user/${userId}?page=${page}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value // Thêm token để xác thực
                }
            });

            // Kiểm tra dữ liệu trả về có hợp lệ không
            if (!res || !res.data || !Array.isArray(res.data.data)) {
                notifications.value = []; // Đảm bảo notifications là mảng rỗng nếu dữ liệu không hợp lệ
                return;
            }

            const list = res.data.data; // Lấy danh sách thông báo
            currentPage.value = res.data.current_page; // Cập nhật trang hiện tại
            totalPages.value = res.data.last_page; // Cập nhật tổng số trang
            // Chuyển đổi dữ liệu API thành định dạng phù hợp
            notifications.value = list.map(item => ({
                id: item.id,
                title: item.title,
                content: item.content,
                unread: item.status === 'Chưa đọc', // Kiểm tra trạng thái chưa đọc
                time: item.created_at // Thời gian tạo thông báo
            }));
        } catch (err) {
            // Xử lý lỗi
            if (err.response?.status === 404) {
                notifications.value = []; // Nếu không tìm thấy thông báo, đặt danh sách rỗng
            } else {
                toast.error('Lỗi khi lấy thông báo'); // Hiển thị thông báo lỗi
                console.error(err);
            }
        } finally {
            loading.value = false; // Tắt trạng thái loading
        }
    };

    // Hàm đánh dấu một thông báo là đã đọc
    const markAsRead = async id => {
        const index = notifications.value.findIndex(n => n.id === id); // Tìm vị trí thông báo
        if (index === -1 || !notifications.value[index].unread) return; // Thoát nếu không tìm thấy hoặc đã đọc
        try {
            // Gửi yêu cầu POST để đánh dấu thông báo đã đọc
            const res = await $api(`/notifications/${id}/mark-as-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value // Thêm token xác thực
                },
                body: { _method: 'PATCH' }
            });
            if (res.status === false) {
                toast.error(res.message || 'Lỗi khi đánh dấu đã đọc'); // Hiển thị lỗi nếu có
                return;
            }
            notifications.value[index].unread = false; // Cập nhật trạng thái thông báo
        } catch (e) {
            console.error(e); // Ghi log lỗi
        }
    };

    // Hàm đánh dấu tất cả thông báo là đã đọc
    const onMarkAllAsRead = async () => {
        try {
            // Gửi yêu cầu POST để đánh dấu tất cả thông báo
            const res = await $api('/notifications/mark-all-as-read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value // Thêm token xác thực
                },
                body: { _method: 'PATCH' }
            });
            if (res.status === false) {
                toast.error(res.message || 'Lỗi khi đánh dấu đã đọc'); // Hiển thị lỗi nếu có
                return;
            }
            // Cập nhật trạng thái tất cả thông báo thành đã đọc
            notifications.value.forEach(n => {
                n.unread = false;
            });
        } catch (e) {
            console.error(e); // Ghi log lỗi
        }
    };

    // Tính số lượng thông báo chưa đọc
    const unreadCount = computed(() => notifications.value.filter(n => n.unread).length);

    // Hàm xóa thông báo (hiện không được sử dụng trong giao diện)
    const removeNotification = index => {
        notifications.value.splice(index, 1); // Xóa thông báo tại vị trí index
    };

    // Trả về các giá trị và hàm để sử dụng trong ứng dụng
    return {
        notifications, // Danh sách thông báo
        loading, // Trạng thái loading
        fetchNotifications, // Hàm lấy thông báo
        removeNotification, // Hàm xóa thông báo
        markAsRead, // Hàm đánh dấu một thông báo đã đọc
        unreadCount, // Số lượng thông báo chưa đọc
        totalPages, // Tổng số trang
        currentPage, // Trang hiện tại
        onMarkAllAsRead // Hàm đánh dấu tất cả đã đọc
    };
});
