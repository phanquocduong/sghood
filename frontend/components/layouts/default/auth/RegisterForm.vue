<template>
    <div class="tab-content" id="register" style="display: none">
        <div class="progress-steps">
            <span :class="{ active: !otpSent }">B1: Nhập SĐT</span>
            <span :class="{ active: otpSent && !showRegisterFields }">B2: OTP</span>
            <span :class="{ active: showRegisterFields }">B3: Đăng ký</span>
        </div>

        <div v-show="!showRegisterFields">
            <p class="info-text">
                Nhập số điện thoại của bạn để nhận mã OTP. Chúng tôi sử dụng OTP để xác minh danh tính và bảo vệ tài khoản của bạn. Số điện
                thoại của bạn được bảo mật tuyệt đối!
            </p>
            <div class="security-icon"><i class="im im-icon-Shield"></i> Dữ liệu của bạn được bảo mật!</div>
            <form @submit.prevent="handleSendOTP">
                <div class="form-row form-row-wide">
                    <label for="phone">
                        Số điện thoại:
                        <i class="im im-icon-Phone-2"></i>
                        <input
                            type="tel"
                            class="input-text"
                            id="phone"
                            v-model="phone"
                            required
                            pattern="^(\+84|0)(3|5|7|8|9)\d{8}$"
                            :disabled="otpSent"
                            placeholder="+84xxxxxxxxx"
                            autocomplete="tel"
                        />
                        <span v-if="phoneError" class="error-text">{{ phoneError }}</span>
                    </label>
                </div>

                <div class="form-row" v-if="!otpSent">
                    <button class="button margin-top-10" :disabled="loading">
                        <span v-if="loading" class="spinner"></span>
                        {{ loading ? 'Đang gửi...' : 'Gửi OTP' }}
                    </button>
                    <div id="recaptcha-container"></div>
                    <p class="recaptcha-info">Hệ thống của chúng tôi sử dụng reCAPTCHA để bảo vệ chống bot.</p>
                </div>
            </form>

            <div v-if="otpSent" class="success-message">Mã OTP đã được gửi đến số {{ phone }}. Vui lòng kiểm tra tin nhắn!</div>
            <div v-if="otpSent">
                <form @submit.prevent="authStore.verifyOTP">
                    <div class="form-row form-row-wide">
                        <label for="otp">
                            Mã OTP:
                            <i class="im im-icon-Mailbox-Empty"></i>
                            <input type="text" class="input-text" id="otp" v-model="otp" required />
                        </label>
                    </div>
                    <button class="button margin-top-10" :disabled="loading">
                        <span v-if="loading" class="spinner"></span>
                        {{ loading ? 'Đang xác minh...' : 'Xác minh OTP' }}
                    </button>
                </form>
            </div>
            <p class="support-text">Gặp vấn đề? <a href="mailto:sghoodvn@gmail.com">Liên hệ hỗ trợ</a></p>
        </div>

        <div v-show="showRegisterFields">
            <form @submit.prevent="authStore.registerUser">
                <div class="form-row form-row-wide">
                    <label for="name">
                        Họ và tên:
                        <i class="im im-icon-Male"></i>
                        <input type="text" class="input-text" id="name" v-model="name" required />
                    </label>
                </div>

                <div class="form-row form-row-wide">
                    <label for="email">
                        Email:
                        <i class="im im-icon-Mail tooltip">
                            <span class="tooltip-text"> Email phải có định dạng hợp lệ, ví dụ: user@domain.com </span>
                        </i>
                        <input
                            type="email"
                            class="input-text"
                            id="email"
                            v-model="email"
                            required
                            pattern="^[A-Za-z0-9](([_\.\-]?[a-zA-Z0-9]+)*)@([A-Za-z0-9]+)(([\.\-]?[a-zA-Z0-9]+)*)\.([A-Za-z]{2,})$"
                        />
                    </label>
                </div>

                <div class="form-row form-row-wide">
                    <label for="password2">
                        Mật khẩu:
                        <i class="im im-icon-Lock-2 tooltip">
                            <span class="tooltip-text">
                                Mật khẩu phải tối thiểu 8 ký tự, bao gồm chữ hoa, chữ thường, số và ký tự đặc biệt (@$!%*?&.-)
                            </span>
                        </i>
                        <input
                            type="password"
                            class="input-text"
                            id="password2"
                            v-model="password"
                            required
                            pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&\.\-]{8,}$"
                        />
                    </label>
                </div>

                <div class="form-row form-row-wide">
                    <label for="confirm_password">
                        Xác nhận mật khẩu:
                        <i class="im im-icon-Lock-2"></i>
                        <input type="password" class="input-text" id="confirm_password" v-model="confirmPassword" required />
                    </label>
                </div>

                <button class="button margin-top-10" :disabled="loading">
                    <span v-if="loading" class="spinner"></span>
                    {{ loading ? 'Đang đăng ký...' : 'Hoàn tất đăng ký' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { storeToRefs } from 'pinia';
import { useAuthStore } from '~/stores/auth';
import { ref } from 'vue';

const authStore = useAuthStore();
const { phone, otp, otpSent, showRegisterFields, name, email, password, confirmPassword, loading } = storeToRefs(authStore);
const phoneError = ref('');

const normalizePhoneNumber = value => {
    let cleaned = value.replace(/[^0-9+]/g, '');
    if (cleaned.startsWith('0')) {
        cleaned = '+84' + cleaned.slice(1);
    }
    if (!cleaned.startsWith('+84') && cleaned.length > 0) {
        cleaned = '+84' + cleaned;
    }
    return cleaned;
};

const handleSendOTP = async () => {
    phone.value = normalizePhoneNumber(phone.value);
    if (!phone.value.match(/^(\+84|0)(3|5|7|8|9)\d{8}$/)) {
        phoneError.value = 'Vui lòng nhập số điện thoại hợp lệ (bắt đầu bằng +84 hoặc 0, theo sau là 9 chữ số).';
        return;
    }
    phoneError.value = '';
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

input:disabled {
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
    bottom: 125%;
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

.info-text {
    font-size: 0.9em;
    color: #555;
    margin-bottom: 15px;
    text-align: center;
}

.security-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 0.9em;
    color: #2ecc71;
    margin-bottom: 15px;
}

.error-text {
    color: #e74c3c;
    font-size: 0.8em;
    margin-top: 5px;
    display: block;
}

.progress-steps {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    font-size: 0.9em;
    color: #999;
}

.progress-steps span.active {
    color: #2ecc71;
    font-weight: bold;
}

.success-message {
    background-color: #dff0d8;
    color: #3c763d;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 15px;
    text-align: center;
}

.recaptcha-info {
    font-size: 0.8em;
    color: #555;
    text-align: center;
    margin-top: 10px;
}

.support-text {
    font-size: 0.8em;
    color: #555;
    text-align: center;
    margin-top: 10px;
}

.support-text a {
    color: #3498db;
    text-decoration: none;
}

.support-text a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .input-text {
        font-size: 1.1em;
        padding: 12px;
    }
    .button {
        padding: 12px;
        font-size: 1.1em;
    }
}
</style>
