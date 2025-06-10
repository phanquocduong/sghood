<template>
    <div class="tab-content" id="forgot-password" style="display: none">
        <div v-show="!showResetFields">
            <form @submit.prevent="authStore.sendOTP">
                <div class="form-row form-row-wide">
                    <label for="phone2">
                        Số điện thoại:
                        <i class="im im-icon-Phone-2"></i>
                        <input
                            type="tel"
                            class="input-text"
                            id="phone2"
                            v-model="phone"
                            required
                            placeholder="Nhập số điện thoại"
                            pattern="^(?:\+84|0)(3|5|7|8|9)\d{8}$"
                        />
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
                            <input type="text" class="input-text" id="otp2" v-model="otp" placeholder="Nhập mã OTP" required />
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
                        Mật khẩu mới:
                        <i class="im im-icon-Lock-2"></i>
                        <input type="password" class="input-text" id="password3" v-model="password" required />
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
</style>
