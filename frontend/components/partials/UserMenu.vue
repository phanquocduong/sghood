<template>
    <div class="right-side">
        <div class="header-widget">
            <!-- Nếu chưa đăng nhập -->
            <a v-if="!user" href="#sign-in-dialog" class="sign-in popup-with-zoom-anim" style="margin-left: auto">
                <i class="sl sl-icon-login"></i> Đăng ký/Đăng nhập
            </a>
            <!-- Nếu đã đăng nhập -->
            <ClientOnly>
                <div v-show="user" class="auth-container">
                    <div class="user-menu notification-wrapper">
                        <div class="notification-icon" @click="toggleDropdown">
                            <i class="sl sl-icon-bell"></i>
                            <span class="badge" v-if="unreadCount > 0">{{ unreadCount }}</span>
                        </div>

                        <!-- Dropdown -->
                        <ul v-if="showDropdown" class="dropdown">
                            <template v-if="topNoti.length > 0">
                                <li v-for="noti in topNoti" :key="noti.id">
                                    <NuxtLink to="/quan-ly/thong-bao">
                                        <strong>{{ noti.title }}</strong>
                                        <small>{{ formatTimeAgo(noti.time) }}</small>
                                    </NuxtLink>
                                </li>
                            </template>
                            <li v-else>
                                <p style="padding: 10px; text-align: center">Chưa có thông báo nào.</p>
                            </li>

                            <!-- Gạch ngang -->
                            <li class="divider"></li>

                            <!-- Xem tất cả -->
                            <li class="view-all">
                                <NuxtLink to="/quan-ly/thong-bao">
                                    <p>Xem tất cả</p>
                                </NuxtLink>
                            </li>
                        </ul>
                    </div>

                    <!-- 👤 Menu người dùng -->
                    <div class="user-menu">
                        <div class="user-name">
                            <span>
                                <img
                                    :src="user?.avatar ? config.public.baseUrl + user.avatar : '/images/default-avatar.webp'"
                                    alt="Avatar"
                                />
                            </span>
                            Xin chào, {{ user?.name || 'Người dùng' }}!
                        </div>

                        <ul>
                            <li>
                                <NuxtLink to="/quan-ly/thong-bao"> <i class="sl sl-icon-bell"></i> Thông báo </NuxtLink>
                            </li>
                            <li>
                                <NuxtLink to="/quan-ly/ho-so-ca-nhan"> <i class="sl sl-icon-user"></i> Hồ sơ cá nhân </NuxtLink>
                            </li>
                            <li>
                                <NuxtLink to="/quan-ly/hop-dong"> <i class="sl sl-icon-notebook"></i> Hợp đồng </NuxtLink>
                            </li>
                            <li>
                                <a href="#" @click.prevent="authStore.logout"> <i class="sl sl-icon-power"></i> Đăng xuất </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </ClientOnly>
        </div>
    </div>
</template>

<script setup>
import { useAuthStore } from '~/stores/auth';
import { useNotificationStore } from '~/stores/notication';
import { storeToRefs } from 'pinia';
import { ref, onMounted, computed } from 'vue';
import { formatTimeAgo } from '~/utils/time';
const config = useRuntimeConfig();
const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

const notificationStore = useNotificationStore();
const { notifications } = storeToRefs(notificationStore);

// Gọi API lấy thông báo khi mount
onMounted(() => {
    notificationStore.fetchNotifications();
    window.addEventListener('click', handleClickOutside);
});

// Dropdown control
const showDropdown = ref(false);
const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
};

// Đếm số thông báo chưa đọc
const unreadCount = computed(() => notifications.value.filter(n => n.unread).length);

// Lấy top 5 thông báo mới nhất
const topNoti = computed(() => {
    return [...notifications.value]
        .filter(m => m.unread)
        .sort((a, b) => new Date(b.time) - new Date(a.time))
        .slice(0, 5);
});
// Đóng dropdown khi click ra ngoài
const handleClickOutside = event => {
    const target = event.target;
    if (!target.closest('.notification-wrapper')) {
        showDropdown.value = false;
    }
};

onBeforeUnmount(() => {
    window.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
@media screen and (max-width: 480px) {
    .user-name {
        max-width: 200px;
        font-size: 18px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    .user-menu .dropdown {
        right: 0 !important;
        transform: none !important;
        width: 280px;
    }
    .auth-container {
        display: inline-flex;
        padding: 12px 16px;
        align-items: center;
        gap: 12px; /* khoảng cách giữa chuông và user */
        margin-left: auto;
    }
    .notification-icon {
        margin-left: 0px !important;
        top: -1px !important;
    }
}

.auth-container {
    margin-left: auto;
}
.user-name img {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
}
.header-widget {
    display: flex;
}

/* Icon thông báo */
.notification-icon {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    font-size: 18px;
    color: #4b4a4a;
    border-radius: 50%;
    background-color: #f1f1f1;
    transition: all 0.2s;
    cursor: pointer;
    margin-left: 0px;
    top: -3px;
}

.notification-icon:hover {
    color: #f91942;
    background-color: #eaeaea;
}

.notification-icon .badge {
    position: absolute;
    top: -4px;
    right: -4px;
    background-color: #f91942;
    color: white;
    font-size: 10px;
    padding: 1px 4px;
    border-radius: 50%;
    font-weight: bold;
    min-width: 16px;
    height: 16px;
    line-height: 1;
    align-items: center;
    display: flex;
    justify-content: center;
}

/* Dùng class dropdown của template */
.user-menu .dropdown {
    position: absolute;
    right: -130px; /* dịch sang phải icon chuông */
    top: 48px; /* ngay phía dưới icon chuông */
    background: white;
    width: 220px;
    border-radius: 4px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    z-index: 100;
}
.user-menu .dropdown li:last-child {
    border-bottom: none;
}
.notification-wrapper {
    position: relative; /* Bổ sung */
}
.user-menu .dropdown li:hover a {
    color: #f91942; /* Chỉ chữ đổi màu đỏ */
}

/* 👉 Tăng kích thước dropdown box */
.user-menu .dropdown {
    width: 300px; /* rộng hơn */
    padding: 10px 0;
}

/* 👉 Canh thời gian hiển thị ngang hàng với title */
.user-menu .dropdown li a {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    gap: 10px;
    font-size: 14px;
    transition: all 0.2s;
}

/* 👉 Căn chỉnh phần title và time */
.user-menu .dropdown li a strong {
    flex: 1;
    font-weight: 600;
    color: #333;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.user-menu .dropdown li a small {
    white-space: nowrap;
    color: #888;
    font-size: 12px;
}
/* ... giữ nguyên các đoạn khác ... */

/* Hover toàn dòng đỏ chữ */
.user-menu .dropdown li:hover a strong,
.user-menu .dropdown li:hover a small {
    color: #f91942;
}

/* Divider (dấu gạch ngang) */
.user-menu .dropdown .divider {
    border-top: 1px solid #eee;
    margin: 5px 0;
    height: 1px;
}

/* "Xem tất cả" gọn và căn giữa */
.user-menu .dropdown .view-all {
    text-align: center;

    padding: 2px 0;
    height: 30px;
}

.user-menu .dropdown .view-all a p {
    font-size: 13px;
    color: #555;
    transition: color 0.2s;
    margin: auto;
    margin-top: -10px;
}

.user-menu .dropdown .view-all a:hover p {
    color: #f91942;
}
</style>
