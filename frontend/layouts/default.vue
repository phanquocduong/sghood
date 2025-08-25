<!-- Template cho layout mặc định của ứng dụng -->
<template>
    <!-- Hiển thị component Loading khi đang tải -->
    <Loading :is-loading="isLoading" />
    <!-- Hiển thị nội dung chính khi không còn trạng thái loading -->
    <div v-if="!isLoading" id="wrapper">
        <!-- Phần header của trang -->
        <header id="header-container">
            <div id="header">
                <div class="container">
                    <div class="left-side">
                        <!-- Logo dẫn về trang chủ -->
                        <div id="logo">
                            <NuxtLink to="/">
                                <!-- Hiển thị logo chính nếu có trong cấu hình -->
                                <img v-if="config?.main_logo" :src="baseUrl + config.main_logo" />
                            </NuxtLink>
                        </div>

                        <!-- Component điều hướng cho thiết bị di động -->
                        <MobileNavigation />

                        <!-- Component điều hướng chính cho desktop -->
                        <MainNavigation />
                    </div>

                    <!-- Menu người dùng (đăng nhập/đăng ký) -->
                    <UserMenu />

                    <!-- Dialog đăng nhập/đăng ký -->
                    <div id="sign-in-dialog" class="zoom-anim-dialog mfp-hide">
                        <div class="small-dialog-header">
                            <h3>Đăng ký/Đăng nhập</h3>
                        </div>

                        <div class="sign-in-form style-1">
                            <!-- Tabs chuyển đổi giữa đăng nhập và đăng ký -->
                            <ul class="tabs-nav">
                                <li><a href="#login">Đăng nhập</a></li>
                                <li><a href="#register">Đăng ký</a></li>
                            </ul>

                            <!-- Nội dung của các tab -->
                            <div class="tabs-container alt">
                                <!-- Form đăng nhập -->
                                <LoginForm />
                                <!-- Form đăng ký -->
                                <RegisterForm />
                                <!-- Form quên mật khẩu -->
                                <ForgotPasswordForm />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <div class="clearfix"></div>

        <!-- Nội dung chính của trang -->
        <NuxtPage />

        <!-- Footer của ứng dụng -->
        <AppFooter />

        <!-- Nút quay lại đầu trang -->
        <div id="backtotop"><a href="#"></a></div>

        <!-- Phần chatbox -->
        <div>
            <!-- Hiển thị biểu tượng chat nếu người dùng đã đăng nhập và chat chưa mở hoặc không phải thiết bị di động -->
            <ChatIcon v-if="user && (!isChatOpen || !isMobile)" :unreadMessages="unreadMessages" @toggle="toggleChat" class="chat-icon" />
            <div>
                <!-- Hiển thị hộp chat nếu người dùng đã đăng nhập và chat đang mở -->
                <ChatBox
                    v-if="user && isChatOpen"
                    :isOpen="isChatOpen"
                    @close="isChatOpen = false"
                    @unread="onUnreadMessage"
                    class="chat-box"
                ></ChatBox>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useRoute } from 'vue-router';
import { ref, watch, nextTick, onMounted } from 'vue';
import { useAuthStore } from '~/stores/auth';
import ChatIcon from '~/components/partials/ChatIcon.vue';
import ChatBox from '~/components/partials/ChatBox.vue';

// Lấy thông tin route và khởi tạo các biến trạng thái
const route = useRoute();
const isLoading = ref(false);
const config = useState('configs'); // Lấy cấu hình từ state toàn cục
const baseUrl = useRuntimeConfig().public.baseUrl; // Lấy base URL từ runtime config
const isChatOpen = ref(false); // Trạng thái mở/đóng chatbox
const unreadMessages = ref(0); // Số lượng tin nhắn chưa đọc
const authStore = useAuthStore(); // Lấy store xác thực
const isMobile = ref(false); // Trạng thái thiết bị di động
const { user } = storeToRefs(authStore); // Lấy thông tin người dùng từ store

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

// Hàm kiểm tra thiết bị di động
onMounted(() => {
    const CheckMobile = () => {
        isMobile.value = window.innerWidth <= 480; // Kiểm tra kích thước màn hình
    };
    CheckMobile();
    window.addEventListener('resize', CheckMobile); // Theo dõi sự thay đổi kích thước màn hình
});
</script>

<!-- CSS tùy chỉnh cho component -->
<style scoped>
/* CSS cho chatbox trên thiết bị di động */
@media only screen and (max-width: 480px) {
    .chat-box {
        width: 100% !important;
        right: 0;
        bottom: 0;
        height: 80% !important;
        max-height: none;
        position: fixed;
        z-index: 1000;
    }
}
</style>
