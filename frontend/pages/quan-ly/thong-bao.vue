<template>
  <div>
    <Titlebar title="Thông báo" />

    <div class="row">
      <div class="col-lg-12 col-md-12">
        <div class="dashboard-list-box margin-top-0">
          <!-- Tiêu đề khung -->
          <div class="box-title-bar">
            <h4>Thông báo</h4>
          </div>

        
         <!-- Danh sách thông báo -->
<div 
  v-for="(noti, index) in notifications"
  :key="noti.id"
  class="message-item"
  :class="{ unread: noti.unread, read: !noti.unread }"
  @click="markAsRead(index)"
>
  <a href="#" class="message-content">
    <div class="message-avatar">
      <img src="/images/sghood_logo1.png" alt="avatar" />
    </div>

    <div class="message-by">
      <div class="message-header">
        <h5>{{ noti.title }} <i v-if="noti.unread">Chưa đọc</i></h5>
        <span class="message-time">{{ formatTimeAgo(noti.time) }}</span>
      </div>
      <p>{{ noti.content }}</p>
    </div>
  </a>
  <button class="delete-btn" @click.stop="removeNotification(index)">✕</button>
</div>



<!-- Nếu không có thông báo -->
<div v-if="notifications.length === 0" class="box-title-bar">
  <p>Chưa có thông báo nào.</p>
</div>

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
const {notifications} = storeToRefs(useNotificationStore());
const authStore = useAuthStore();
const { user } = storeToRefs(authStore);
onMounted(()=>{
  NotiStore.fetchNotifications()
})
const markAsRead = (index) => {
  NotiStore.markAsRead(index);
};

const removeNotification = (index) => {
  NotiStore.removeNotification(index);
};

</script>

<style scoped>
.message-item {
  display: flex;
  align-items: center;
  border-bottom: 1px solid #eee;
  padding:30px;
  position: relative;
  background: #fff;
}

.message-item.unread {
  background-color: #fff1f0;
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
  box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.message-by {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.message-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-top: 6px;
}

.message-header h5 {
  font-size: 16px;
  font-weight: 600;
  color: #333;
  margin: 0;
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

</style>
