<!-- Template cho menu người dùng -->
<template>
    <!-- Phần bên phải của header -->
    <div class="right-side">
        <div class="header-widget">
            <!-- Nếu chưa đăng nhập, hiển thị nút đăng ký/đăng nhập -->
            <a v-if="!user" href="#sign-in-dialog" class="sign-in popup-with-zoom-anim" style="margin-left: auto">
                <i class="sl sl-icon-login"></i> Đăng ký/Đăng nhập
            </a>
            <!-- Nếu đã đăng nhập, hiển thị menu người dùng -->
            <ClientOnly>
                <div v-show="user" class="auth-container">
                    <!-- Phần thông báo -->
                    <div class="user-menu notification-wrapper">
                        <!-- Biểu tượng chuông thông báo -->
                        <div class="notification-icon" @click="toggleDropdown">
                            <i class="sl sl-icon-bell"></i>
                            <!-- Hiển thị số lượng thông báo chưa đọc -->
                            <span class="badge" v-if="unreadCount > 0">{{ unreadCount }}</span>
                        </div>

                        <!-- Dropdown thông báo -->
                        <ul v-if="showDropdown" class="dropdown">
                            <!-- Hiển thị top 5 thông báo chưa đọc -->
                            <template v-if="topNoti.length > 0">
                                <li v-for="noti in topNoti" :key="noti.id">
                                    <NuxtLink to="/quan-ly/thong-bao">
                                        <strong>{{ noti.title }}</strong>
                                        <small>{{ formatTimeAgo(noti.time) }}</small>
                                    </NuxtLink>
                                </li>
                            </template>
                            <!-- Thông báo khi không có thông báo -->
                            <li v-else>
                                <p style="padding: 10px; text-align: center">Chưa có thông báo nào.</p>
                            </li>

                            <!-- Gạch ngang phân cách -->
                            <li class="divider"></li>

                            <!-- Liên kết xem tất cả thông báo -->
                            <li class="view-all">
                                <NuxtLink to="/quan-ly/thong-bao">
                                    <p>Xem tất cả</p>
                                </NuxtLink>
                            </li>
                        </ul>
                    </div>

                    <!-- Menu người dùng -->
                    <div class="user-menu">
                        <!-- Hiển thị tên và avatar người dùng -->
                        <div class="user-name">
                            <span>
                                <img
                                    :src="user?.avatar ? config.public.baseUrl + user.avatar : '/images/default-avatar.webp'"
                                    alt="Avatar"
                                />
                            </span>
                            Xin chào, {{ user?.name || 'Người dùng' }}!
                        </div>

                        <!-- Danh sách menu người dùng -->
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
import { ref, onMounted, computed, onBeforeUnmount } from 'vue';
import { formatTimeAgo } from '~/utils/time';

// Lấy cấu hình runtime
const config = useRuntimeConfig();
// Lấy store xác thực và thông tin người dùng
const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

// Lấy store thông báo và danh sách thông báo
const notificationStore = useNotificationStore();
const { notifications } = storeToRefs(notificationStore);

// Gọi API lấy thông báo khi component được mount
onMounted(() => {
    notificationStore.fetchNotifications();
    window.addEventListener('click', handleClickOutside); // Thêm sự kiện click ngoài để đóng dropdown
});

// Biến kiểm soát hiển thị dropdown thông báo
const showDropdown = ref(false);
// Hàm bật/tắt dropdown thông báo
const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value;
};

// Tính toán số lượng thông báo chưa đọc
const unreadCount = computed(() => notifications.value.filter(n => n.unread).length);

// Lấy top 5 thông báo chưa đọc mới nhất
const topNoti = computed(() => {
    return [...notifications.value]
        .filter(m => m.unread)
        .sort((a, b) => new Date(b.time) - new Date(a.time)) // Sắp xếp theo thời gian giảm dần
        .slice(0, 5); // Lấy 5 thông báo đầu tiên
});

// Hàm xử lý khi click ngoài dropdown
const handleClickOutside = event => {
    const target = event.target;
    if (!target.closest('.notification-wrapper')) {
        showDropdown.value = false; // Đóng dropdown nếu click ngoài
    }
};

// Xóa sự kiện khi component bị hủy
onBeforeUnmount(() => {
    window.removeEventListener('click', handleClickOutside);
});
</script>

<!-- CSS tùy chỉnh cho component -->
<style scoped>
/* CSS responsive cho thiết bị di động */
@media screen and (max-width: 480px) {
    /* Giới hạn chiều rộng tên người dùng */
    .user-name {
        max-width: 200px;
        font-size: 18px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }

    /* Điều chỉnh dropdown trên di động */
    .user-menu .dropdown {
        right: 0 !important;
        transform: none !important;
        width: 280px;
    }
    /* Container cho phần xác thực */
    .auth-container {
        display: inline-flex;
        padding: 12px 16px;
        align-items: center;
        gap: 12px; /* Khoảng cách giữa chuông và menu người dùng */
        margin-left: auto;
    }
    /* Điều chỉnh vị trí biểu tượng thông báo */
    .notification-icon {
        margin-left: 0px !important;
        top: -1px !important;
    }
}

/* Căn chỉnh auth-container */
.auth-container {
    margin-left: auto;
}

/* CSS cho avatar người dùng */
.user-name img {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
}

/* CSS cho header widget */
.header-widget {
    display: flex;
}

/* CSS cho biểu tượng thông báo */
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

/* Hiệu ứng hover cho biểu tượng thông báo */
.notification-icon:hover {
    color: #f91942;
    background-color: #eaeaea;
}

/* CSS cho badge thông báo */
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

/* CSS cho dropdown thông báo */
.user-menu .dropdown {
    position: absolute;
    right: -130px; /* Dịch sang phải so với biểu tượng chuông */
    top: 48px; /* Ngay dưới biểu tượng chuông */
    background: white;
    width: 300px; /* Kích thước dropdown */
    padding: 10px 0;
    border-radius: 4px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    z-index: 100;
}

/* Xóa border dưới cho mục cuối cùng */
.user-menu .dropdown li:last-child {
    border-bottom: none;
}

/* Hiệu ứng hover cho mục dropdown */
.user-menu .dropdown li:hover a {
    color: #f91942; /* Chỉ chữ đổi màu đỏ */
}

/* CSS cho mục trong dropdown */
.user-menu .dropdown li a {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    gap: 10px;
    font-size: 14px;
    transition: all 0.2s;
}

/* CSS cho tiêu đề thông báo */
.user-menu .dropdown li a strong {
    flex: 1;
    font-weight: 600;
    color: #333;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* CSS cho thời gian thông báo */
.user-menu .dropdown li a small {
    white-space: nowrap;
    color: #888;
    font-size: 12px;
}

/* Hiệu ứng hover cho tiêu đề và thời gian */
.user-menu .dropdown li:hover a strong,
.user-menu .dropdown li:hover a small {
    color: #f91942;
}

/* CSS cho gạch ngang phân cách */
.user-menu .dropdown .divider {
    border-top: 1px solid #eee;
    margin: 5px 0;
    height: 1px;
}

/* CSS cho mục "Xem tất cả" */
.user-menu .dropdown .view-all {
    text-align: center;
    padding: 2px 0;
    height: 30px;
}

/* CSS cho văn bản "Xem tất cả" */
.user-menu .dropdown .view-all a p {
    font-size: 13px;
    color: #555;
    transition: color 0.2s;
    margin: auto;
    margin-top: -10px;
}

/* Hiệu ứng hover cho "Xem tất cả" */
.user-menu .dropdown .view-all a:hover p {
    color: #f91942;
}
</style>
