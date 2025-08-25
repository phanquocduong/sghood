<template>
    <!-- Tiêu đề của trang thông báo -->
    <Titlebar title="Thông báo" />

    <!-- Hiển thị loading spinner toàn màn hình khi đang tải dữ liệu -->
    <Loading :is-loading="loading" />

    <!-- Hiển thị thông báo đang tải khi loading = true -->
    <div v-if="loading" class="text-center p-5">
        <p>Đang tải thông báo...</p>
    </div>

    <!-- Hiển thị danh sách thông báo khi đã tải xong -->
    <div v-else class="row">
        <div class="col-lg-12 col-md-12">
            <div class="dashboard-list-box margin-top-0">
                <!-- Thanh tiêu đề với nút đánh dấu đã đọc tất cả -->
                <div
                    class="box-title-bar"
                    style="display: flex; justify-content: space-between; align-items: center; background-color: white"
                >
                    <h4>Thông báo</h4>
                    <!-- Nút để đánh dấu tất cả thông báo là đã đọc -->
                    <button class="read-all-btn" @click="onReadAll">Đánh dấu là đã đọc tất cả</button>
                </div>

                <!-- Hiển thị thông báo khi không có thông báo nào -->
                <div v-if="safeNotifications.length === 0" class="box-title-bar-tb">
                    <p>Chưa có thông báo nào.</p>
                </div>

                <!-- Danh sách thông báo -->
                <NuxtLink
                    v-for="(noti, index) in safeNotifications"
                    :key="noti.id"
                    class="message-item"
                    :class="{ unread: noti.unread, read: !noti.unread }"
                    @click="onMarkAsRead(noti.id)"
                >
                    <a href="#" class="message-content">
                        <!-- Avatar của thông báo -->
                        <div class="message-avatar">
                            <img src="/images/sghood_logo1.png" alt="avatar" />
                        </div>

                        <!-- Nội dung thông báo -->
                        <div class="message-by">
                            <div class="message-header">
                                <!-- Phần tiêu đề thông báo -->
                                <div class="left-side">
                                    <h5 class="message-title">{{ noti.title }}</h5>
                                </div>
                                <!-- Phần trạng thái và thời gian -->
                                <div class="right-side">
                                    <span v-if="noti.unread" class="message-status">Chưa đọc</span>
                                    <span class="message-time">{{ formatTimeAgo(noti.time) }}</span>
                                </div>
                            </div>

                            <!-- Nội dung chi tiết của thông báo -->
                            <p>{{ noti.content }}</p>
                        </div>
                    </a>
                </NuxtLink>
            </div>
        </div>
    </div>

    <!-- Phân trang khi có nhiều hơn 1 trang -->
    <div class="pagination-container margin-bottom-40" v-if="totalPages > 1">
        <nav class="pagination">
            <ul>
                <!-- Nút quay lại trang trước -->
                <li v-if="currentPage > 1">
                    <a href="#" @click.prevent="goToPage(currentPage - 1)">
                        <i class="sl sl-icon-arrow-left"></i>
                    </a>
                </li>
                <!-- Danh sách các trang -->
                <li v-for="page in totalPages" :key="page">
                    <a href="#" :class="{ 'current-page': page === currentPage }" @click.prevent="goToPage(page)">
                        {{ page }}
                    </a>
                </li>
                <!-- Nút chuyển sang trang tiếp theo -->
                <li v-if="currentPage < totalPages">
                    <a href="#" @click.prevent="goToPage(currentPage + 1)">
                        <i class="sl sl-icon-arrow-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script setup>
// Cấu hình metadata cho trang, sử dụng layout 'management'
definePageMeta({ layout: 'management' });

// Import các composable và utility cần thiết
import { useAppToast } from '~/composables/useToast';
import { useAuthStore } from '~/stores/auth';
import { formatTimeAgo } from '~/utils/time';
import { useNotificationStore } from '~/stores/notication';

// Khởi tạo store và các biến reactive
const NotiStore = useNotificationStore(); // Store quản lý thông báo
const noti = useAppToast(); // Hàm hiển thị thông báo
const { notifications, loading, currentPage, totalPages, onMarkAllAsRead } = storeToRefs(useNotificationStore()); // Lấy các giá trị từ store
const authStore = useAuthStore(); // Store quản lý thông tin người dùng
const { user } = storeToRefs(authStore); // Lấy thông tin người dùng
const safeNotifications = computed(() => notifications.value || []); // Computed property để đảm bảo notifications luôn có giá trị

// Hàm chuyển đến trang cụ thể
const goToPage = page => {
    if (page !== currentPage.value) {
        // Kiểm tra nếu trang khác với trang hiện tại
        NotiStore.fetchNotifications(page); // Gọi hàm lấy thông báo với trang mới
    }
};

// Hàm đánh dấu tất cả thông báo là đã đọc
const onReadAll = async () => {
    NotiStore.onMarkAllAsRead(); // Gọi hàm từ store để đánh dấu tất cả
};

// Gọi hàm lấy thông báo khi component được mounted
onMounted(() => {
    NotiStore.fetchNotifications(); // Lấy danh sách thông báo ban đầu
});

// Hàm đánh dấu một thông báo là đã đọc
const onMarkAsRead = async id => {
    NotiStore.markAsRead(id); // Gọi hàm từ store để đánh dấu thông báo cụ thể
};
</script>

<style scoped>
/* Style cho mỗi mục thông báo */
.message-item {
    display: flex;
    align-items: center;
    border-bottom: 1px solid #eee; /* Đường viền dưới mỗi thông báo */
    padding: 30px;
    position: relative;
    background: #fff; /* Màu nền mặc định */
}

/* Style cho thông báo chưa đọc */
.message-item.unread {
    background-color: #fff1f0; /* Màu nền nổi bật cho thông báo chưa đọc */
    transition: background-color 0.3s ease; /* Hiệu ứng chuyển màu mượt mà */
}

/* Style cho nội dung thông báo */
.message-content {
    display: flex;
    align-items: center;
    flex: 1;
    text-decoration: none; /* Bỏ gạch chân liên kết */
    color: inherit; /* Kế thừa màu chữ */
}

/* Style cho avatar thông báo */
.message-avatar {
    flex-shrink: 0; /* Không co lại */
    margin-right: 15px; /* Khoảng cách bên phải */
}

/* Style cho hình ảnh avatar */
.message-avatar img {
    width: 60px;
    height: 60px;
    border-radius: 50%; /* Bo tròn */
    object-fit: cover; /* Căn chỉnh hình ảnh */
    border: 1px solid #ddd; /* Viền nhẹ */
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1); /* Hiệu ứng bóng */
}

/* Style cho phần nội dung chính của thông báo */
.message-by {
    flex: 1;
    display: flex;
    flex-direction: column;
}

/* Style cho header của thông báo */
.message-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: nowrap;
    gap: 10px;
}

/* Style cho phần tiêu đề bên trái */
.left-side {
    flex: 1;
    min-width: 0;
    overflow: hidden;
}

/* Style cho tiêu đề thông báo */
.message-title {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin: 0;
    line-height: 1.4;
    max-height: 2.8em; /* Giới hạn 2 dòng */
    display: -webkit-box;
    -webkit-line-clamp: 2; /* Hiển thị tối đa 2 dòng */
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis; /* Thêm dấu ... khi vượt quá */
    word-break: break-word; /* Ngắt từ khi cần */
}

/* Style cho phần trạng thái và thời gian bên phải */
.right-side {
    display: contents;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
    min-width: 80px;
    flex-shrink: 0;
    max-width: 100px; /* Giới hạn chiều rộng */
}

/* Style cho trạng thái "Chưa đọc" */
.message-status {
    background-color: #4caf50; /* Màu nền xanh */
    color: white;
    font-size: 12px;
    padding: 2px 8px;
    border-radius: 12px; /* Bo tròn */
    display: inline-block;
    white-space: nowrap; /* Không xuống dòng */
}

/* Style cho thời gian thông báo */
.message-time {
    font-size: 12px;
    color: #999;
    white-space: nowrap;
}

/* Style cho icon trong header */
.message-header i {
    font-size: 12px;
    font-style: normal;
    color: white;
    margin-left: 8px;
}

/* Style bổ sung cho thời gian */
.message-time {
    font-size: 12px;
    color: #999;
    margin-left: 20px;
    white-space: nowrap;
    margin-top: -10px;
}

/* Style cho nội dung chi tiết thông báo */
.message-by p {
    font-size: 14px;
    color: #666;
    margin: 0;
}

/* Style cho nút xoá thông báo (hiện đang bị comment) */
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

/* Hiệu ứng hover cho nút xoá */
.delete-btn:hover {
    color: #f44336; /* Màu đỏ khi hover */
}

/* Style cho thông báo không có dữ liệu */
.box-title-bar-tb {
    font-size: larger;
    padding: 5px 20px 5px 20px;
    align-items: center;
    border: none;
    text-align: center;
    height: 46px;
}

/* Style cho nút "Đánh dấu là đã đọc tất cả" */
.read-all-btn {
    font-size: 13px;
    color: #999;
    background: none;
    border: none;
    cursor: pointer;
    margin-right: 15px;
    line-height: 40px;
    font-weight: 600;
}

/* Hiệu ứng hover cho nút "Đánh dấu là đã đọc tất cả" */
.read-all-btn:hover {
    color: #333;
}
</style>
