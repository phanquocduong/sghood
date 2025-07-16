<template>
    <div>
        <Titlebar title="Thông báo" />

        <!-- Loading full screen -->
        <Loading :is-loading="loading" />

        <!-- Nếu đang loading -->
        <div v-if="loading" class="text-center p-5">
            <p>Đang tải thông báo...</p>
        </div>

        <!-- Nếu đã load xong -->
        <div v-else class="row">
            <div class="col-lg-12 col-md-12">
                <div class="dashboard-list-box margin-top-0">
                    <div class="box-title-bar">
                        <h4>Thông báo</h4>
                    </div>

                    <!-- Không có thông báo -->
                    <div v-if="safeNotifications.length === 0" class="box-title-bar-tb">
                        <p>Chưa có thông báo nào.</p>
                    </div>

                    <!-- Có thông báo -->
                    <NuxtLink
                        v-for="(noti, index) in safeNotifications"
                        :key="noti.id"
                        class="message-item"
                        :class="{ unread: noti.unread, read: !noti.unread }"
                        @click="onMarkAsRead(noti.id)"
                    >
                        <a href="#" class="message-content">
                            <div class="message-avatar">
                                <img src="/images/sghood_logo1.png" alt="avatar" />
                            </div>

                            <div class="message-by">
                  <div class="message-header">
                        <div class="left-side">
                            <h5 class="message-title">{{ noti.title }}</h5>
                        </div>
                        <div class="right-side">
                            <span v-if="noti.unread" class="message-status">Chưa đọc</span>
                            <span class="message-time">{{ formatTimeAgo(noti.time) }}</span>
                        </div>
                        </div>

                                <p>{{ noti.content }}</p>
                            </div>
                        </a>
                        <button class="delete-btn" @click.stop="removeNotification(index)">✕</button>
                    </NuxtLink>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
definePageMeta({ layout: 'management' });
import { useToast } from 'vue-toastification';
import { useAuthStore } from '~/stores/auth';
import { formatTimeAgo } from '~/utils/time';
import { useNotificationStore } from '~/stores/notication';
const NotiStore = useNotificationStore();
const noti = useToast();
const { notifications, loading } = storeToRefs(useNotificationStore());
const authStore = useAuthStore();
const { user } = storeToRefs(authStore);
const safeNotifications = computed(() => notifications.value || []);
onMounted(() => {
    NotiStore.fetchNotifications();
});
const onMarkAsRead = async id => {
    NotiStore.markAsRead(id);
};

const removeNotification = index => {
    NotiStore.removeNotification(index);
};
</script>

<style scoped>
.message-item {
    display: flex;
    align-items: center;
    border-bottom: 1px solid #eee;
    padding: 30px;
    position: relative;
    background: #fff;
}

.message-item.unread {
    background-color: #fff1f0;
    transition: background-color 0.3s ease;
}

.message-content {
    display: flex;
    align-items: center;
    flex: 1;
    text-decoration: none;
    color: inherit;
}

.message-avatar {
    flex-shrink: 0;
    margin-right: 15px;
}

.message-avatar img {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid #ddd;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.message-by {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.message-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: nowrap;
  gap: 10px;
}
.left-side {
  flex: 1;
  min-width: 0;
  overflow: hidden;
}

.message-title {
  font-size: 16px;
  font-weight: 600;
  color: #333;
  margin: 0;
  line-height: 1.4;
  max-height: 2.8em; /* 2 dòng */
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  word-break: break-word;
}





.right-side {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
    min-width: 80px;
    flex-shrink: 0;
    max-width: 100px; /* hoặc thử 80-120px */
}


.message-status {
    background-color: #4caf50;
    color: white;
    font-size: 12px;
    padding: 2px 8px;
    border-radius: 12px;
    display: inline-block;
    white-space: nowrap;
}

.message-time {
    font-size: 12px;
    color: #999;
    white-space: nowrap;
}

.message-header i {
    font-size: 12px;
    font-style: normal;
    color: white;
    margin-left: 8px;
}

.message-time {
    font-size: 12px;
    color: #999;
    margin-left: 20px;
    white-space: nowrap;
    margin-top: -10px;
}

.message-by p {
    font-size: 14px;
    color: #666;
    margin: 0;
}

/* Nút xoá nằm ở giữa bên phải */
.delete-btn {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    font-size: 18px;
    color: #aaa;
    cursor: pointer;
    margin-top: 10px;
}

.delete-btn:hover {
    color: #f44336;
}
.box-title-bar-tb {
    font-size: larger;
    padding: 10px;
    align-items: center;
    border: none;
    text-align: center;
    height: 46px;
}

</style>
