// ~/stores/notification.ts
import { defineStore } from 'pinia';
import { useAuthStore } from './auth';
import { useToast } from 'vue-toastification';

export const useNotificationStore = defineStore('notification', () => {
  const notifications = ref([]);
  const loading = ref(false);
  const { $api } = useNuxtApp();
  const toast = useToast();
  const authStore = useAuthStore();

  const fetchNotifications = async () => {
    const userId = authStore.user?.id;
    if (!userId) return;

    loading.value = true;
    try {
      const res = await $api(`notifications/user/${userId}`, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
        },
      });

     if (!res || !res.data || !Array.isArray(res.data.data)) {
    notifications.value = []; // ← Đảm bảo luôn là mảng
    return;
  }
   if (res?.status === false) {
      toast.error(` ${res.message || 'Lỗi khi lấy thông báo.'}`);
      return;
    }

  const list = res.data.data
  notifications.value = list.map(item => ({
    id: item.id,
    title: item.title,
    content: item.content,
    unread: item.status === 'Chưa đọc',
    time: new Date(item.created_at).toLocaleString(),
  }));
      }catch (err) {
      
      console.log(err)
    }finally{
      loading.value = false
    }
  };
const removeNotification = index => {
  notifications.value.splice(index, 1);
};

const markAsRead = index => {
  if (notifications.value[index].unread) {
    notifications.value[index].unread = false;
  };
  
};
const unreadCount = computed(() => notifications.value.filter(n => n.unread).length);
  return {
    notifications,
    loading,
    fetchNotifications,
    removeNotification,
    markAsRead,
    unreadCount,
  };
});
