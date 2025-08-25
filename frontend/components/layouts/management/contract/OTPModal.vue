<template>
    <!-- Modal xác minh OTP -->
    <div id="otp-dialog" class="mfp-hide white-popup">
        <div class="otp-header">
            <div class="otp-icon">
                <!-- Icon đồng hồ -->
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M20 12C20 16.4183 16.4183 20 12 20C7.58172 20 4 16.4183 4 12C4 7.58172 7.58172 4 12 4C16.4183 4 20 7.58172 20 12Z"
                        stroke="currentColor"
                        stroke-width="2"
                    />
                    <path d="M12 8V12L15 15" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
            </div>
            <h2 class="otp-title">Xác minh OTP</h2>
            <!-- Tiêu đề modal -->
            <p class="otp-subtitle">Mã OTP đã được gửi đến số điện thoại</p>
            <!-- Phụ đề -->
            <p class="otp-phone">{{ maskedPhone }}</p>
            <!-- Số điện thoại ẩn một phần -->
        </div>

        <div class="otp-content">
            <div class="otp-input-group">
                <label for="otp-input" class="otp-label">Nhập mã OTP của bạn:</label>
                <!-- Nhãn input OTP -->
                <input
                    id="otp-input"
                    v-model="otpCode"
                    type="text"
                    class="otp-input"
                    placeholder="● ● ● ● ● ●"
                    maxlength="6"
                    autocomplete="off"
                    :class="{ 'otp-input-error': otpCode.length > 0 && otpCode.length < 6 }"
                />
                <div class="otp-input-indicator">
                    <!-- Chỉ báo cho từng ký tự OTP -->
                    <span v-for="n in 6" :key="n" :class="['otp-dot', { filled: n <= otpCode.length }]">
                        <span class="otp-dot-inner"></span>
                    </span>
                </div>
            </div>

            <div class="otp-actions">
                <!-- Nút hủy -->
                <button @click="closeModal" class="otp-btn otp-btn-cancel" type="button">
                    <span class="btn-icon">✕</span>
                    <span class="btn-text">Hủy</span>
                </button>
                <!-- Nút xác minh OTP -->
                <button
                    @click="$emit('confirm')"
                    class="otp-btn otp-btn-confirm"
                    :disabled="otpCode.length !== 6 || loading"
                    :class="{ loading: loading }"
                >
                    <span v-if="loading" class="btn-spinner"></span>
                    <span v-else class="btn-icon">✓</span>
                    <span class="btn-text">{{ loading ? 'Đang xác minh...' : 'Xác minh' }}</span>
                </button>
            </div>
        </div>

        <div id="recaptcha-container" class="recaptcha-container"></div>
        <!-- Container cho reCAPTCHA -->
    </div>
</template>

<script setup>
import { computed } from 'vue';

// Định nghĩa props
const props = defineProps({
    phoneNumber: { type: String, required: true }, // Số điện thoại
    loading: { type: Boolean, required: true } // Trạng thái loading
});

// Định nghĩa emits
const emit = defineEmits(['confirm']);

// Model cho mã OTP
const otpCode = defineModel('otpCode', { type: String, default: '' });

// Ẩn một phần số điện thoại
const maskedPhone = computed(() => {
    if (!props.phoneNumber) return '';
    const phone = props.phoneNumber.replace(/^\+84/, '0');
    return phone.slice(0, 3) + '****' + phone.slice(-4); // Hiển thị số điện thoại dạng 012****3456
});

// Đóng modal OTP
const closeModal = () => {
    if (window.jQuery && window.jQuery.fn.magnificPopup) {
        window.jQuery.magnificPopup.close(); // Đóng modal
    }
};
</script>

<style scoped>
@import '~/public/css/otp-modal.css';
</style>
