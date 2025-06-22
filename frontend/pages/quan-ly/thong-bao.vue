<template>
  <div>
    <Titlebar title="Thông báo" />

    <div class="row">
      <div class="col-lg-12 col-md-12">
        <div class="dashboard-list-box margin-top-0">
          <!-- Tiêu đề khung -->
          <div class="box-title-bar">
            <h3>Thông báo</h3>
          </div>

          <!-- Danh sách thông báo -->
          <div
            v-for="(noti, index) in notifications"
            :key="noti.id"
            class="notification-item"
            :class="{ unread: noti.unread, read: !noti.unread }"
            @click="markAsRead(index)"
          >
            <!-- Thời gian & trạng thái ở góc phải trên -->
            <div class="notification-meta-top">
              <span class="notification-time-top">{{ noti.time }}</span>
              <span
                v-if="noti.unread"
                class="badge-status badge-inline"
              >Chưa đọc</span>
            </div>

            <div class="notification-inner">
              <!-- Ảnh đại diện -->
              <div class="notification-thumb">
                <img :src="noti.avatar || defaultAvatar" alt="avatar" />
              </div>

              <!-- Nội dung -->
              <div class="notification-content">
                <h4 class="notification-title">
                  {{ noti.title }}
                </h4>
                <p class="notification-text">{{ noti.text }}</p>
              </div>

              <!-- Nút xoá -->
              <button class="delete-btn" @click.stop="removeNotification(index)">✕</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>




<script setup>
definePageMeta({ layout: 'management' });

const defaultAvatar = '/images/default-user.png';

const notifications = ref([
  {
    id: 1,
    title: 'Hệ thống',
    text: 'Bạn có 1 hợp đồng mới cần xác nhận.',
    time: '5 phút trước',
    unread: true,
    avatar: ''
  },
  {
    id: 2,
    title: 'Nguyễn Văn A',
    text: 'Yêu cầu xem phòng ngày 28/06 đã được duyệt.',
    time: '1 giờ trước',
    unread: true,
    avatar: ''
  },
  {
    id: 3,
    title: 'Hệ thống',
    text: 'Cập nhật điều khoản sử dụng. Vui lòng xem lại.',
    time: 'Hôm qua',
    unread: false,
    avatar: ''
  },
  {
    id: 4,
    title: 'Admin',
    text: 'Chúng tôi đã nhận được thanh toán của bạn.',
    time: '20/06/2025',
    unread: false,
    avatar: ''
  },
]);

const removeNotification = index => {
  notifications.value.splice(index, 1);
};

const markAsRead = index => {
  if (notifications.value[index].unread) {
    notifications.value[index].unread = false;
  }
};
</script>

<style scoped>
.dashboard-list-box {
  background-color: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  overflow: hidden;
  padding: 0;
}

.box-title-bar {
  padding: 16px 24px 12px;
  border-bottom: 1px solid #eee;
}

.box-title-bar h3 {
  font-size: 18px;
  font-weight: 600;
  margin: 0;
  color: #333;
}

/* Item thông báo */
.notification-item {
  padding: 24px;
  border-bottom: 1px solid #eee;
  transition: background-color 0.3s ease;
  cursor: pointer;
  position: relative;
  
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-item.unread {
  background-color: #fff1f0;
}

.notification-inner {
  display: flex;
  align-items: flex-start;
  position: relative;
}

.notification-meta-top {
  position: absolute;

  top: 12px;
  right: 24px;
  display: flex;
  gap: 20px;
  align-items: center;
}

/* Thời gian */
.notification-time-top {
  font-size: 14px;
  color: #999;
}

/* Trạng thái chưa đọc */
.badge-status.badge-inline {
  background-color: #f44336;
  color: #fff;
  font-size: 12px;
  padding: 2px 6px;
  border-radius: 4px;
}

/* Ảnh đại diện */
.notification-thumb {
  width: 80px;
  height: 60px;
  flex-shrink: 0;
  margin-right: 16px;
}

.notification-thumb img {
  width: 150px;
  height: 112.41;
  border-radius: 8px;
  object-fit: cover;
  border: 1px solid #eee;
}

/* Nội dung */
.notification-content {
  flex: 1;
}

.notification-title {
  font-size: 18px;
  font-weight: 600;
  color: #333;
  margin-bottom: 8px;
  width: auto;
  border: none;
  background-color: transparent;
}

.notification-text {
  font-size: 18px;
  color: #444;
  line-height: 1.5;
}

/* Nút xoá */
.delete-btn {
  position: absolute;
  right: 24px;
  top: 50%;
  transform: translateY(-50%);
  background: transparent;
  border: none;
  font-size: 18px;
  color: #aaa;
  cursor: pointer;
}

.delete-btn:hover {
  color: #f44336;
}
</style>
