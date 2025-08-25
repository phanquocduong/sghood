<!-- Template cho form quên mật khẩu -->
<template>
    <!-- Tab nội dung quên mật khẩu, ẩn mặc định -->
    <div class="tab-content" id="forgot-password" style="display: none">
        <!-- Form gửi yêu cầu đặt lại mật khẩu -->
        <form @submit.prevent="authStore.resetPassword">
            <p class="form-row form-row-wide">
                <label for="phone">
                    SĐT:
                    <i class="im im-icon-Phone-2"></i>
                    <!-- Input số điện thoại với định dạng bắt buộc -->
                    <input
                        type="tel"
                        class="input-text"
                        id="phone"
                        v-model="phone"
                        pattern="^(\+84|0)(3|5|7|8|9)\d{8}$"
                        required
                        autocomplete="tel"
                    />
                </label>
            </p>

            <!-- Form OTP nếu đã gửi mã -->
            <div v-if="showResetFields">
                <p class="form-row form-row-wide">
                    <label for="otp">
                        Mã OTP:
                        <i class="im im-icon-Mailbox-Empty"></i>
                        <input type="text" class="input-text" id="otp" v-model="otp" required />
                    </label>
                </p>

                <p class="form-row form-row-wide">
                    <label for="password">
                        Mật khẩu mới:
                        <i class="im im-icon-Lock-2"></i>
                        <!-- Input mật khẩu với định dạng bắt buộc -->
                        <input
                            type="password"
                            class="input-text"
                            id="password"
                            v-model="password"
                            required
                            pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&\.\-]{8,}$"
                        />
                    </label>
                </p>

                <p class="form-row form-row-wide">
                    <label for="confirm_password">
                        Xác nhận mật khẩu:
                        <i class="im im-icon-Lock-2"></i>
                        <input type="password" class="input-text" id="confirm_password" v-model="confirmPassword" required />
                    </label>
                </p>
            </div>

            <!-- Nút gửi yêu cầu -->
            <div class="form-row">
                <button type="submit" class="button" :disabled="loading">
                    <span v-if="loading" class="spinner"></span>
                    {{ loading ? 'Đang xử lý...' : showResetFields ? 'Đặt lại mật khẩu' : 'Gửi OTP' }}
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
const { phone, otp, password, confirmPassword, loading, showResetFields } = storeToRefs(authStore);
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
