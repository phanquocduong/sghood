<!-- Template cho layout khu vực quản lý -->
<template>
    <!-- Hiển thị component Loading khi đang tải -->
    <Loading :is-loading="isLoading" />
    <!-- Hiển thị nội dung chính khi không còn trạng thái loading -->
    <div v-if="!isLoading" id="wrapper">
        <!-- Phần header của khu vực quản lý -->
        <header id="header-container" class="fixed fullwidth dashboard">
            <div id="header" class="not-sticky">
                <div class="container">
                    <div class="left-side">
                        <!-- Logo dẫn về trang chủ -->
                        <div id="logo">
                            <NuxtLink to="/">
                                <!-- Hiển thị logo phụ nếu có trong cấu hình -->
                                <img v-if="config && config.secondary_logo" :src="baseUrl + config.secondary_logo" alt="SGHood Logo" />
                            </NuxtLink>
                            <!-- Logo cho dashboard -->
                            <NuxtLink to="/" class="dashboard-logo">
                                <img v-if="config && config.secondary_logo" :src="baseUrl + config.secondary_logo" alt="SGHood Logo" />
                            </NuxtLink>
                        </div>

                        <!-- Component điều hướng cho thiết bị di động -->
                        <MobileNavigation />

                        <!-- Component điều hướng chính cho desktop -->
                        <MainNavigation />
                    </div>

                    <!-- Menu người dùng (đăng nhập/đăng ký) -->
                    <UserMenu />
                </div>
            </div>
        </header>

        <!-- Phần dashboard quản lý -->
        <div id="dashboard">
            <!-- Nút điều hướng responsive cho dashboard -->
            <a href="#" class="dashboard-responsive-nav-trigger"><i class="fa fa-reorder"></i>Thanh điều hướng</a>
            <!-- Component điều hướng cho dashboard -->
            <DashboardNavigation />
            <!-- Component Loading (không rõ mục đích, có thể bị dư thừa) -->
            <Loading />
            <!-- Nội dung chính của dashboard -->
            <div class="dashboard-content">
                <NuxtPage />
                <!-- Phần bản quyền -->
                <div class="col-md-12">
                    <div class="copyrights">{{ config.copyright_title }}</div>
                </div>
            </div>
            <!-- Phần chatbox -->
            <div>
                <!-- Hiển thị biểu tượng chat nếu người dùng đã đăng nhập -->
                <ChatIcon v-if="user" :unreadMessages="unreadMessages" @toggle="toggleChat" />
                <div>
                    <!-- Hiển thị hộp chat nếu người dùng đã đăng nhập -->
                    <ChatBox v-if="user" :isOpen="isChatOpen" @close="isChatOpen = false" @unread="onUnreadMessage"></ChatBox>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useRouter, useRoute, useHead } from '#app';
import { ref, watch, nextTick, onMounted } from 'vue';
import { useAppToast } from '~/composables/useToast';
import { useAuthStore } from '~/stores/auth';

// Lấy thông tin route, router, và các công cụ khác
const route = useRoute();
const isLoading = ref(false); // Trạng thái loading
const config = useState('configs'); // Lấy cấu hình từ state toàn cục
const baseUrl = useRuntimeConfig().public.baseUrl; // Lấy base URL từ runtime config
const isChatOpen = ref(false); // Trạng thái mở/đóng chatbox
const unreadMessages = ref(0); // Số lượng tin nhắn chưa đọc
const authStore = useAuthStore(); // Lấy store xác thực
const { user } = storeToRefs(authStore); // Lấy thông tin người dùng từ store
const toast = useAppToast(); // Lấy composable hiển thị thông báo
const router = useRouter(); // Lấy router để điều hướng

// Cấu hình SEO cho khu vực quản lý
useHead({
    title: 'SGHood - Khu Vực Quản Lý', // Tiêu đề trang
    meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { name: 'robots', content: 'noindex' } // Ngăn công cụ tìm kiếm lập chỉ mục
    ]
});

// Hàm bật/tắt chatbox
const toggleChat = () => {
    isChatOpen.value = !isChatOpen.value;
    if (isChatOpen.value) {
        unreadMessages.value = 0; // Xóa số tin nhắn chưa đọc khi mở chat
    }
};

// Hàm xử lý tin nhắn chưa đọc
const onUnreadMessage = () => {
    if (!isChatOpen.value) {
        unreadMessages.value++; // Tăng số tin nhắn chưa đọc nếu chatbox đang đóng
    }
};

// Theo dõi thay đổi đường dẫn để kích hoạt loading
watch(
    () => route.fullPath,
    async () => {
        isLoading.value = true; // Bật trạng thái loading
        await nextTick(); // Đợi DOM cập nhật
        // Tắt trạng thái loading sau 500ms
        setTimeout(() => {
            isLoading.value = false;
        }, 500);
    },
    { immediate: true } // Kích hoạt ngay khi component được mount
);

// Hàm kiểm tra xác thực người dùng
const checkAuth = async () => {
    if (!authStore.user) {
        await authStore.fetchUser(); // Lấy thông tin người dùng từ API
    }

    if (!authStore.user) {
        toast.error('Vui lòng đăng nhập!'); // Hiển thị thông báo lỗi nếu chưa đăng nhập
        router.push('/'); // Chuyển hướng về trang chủ
    }
};

// Kiểm tra xác thực khi component được mount
onMounted(() => {
    checkAuth();
});
</script>

<!-- CSS tùy chỉnh cho component -->
<style scoped>
/* CSS cho lớp phủ loading */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    transition: opacity 0.3s ease;
}

/* Ẩn lớp phủ khi không hiển thị */
.loading-overlay[style*='display: none'] {
    opacity: 0;
    pointer-events: none;
}

/* CSS cho spinner loading */
.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #ddd;
    border-top: 5px solid #f91942;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* Keyframes cho hiệu ứng quay của spinner */
@keyframes spin {
    0% {
        transform: rotate(0);
    }
    100% {
        transform: rotate(360deg);
    }
}

/* CSS đảm bảo container chiếm toàn bộ chiều rộng */
.container {
    max-width: 100% !important;
}

/* CSS cho phần bản quyền */
.copyrights {
    font-size: 16px !important;
}
</style>
