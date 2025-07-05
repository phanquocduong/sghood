<template>
    <div class="tab-content" id="register" style="display: none">
        <div v-show="!showRegisterFields">
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
                        />
                    </label>
                </div>

                <div class="form-row" v-if="!otpSent">
                    <button class="button margin-top-10" :disabled="loading">
                        <span v-if="loading" class="spinner"></span>
                        {{ loading ? 'Đang gửi...' : 'Gửi OTP' }}
                    </button>
                    <div id="recaptcha-container"></div>
                </div>
            </form>

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

const authStore = useAuthStore();
const { phone, otp, otpSent, showRegisterFields, name, email, password, confirmPassword, loading } = storeToRefs(authStore);

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
</style>
