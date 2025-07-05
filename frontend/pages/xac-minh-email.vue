<template>
    <div class="page-container">
        <div class="container">
            <!-- Header -->
            <h2 class="header-title">Xác Minh Email</h2>

            <!-- Loading State -->
            <div v-if="loading" class="loading-container">
                <div class="spinner"></div>
                <p class="loading-text">Đang xác minh...</p>
            </div>

            <!-- Message State -->
            <div v-else-if="message" class="notification" :class="{ success: !error, error: error }">
                <div class="notification-content">
                    <span class="notification-icon">{{ error ? '❌' : '✅' }}</span>
                    <p class="notification-text">{{ message }}</p>
                </div>
                <!-- Countdown Timer -->
                <p v-if="!loading && !redirecting" class="countdown-text">Sẽ chuyển hướng về trang chủ sau {{ countdown }} giây...</p>
            </div>

            <!-- Invalid Link State -->
            <div v-else class="notification error">
                <div class="notification-content">
                    <span class="notification-icon">❌</span>
                    <p class="notification-text">Liên kết xác minh không hợp lệ.</p>
                </div>
                <!-- Countdown Timer -->
                <p v-if="!loading && !redirecting" class="countdown-text">Sẽ chuyển hướng về trang chủ sau {{ countdown }} giây...</p>
            </div>

            <!-- Back Button -->
            <NuxtLink v-if="!redirecting" to="/" class="back-button">Quay lại trang chủ</NuxtLink>
        </div>
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'blank'
});

import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useToast } from 'vue-toastification';

const router = useRouter();
const route = useRoute();
const toast = useToast();
const loading = ref(true);
const message = ref('');
const error = ref(false);
const redirecting = ref(false);
const countdown = ref(5); // Thời gian đếm ngược ban đầu là 5 giây

let countdownInterval = null;

onMounted(() => {
    const { message: msg, error: err } = route.query;

    if (msg) {
        message.value = decodeURIComponent(msg);
        error.value = false;
    } else if (err) {
        message.value = decodeURIComponent(err);
        error.value = true;
        toast.error(message.value);
    } else {
        message.value = 'Liên kết xác minh không hợp lệ';
        error.value = true;
        toast.error(message.value);
    }

    loading.value = false;

    // Bắt đầu đếm ngược
    countdownInterval = setInterval(() => {
        countdown.value -= 1;
        if (countdown.value <= 0) {
            clearInterval(countdownInterval);
            redirecting.value = true;
            window.location.assign('/');
        }
    }, 1000);
});

onBeforeUnmount(() => {
    // Dọn dẹp interval khi component bị hủy
    if (countdownInterval) {
        clearInterval(countdownInterval);
    }
});
</script>

<style scoped>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

.page-container {
    min-height: 100vh;
    background: linear-gradient(135deg, #fff1f1, #ffe1e3);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.container {
    max-width: 500px;
    width: 100%;
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    padding: 40px 30px;
    text-align: center;
    animation: fadeIn 0.5s ease-out;
}

.header-title {
    font-size: 2rem;
    font-weight: 700;
    color: #2d2d2d;
    margin-bottom: 20px;
}

.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
    animation: pulse 1.5s infinite;
}

.spinner {
    width: 60px;
    height: 60px;
    border: 4px solid #f91942;
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.loading-text {
    font-size: 1.2rem;
    color: #4a4a4a;
}

.notification {
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    border-left: 4px solid;
    transition: all 0.3s ease;
}

.notification-content {
    display: flex;
    align-items: center;
    gap: 10px;
}

.notification-icon {
    font-size: 1.5rem;
}

.notification-text {
    font-size: 1.6rem;
    flex: 1;
}

.countdown-text {
    font-size: 1.1rem;
    color: #4a4a4a;
    margin-top: 10px;
}

.success {
    background: #e6f4ea;
    border-color: #34c759;
    color: #1a3c1f;
}

.success .notification-icon {
    color: #34c759;
}

.error {
    background: #ffe6e8;
    border-color: #f91942;
    color: #5c1c24;
}

.error .notification-icon {
    color: #f91942;
}

.back-button {
    display: inline-block;
    width: 100%;
    padding: 14px 0;
    background: #f91942;
    color: white;
    text-decoration: none;
    border-radius: 50px;
    font-size: 1.4rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(249, 25, 66, 0.3);
}

.back-button:hover {
    background: #d11236;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(249, 25, 66, 0.4);
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

@keyframes pulse {
    0%,
    100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

/* Responsive */
@media (max-width: 600px) {
    .container {
        padding: 20px;
    }

    .header-title {
        font-size: 1.5rem;
    }

    .spinner {
        width: 40px;
        height: 40px;
    }

    .loading-text {
        font-size: 1rem;
    }

    .notification-text {
        font-size: 1rem;
    }

    .countdown-text {
        font-size: 0.9rem;
    }

    .back-button {
        padding: 12px 0;
        font-size: 1rem;
    }
}
</style>
