<!-- Template cho form đăng nhập -->
<template>
    <!-- Tab nội dung đăng nhập, ẩn mặc định -->
    <div class="tab-content" id="login" style="display: none">
        <!-- Form đăng nhập -->
        <form @submit.prevent="handleLogin">
            <p class="form-row form-row-wide">
                <label for="username">
                    SĐT hoặc Email:
                    <i class="im im-icon-Male"></i>
                    <input type="text" class="input-text" id="username" v-model="username" required />
                </label>
            </p>

            <p class="form-row form-row-wide">
                <label for="password">
                    Mật khẩu:
                    <i class="im im-icon-Lock-2"></i>
                    <input class="input-text" type="password" id="password" v-model="password" required />
                </label>
                <!-- Liên kết quên mật khẩu -->
                <span class="lost_password">
                    <a href="#" @click.prevent="showForgotPassword">Quên mật khẩu?</a>
                </span>
            </p>

            <!-- Nút đăng nhập -->
            <div class="form-row">
                <button type="submit" class="button" :disabled="loading">
                    <span v-if="loading" class="spinner"></span>
                    {{ loading ? 'Đang đăng nhập...' : 'Đăng nhập' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { storeToRefs } from 'pinia';
import { useAuthStore } from '~/stores/auth';

// Lấy store xác thực
const authStore = useAuthStore();
// Lấy các biến trạng thái từ store
const { username, password, loading } = storeToRefs(authStore);

// Hàm chuẩn hóa số điện thoại
const normalizePhoneNumber = value => {
    // Nếu giá trị là email, không cần chuẩn hóa
    if (value.includes('@')) {
        return value;
    }

    let cleaned = value.replace(/[^0-9+]/g, ''); // Loại bỏ ký tự không phải số hoặc dấu +
    // Nếu số bắt đầu bằng 0, chuyển thành +84
    if (cleaned.startsWith('0')) {
        cleaned = '+84' + cleaned.slice(1);
    }
    // Nếu số không bắt đầu bằng +84, thêm +84 vào
    if (!cleaned.startsWith('+84') && cleaned.length > 0) {
        cleaned = '+84' + cleaned;
    }
    return cleaned;
};

// Hàm xử lý đăng nhập
const handleLogin = async () => {
    // Chuẩn hóa username trước khi gửi
    if (!username.value.includes('@')) {
        username.value = normalizePhoneNumber(username.value);
    }
    // Gọi hàm đăng nhập từ authStore
    await authStore.loginUser();
};

// Hàm hiển thị form quên mật khẩu
const showForgotPassword = () => {
    if (typeof window !== 'undefined' && window.$.magnificPopup) {
        window.$('.tab-content').hide(); // Ẩn tất cả tab
        window.$('#forgot-password').show(); // Hiển thị tab quên mật khẩu
    }
};
</script>

<!-- CSS tùy chỉnh cho component -->
<style scoped>
/* CSS cho spinner loading */
.spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
    margin-right: 8px;
    vertical-align: middle;
}

/* Keyframes cho hiệu ứng quay của spinner */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* CSS cho nút bị vô hiệu hóa */
.button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
