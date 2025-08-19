<template>
    <div class="tab-content" id="forgot-password" style="display: none">
        <div v-show="!showResetFields">
            <form @submit.prevent="handleSendOTP">
                <div class="form-row form-row-wide">
                    <label for="phone2">
                        Số điện thoại:
                        <i class="im im-icon-Phone-2"></i>
                        <input type="tel" class="input-text" id="phone2" v-model="phone" required pattern="^(\+84|0)(3|5|7|8|9)\d{8}$" />
                    </label>
                </div>
                <div class="form-row" v-if="!otpSent">
                    <button type="submit" class="button" :disabled="loading">
                        <span v-if="loading" class="spinner"></span>
                        {{ loading ? 'Đang xử lý...' : 'Gửi yêu cầu' }}
                    </button>
                    <div id="recaptcha-container"></div>
                </div>
            </form>
            <div v-if="otpSent">
                <form @submit.prevent="authStore.verifyOTP">
                    <div class="form-row form-row-wide">
                        <label for="otp2">
                            Mã OTP:
                            <i class="im im-icon-Mailbox-Empty"></i>
                            <input type="text" class="input-text" id="otp2" v-model="otp" required />
                        </label>
                    </div>
                    <button class="button margin-top-10" :disabled="loading">
                        <span v-if="loading" class="spinner"></span>
                        {{ loading ? 'Đang xác minh...' : 'Xác minh OTP' }}
                    </button>
                </form>
            </div>
        </div>

        <div v-show="showResetFields">
            <form @submit.prevent="authStore.resetPassword">
                <div class="form-row form-row-wide">
                    <label for="password3">
                        Mật khẩu mới
                        <span style="color: #f91942">(tối thiểu 8 ký tự, gồm chữ hoa/thường, số và ký tự đặc biệt)</span>:
                        <i class="im im-icon-Lock-2"></i>

                        <input
                            type="password"
                            class="input-text"
                            id="password3"
                            v-model="password"
                            pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&\.\-]{8,}$"
                            required
                        />
                    </label>
                </div>
                <div class="form-row form-row-wide">
                    <label for="password_confirmation2">
                        Xác nhận mật khẩu:
                        <i class="im im-icon-Lock-2"></i>
                        <input type="password" class="input-text" id="password_confirmation2" v-model="confirmPassword" required />
                    </label>
                </div>
                <div class="form-row">
                    <button type="submit" class="button" :disabled="loading">
                        <span v-if="loading" class="spinner"></span>
                        {{ loading ? 'Đang đặt lại...' : 'Đặt lại mật khẩu' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { storeToRefs } from 'pinia';
import { useAuthStore } from '~/stores/auth';

const authStore = useAuthStore();
const { phone, otp, otpSent, showResetFields, password, confirmPassword, loading } = storeToRefs(authStore);

// Hàm chuẩn hóa số điện thoại
const normalizePhoneNumber = value => {
    let cleaned = value.replace(/[^0-9+]/g, '');

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

// Hàm xử lý gửi OTP
const handleSendOTP = async () => {
    // Chuẩn hóa số điện thoại trước khi gửi
    phone.value = normalizePhoneNumber(phone.value);

    // Gọi hàm gửi OTP từ authStore
    await authStore.sendOTP();
};
</script>

<style scoped>
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

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.tooltip {
    display: inline-block;
    cursor: help;
}

.tooltip .tooltip-text {
    visibility: hidden;
    width: 250px;
    background-color: #333;
    color: #fff;
    text-align: center;
    border-radius: 6px;
    padding: 8px;
    position: absolute;
    z-index: 1;
    top: 125%;
    left: 50%;
    transform: translateX(-50%);
    opacity: 0;
    transition: opacity 0.3s;
    font-size: 0.8em;
}

.tooltip:hover .tooltip-text {
    visibility: visible;
    opacity: 1;
}
</style>
