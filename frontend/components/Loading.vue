<template>
    <div v-if="loading" class="loading-overlay">
        <div class="spinner"></div>
        <p>Đang tải...</p>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useNuxtApp } from '#app';

const { $router } = useNuxtApp();
const loading = ref(false);

// Hàm vô hiệu hóa tương tác với body
const disableInteraction = () => {
    document.body.style.pointerEvents = 'none';
};

// Hàm khôi phục tương tác với body
const enableInteraction = () => {
    document.body.style.pointerEvents = 'auto';
};

// Hiển thị loading khi trang bắt đầu tải (bao gồm làm mới trang)
onMounted(() => {
    loading.value = true;
    disableInteraction();

    // Đợi DOM và scripts tải xong
    setTimeout(() => {
        loading.value = false;
        enableInteraction();
    }, 500); // Độ trễ nhỏ để đảm bảo DOM render xong
});

// Bắt đầu loading khi bắt đầu chuyển trang
$router.beforeEach(() => {
    loading.value = true;
    disableInteraction();
});

// Kết thúc loading khi DOM và scripts đã tải xong
$router.afterEach(() => {
    // Đợi DOM và scripts tải xong
    setTimeout(() => {
        loading.value = false;
        enableInteraction();
    }, 200); // Độ trễ nhỏ để đảm bảo DOM render xong
});

// Xử lý khi component được unmount
onUnmounted(() => {
    enableInteraction(); // Đảm bảo khôi phục khi component bị hủy
});
</script>

<style scoped>
.loading-overlay {
      top: 60px; /* giữ lại header, tuỳ theo chiều cao header của bạn */
    height: calc(100% - 60px); /* điều chỉnh tương ứng */
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: white; /* Nền tối hơn để che hoàn toàn */
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
   
}

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #f91942;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

p {
    color: #333;
    margin-top: 10px;
    font-size: 16px;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.loading-overlay {
    transition: opacity 0.3s ease;
    opacity: 1;
}
.loading-overlay[style*="display: none"] {
    opacity: 0;
    pointer-events: none;
}

</style>
