<!-- Template cho trang xác minh email -->
<template>
    <div class="page-container">
        <div class="container">
            <!-- Tiêu đề trang -->
            <h2 class="header-title">Xác Minh Email</h2>

            <!-- Trạng thái đang tải -->
            <div v-if="loading" class="loading-container">
                <div class="spinner"></div>
                <p class="loading-text">Đang xác minh...</p>
            </div>

            <!-- Trạng thái thông báo (thành công hoặc lỗi) -->
            <div v-else-if="message" class="notification" :class="{ success: !error, error: error }">
                <div class="notification-content">
                    <!-- Biểu tượng thông báo (thành công hoặc lỗi) -->
                    <span class="notification-icon">{{ error ? '❌' : '✅' }}</span>
                    <!-- Nội dung thông báo -->
                    <p class="notification-text">{{ message }}</p>
                </div>
                <!-- Bộ đếm ngược trước khi chuyển hướng -->
                <p v-if="!loading && !redirecting" class="countdown-text">Sẽ chuyển hướng về trang chủ sau {{ countdown }} giây...</p>
            </div>

            <!-- Trạng thái liên kết không hợp lệ -->
            <div v-else class="notification error">
                <div class="notification-content">
                    <!-- Biểu tượng lỗi -->
                    <span class="notification-icon">❌</span>
                    <!-- Thông báo lỗi mặc định -->
                    <p class="notification-text">Liên kết xác minh không hợp lệ.</p>
                </div>
                <!-- Bộ đếm ngược trước khi chuyển hướng -->
                <p v-if="!loading && !redirecting" class="countdown-text">Sẽ chuyển hướng về trang chủ sau {{ countdown }} giây...</p>
            </div>

            <!-- Nút quay lại trang chủ -->
            <NuxtLink v-if="!redirecting" to="/" class="back-button">Quay lại trang chủ</NuxtLink>
        </div>
    </div>
</template>

<script setup>
// Sử dụng layout trống (không có header/footer)
definePageMeta({
    layout: 'blank'
});

import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAppToast } from '~/composables/useToast';

// Khởi tạo router và route
const router = useRouter(); // Đối tượng router để điều hướng
const route = useRoute(); // Lấy thông tin route hiện tại
const toast = useAppToast(); // Composable hiển thị thông báo
const loading = ref(true); // Biến trạng thái đang tải
const message = ref(''); // Biến lưu trữ thông báo
const error = ref(false); // Biến trạng thái lỗi
const redirecting = ref(false); // Biến kiểm soát trạng thái chuyển hướng
const countdown = ref(5); // Thời gian đếm ngược ban đầu (5 giây)

let countdownInterval = null; // Biến lưu trữ interval đếm ngược

// Xử lý khi component được mount
onMounted(() => {
    const { message: msg, error: err } = route.query; // Lấy tham số query từ URL

    // Xử lý thông báo từ query
    if (msg) {
        message.value = decodeURIComponent(msg); // Giải mã thông báo
        error.value = false; // Không có lỗi
    } else if (err) {
        message.value = decodeURIComponent(err); // Giải mã lỗi
        error.value = true; // Có lỗi
        toast.error(message.value); // Hiển thị thông báo lỗi
    } else {
        message.value = 'Liên kết xác minh không hợp lệ'; // Thông báo mặc định
        error.value = true; // Có lỗi
        toast.error(message.value); // Hiển thị thông báo lỗi
    }

    loading.value = false; // Tắt trạng thái đang tải

    // Bắt đầu đếm ngược để chuyển hướng
    countdownInterval = setInterval(() => {
        countdown.value -= 1; // Giảm thời gian đếm ngược
        if (countdown.value <= 0) {
            clearInterval(countdownInterval); // Dừng interval
            redirecting.value = true; // Bật trạng thái chuyển hướng
            window.location.assign('/'); // Chuyển hướng về trang chủ
        }
    }, 1000); // Cập nhật mỗi giây
});

// Dọn dẹp khi component bị hủy
onBeforeUnmount(() => {
    if (countdownInterval) {
        clearInterval(countdownInterval); // Dừng interval đếm ngược
    }
});
</script>

<!-- CSS tùy chỉnh cho trang -->
<style scoped>
@import '~/public/css/email-verification.css';
</style>
