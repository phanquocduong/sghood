// ~/stores/notification.ts
import { defineStore } from 'pinia';
import { useAuthStore } from './auth';
import { useAppToast } from '~/composables/useToast';

export const useNotificationStore = defineStore('notification', () => {
    const notifications = ref([]);
    const loading = ref(false);
    const { $api } = useNuxtApp();
    const toast = useAppToast();
    const authStore = useAuthStore();

    const fetchNotifications = async () => {
        const userId = authStore.user?.id;
        if (!userId) return;
        loading.value = true;
        try {
            const res = await $api(`/notifications/user/${userId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
                }
            });

            if (!res || !res.data || !Array.isArray(res.data.data)) {
                notifications.value = []; // ← Đảm bảo luôn là mảng
                return;
            }
       

            const list = res.data.data;
            notifications.value = list.map(item => ({
                id: item.id,
                title: item.title,
                content: item.content,
                unread: item.status === 'Chưa đọc',
                time: item.created_at
            }));
        } catch (err) {
            if (err.response?.status === 404) {
                notifications.value= [];
            }else{
                toast.error('Lỗi khi lấy thông báo');
                console.log(err);
            }
        } finally {
            loading.value = false;
        }
    };
    const removeNotification = index => {
        notifications.value.splice(index, 1);
    };

    const markAsRead = async id => {
        const index = notifications.value.findIndex(n => n.id === id);
        if (index === -1 || !notifications.value[index].unread) return;
        try {
            const res = await $api(`/notifications/${id}/mark-as-read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
                }
            });
            if (res.status === false) {
                toast.error(res.message || 'Lỗi khi đánh dấu đã đọc');
                return;
            }
            notifications.value[index].unread = false;
        } catch (e) {
            console.log(e);
        }
    };
    const unreadCount = computed(() => notifications.value.filter(n => n.unread).length);
    return {
        notifications,
        loading,
        fetchNotifications,
        removeNotification,
        markAsRead,
        unreadCount
    };
});
