<!-- Template hiển thị lớp phủ loading -->
<template>
    <!-- Hiển thị lớp phủ khi isLoading là true -->
    <div v-if="isLoading" class="loading-overlay">
        <div class="spinner"></div>
        <p>{{ message }}</p>
    </div>
</template>

<script setup>
// Định nghĩa props cho component
defineProps({
    message: {
        type: String,
        default: 'Đang tải...' // Thông điệp mặc định khi loading
    },
    isLoading: {
        type: Boolean,
        required: true // Bắt buộc truyền prop isLoading
    }
});
</script>

<!-- CSS tùy chỉnh cho component -->
<style scoped>
/* CSS cho lớp phủ loading */
.loading-overlay {
    position: fixed;
    inset: 0;
    background: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

/* CSS cho spinner loading */
.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #f91942;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* CSS cho văn bản trong lớp phủ loading */
p {
    color: #333;
    margin-top: 10px;
    font-size: 16px;
}

/* Keyframes cho hiệu ứng quay của spinner */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>
