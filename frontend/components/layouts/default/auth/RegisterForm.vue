<!-- Template cho form đăng ký người dùng -->
<template>
    <!-- Tab nội dung đăng ký, ẩn mặc định -->
    <div class="tab-content" id="register" style="display: none">
        <!-- Hiển thị các bước tiến trình đăng ký -->
        <div class="progress-steps">
            <span :class="{ active: !otpSent }">B1: Nhập SĐT</span>
            <span :class="{ active: otpSent && !showRegisterFields }">B2: OTP</span>
            <span :class="{ active: showRegisterFields }">B3: Đăng ký</span>
        </div>

        <!-- Phần nhập số điện thoại và OTP -->
        <div v-show="!showRegisterFields">
            <!-- Thông tin hướng dẫn người dùng -->
            <p class="info-text">
                Nhập số điện thoại của bạn để nhận mã OTP. Chúng tôi sử dụng OTP để xác minh danh tính và bảo vệ tài khoản của bạn. Số điện
                thoại của bạn được bảo mật tuyệt đối!
            </p>
            <!-- Biểu tượng bảo mật -->
            <div class="security-icon"><i class="im im-icon-Shield"></i> Dữ liệu của bạn được bảo mật!</div>
            <!-- Form gửi yêu cầu OTP -->
            <form @submit.prevent="handleSendOTP">
                <div class="form-row form-row-wide">
                    <label for="phone">
                        Số điện thoại:
                        <i class="im im-icon-Phone-2"></i>
                        <!-- Input số điện thoại với định dạng bắt buộc -->
                        <input
                            type="tel"
                            class="input-text"
                            id="phone"
                            v-model="phone"
                            pattern="^(\+84|0)(3|5|7|8|9)\d{8}$"
                            :disabled="otpSent"
                            required
                            autocomplete="tel"
                        />
                        <!-- Hiển thị lỗi nếu số điện thoại không hợp lệ -->
                        <span v-if="phoneError" class="error-text">{{ phoneError }}</span>
                    </label>
                </div>

                <!-- Nút gửi OTP -->
                <div class="form-row" v-if="!otpSent">
                    <button class="button margin-top-10" :disabled="loading">
                        <span v-if="loading" class="spinner"></span>
                        {{ loading ? 'Đang gửi...' : 'Gửi OTP' }}
                    </button>
                    <!-- Container cho reCAPTCHA -->
                    <div id="recaptcha-container"></div>
                    <p class="recaptcha-info">Hệ thống của chúng tôi sử dụng reCAPTCHA để bảo vệ chống bot.</p>
                </div>
            </form>

            <!-- Thông báo khi OTP đã được gửi -->
            <div v-if="otpSent" class="success-message">Mã OTP đã được gửi đến số {{ phone }}. Vui lòng kiểm tra tin nhắn!</div>
            <!-- Form xác minh OTP -->
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
            <!-- Liên kết hỗ trợ -->
            <p class="support-text">Gặp vấn đề? <a href="mailto:sghoodvn@gmail.com">Liên hệ hỗ trợ</a></p>
        </div>

        <!-- Phần nhập thông tin đăng ký sau khi xác minh OTP -->
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
                        Email <span style="color: #f91942">(định dạng hợp lệ, ví dụ: user@domain.com)</span>:
                        <i class="im im-icon-Mail"> </i>
                        <!-- Input email với định dạng bắt buộc -->
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
                        Mật khẩu <span style="color: #f91942">(tối thiểu 8 ký tự, gồm chữ hoa/thường, số và ký tự đặc biệt)</span>:
                        <i class="im im-icon-Lock-2"></i>
                        <!-- Input mật khẩu với định dạng bắt buộc -->
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

                <!-- Nút hoàn tất đăng ký -->
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

// Lấy store xác thực
const authStore = useAuthStore();
// Lấy các biến trạng thái từ store
const { phone, otp, otpSent, showRegisterFields, name, email, password, confirmPassword, loading } = storeToRefs(authStore);
// Biến lưu trữ lỗi số điện thoại
const phoneError = ref('');

// Hàm chuẩn hóa số điện thoại
const normalizePhoneNumber = value => {
    let cleaned = value.replace(/[^0-9+]/g, ''); // Loại bỏ ký tự không phải số hoặc dấu +
    if (cleaned.startsWith('0')) {
        cleaned = '+84' + cleaned.slice(1); // Chuyển số bắt đầu bằng 0 thành +84
    }
    if (!cleaned.startsWith('+84') && cleaned.length > 0) {
        cleaned = '+84' + cleaned; // Thêm +84 nếu không có
    }
    return cleaned;
};

// Hàm xử lý gửi OTP
const handleSendOTP = async () => {
    phone.value = normalizePhoneNumber(phone.value); // Chuẩn hóa số điện thoại
    // Kiểm tra định dạng số điện thoại
    if (!phone.value.match(/^(\+84|0)(3|5|7|8|9)\d{8}$/)) {
        phoneError.value = 'Vui lòng nhập số điện thoại hợp lệ (bắt đầu bằng +84 hoặc 0, theo sau là 9 chữ số).';
        return;
    }
    phoneError.value = ''; // Xóa lỗi nếu số điện thoại hợp lệ
    await authStore.sendOTP(); // Gọi hàm gửi OTP từ store
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

/* CSS cho input bị vô hiệu hóa */
input:disabled {
    cursor: not-allowed;
}

/* CSS cho văn bản thông tin */
.info-text {
    font-size: 0.9em;
    color: #555;
    margin-bottom: 15px;
    text-align: center;
}

/* CSS cho biểu tượng bảo mật */
.security-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 0.9em;
    color: #2ecc71;
    margin-bottom: 15px;
}

/* CSS cho thông báo lỗi */
.error-text {
    color: #e74c3c;
    font-size: 0.8em;
    margin-top: 5px;
    display: block;
}

/* CSS cho các bước tiến trình */
.progress-steps {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    font-size: 0.9em;
    color: #999;
}

/* CSS cho bước đang hoạt động */
.progress-steps span.active {
    color: #2ecc71;
    font-weight: bold;
}

/* CSS cho thông báo thành công */
.success-message {
    background-color: #dff0d8;
    color: #3c763d;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 15px;
    text-align: center;
}

/* CSS cho thông tin reCAPTCHA */
.recaptcha-info {
    font-size: 0.8em;
    color: #555;
    text-align: center;
    margin-top: 10px;
}

/* CSS cho văn bản hỗ trợ */
.support-text {
    font-size: 0.8em;
    color: #555;
    text-align: center;
    margin-top: 10px;
}

/* CSS cho liên kết hỗ trợ */
.support-text a {
    color: #3498db;
    text-decoration: none;
}

.support-text a:hover {
    text-decoration: underline;
}
</style>
