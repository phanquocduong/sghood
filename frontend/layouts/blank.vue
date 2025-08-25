<!-- Template cho layout trống, chỉ hiển thị nội dung trang -->
<template>
    <!-- Hiển thị component Loading khi đang tải -->
    <Loading :is-loading="isLoading" />
    <!-- Hiển thị nội dung trang khi không còn trạng thái loading -->
    <NuxtPage />
</template>

<script setup>
import { useRoute } from 'vue-router';
import { ref, watch } from 'vue';

// Lấy thông tin route hiện tại
const route = useRoute();
// Khởi tạo biến trạng thái loading
const isLoading = ref(false);

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
</script>

<!-- CSS toàn cục cho layout -->
<style>
/* Đặt màu nền mặc định cho body */
body {
    background: #f4f4f4;
}
</style>
