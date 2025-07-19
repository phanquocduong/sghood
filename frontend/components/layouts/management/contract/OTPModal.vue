<template>
    <div id="otp-dialog" class="mfp-hide white-popup">
        <div class="otp-header">
            <h2 class="otp-title">Xác minh OTP</h2>
            <p class="otp-subtitle">Mã OTP đã được gửi đến số điện thoại</p>
            <p class="otp-phone">{{ maskedPhone }}</p>
        </div>

        <div class="otp-content">
            <div class="otp-input-group">
                <label for="otp-input" class="otp-label">Nhập mã OTP:</label>
                <input
                    id="otp-input"
                    v-model="otpCode"
                    type="text"
                    class="otp-input"
                    placeholder="Nhập 6 chữ số"
                    maxlength="6"
                    autocomplete="off"
                    :class="{ 'otp-input-error': otpCode.length > 0 && otpCode.length < 6 }"
                />
                <div class="otp-input-indicator">
                    <span v-for="n in 6" :key="n" :class="['otp-dot', { filled: n <= otpCode.length }]"></span>
                </div>
            </div>

            <div class="otp-actions">
                <button @click="closeModal" class="otp-btn otp-btn-cancel" type="button">
                    <span class="btn-text">Hủy</span>
                </button>
                <button
                    @click="$emit('confirm')"
                    class="otp-btn otp-btn-confirm"
                    :disabled="otpCode.length !== 6 || loading"
                    :class="{ loading: loading }"
                >
                    <span v-if="loading" class="btn-spinner"></span>
                    <span class="btn-text">{{ loading ? 'Đang xác minh...' : 'Xác minh' }}</span>
                </button>
            </div>
        </div>

        <div id="recaptcha-container" class="recaptcha-container"></div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    phoneNumber: { type: String, required: true },
    loading: { type: Boolean, required: true }
});

const emit = defineEmits(['confirm']);

const otpCode = defineModel('otpCode', { type: String, default: '' });

const maskedPhone = computed(() => {
    if (!props.phoneNumber) return '';
    const phone = props.phoneNumber.replace(/^\+84/, '0');
    return phone.slice(0, 3) + '****' + phone.slice(-4);
});

const closeModal = () => {
    if (window.jQuery && window.jQuery.fn.magnificPopup) {
        window.jQuery.magnificPopup.close();
    }
};
</script>

<style scoped>
/* Sử dụng class white-popup chuẩn của Magnific Popup */
#otp-dialog {
    position: relative;
    background: #fff;
    padding: 0;
    width: auto;
    max-width: 500px;
    margin: 20px auto;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.otp-header {
    background: linear-gradient(135deg, #ff3366 0%, #ff4757 100%);
    color: white;
    padding: 40px 30px 30px;
    text-align: center;
    position: relative;
}

.otp-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: repeating-linear-gradient(
        45deg,
        transparent,
        transparent 10px,
        rgba(255, 255, 255, 0.05) 10px,
        rgba(255, 255, 255, 0.05) 20px
    );
}

.otp-title {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 12px;
    position: relative;
    z-index: 1;
}

.otp-subtitle {
    font-size: 15px;
    margin: 0 0 8px;
    opacity: 0.9;
    position: relative;
    z-index: 1;
}

.otp-phone {
    font-size: 18px;
    font-weight: 700;
    margin: 0;
    position: relative;
    z-index: 1;
    background: rgba(255, 255, 255, 0.2);
    padding: 12px 24px;
    border-radius: 25px;
    display: inline-block;
    margin-top: 12px;
    letter-spacing: 1px;
}

.otp-content {
    padding: 40px 30px 30px;
    background: #fff;
}

.otp-input-group {
    margin-bottom: 40px;
}

.otp-label {
    display: block;
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
    text-align: center;
}

.otp-input {
    width: 100%;
    padding: 20px;
    border: 3px solid #e8ecf0;
    border-radius: 12px;
    font-size: 24px;
    font-weight: 700;
    text-align: center;
    letter-spacing: 8px;
    transition: all 0.3s ease;
    background: #fafbfc;
    outline: none;
    box-sizing: border-box;
    font-family: 'Courier New', monospace;
}

.otp-input:focus {
    border-color: #ff3366;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(255, 51, 102, 0.1);
    transform: translateY(-2px);
}

.otp-input-error {
    border-color: #ff6b6b;
    background: #fff5f5;
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%,
    100% {
        transform: translateX(0);
    }
    25% {
        transform: translateX(-5px);
    }
    75% {
        transform: translateX(5px);
    }
}

.otp-input-indicator {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 20px;
}

.otp-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #e8ecf0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid transparent;
}

.otp-dot.filled {
    background: #ff3366;
    transform: scale(1.3);
    border-color: #ff3366;
    box-shadow: 0 0 10px rgba(255, 51, 102, 0.4);
}

.otp-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.otp-btn {
    padding: 16px 32px;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    min-width: 130px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.otp-btn-cancel {
    background: #f8f9fa;
    color: #6c757d;
    border: 2px solid #e9ecef;
}

.otp-btn-cancel:hover {
    background: #e9ecef;
    border-color: #dee2e6;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.otp-btn-confirm {
    background: linear-gradient(135deg, #ff3366 0%, #ff4757 100%);
    color: white;
    border: 2px solid transparent;
    box-shadow: 0 4px 15px rgba(255, 51, 102, 0.3);
}

.otp-btn-confirm:hover:not(:disabled) {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(255, 51, 102, 0.4);
}

.otp-btn-confirm:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.otp-btn-confirm.loading {
    pointer-events: none;
}

.btn-spinner {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.btn-text {
    white-space: nowrap;
}

.recaptcha-container {
    padding: 0 30px 30px;
    display: flex;
    justify-content: center;
    background: #fff;
}

/* Responsive design */
@media (max-width: 600px) {
    #otp-dialog {
        max-width: 95%;
        margin: 10px auto;
    }

    .otp-header {
        padding: 30px 20px 25px;
    }

    .otp-title {
        font-size: 22px;
    }

    .otp-subtitle {
        font-size: 14px;
    }

    .otp-phone {
        font-size: 16px;
        padding: 10px 20px;
    }

    .otp-content {
        padding: 30px 20px 25px;
    }

    .otp-input {
        padding: 16px;
        font-size: 20px;
        letter-spacing: 4px;
    }

    .otp-actions {
        flex-direction: column;
        gap: 12px;
    }

    .otp-btn {
        padding: 14px 28px;
        font-size: 14px;
        min-width: auto;
    }

    .recaptcha-container {
        padding: 0 20px 25px;
    }
}

/* Magnific Popup compatibility */
.mfp-content {
    position: relative;
}

.mfp-close-btn-in .mfp-close {
    color: #fff;
    right: 6px;
    top: 6px;
    opacity: 0.8;
    padding: 0;
    width: 44px;
    height: 44px;
    line-height: 44px;
    text-align: center;
    background: rgba(0, 0, 0, 0.2);
    border-radius: 50%;
    transition: all 0.3s ease;
}

.mfp-close-btn-in .mfp-close:hover {
    opacity: 1;
    background: rgba(0, 0, 0, 0.3);
}

/* Animation effects */
.mfp-zoom-in .mfp-content {
    opacity: 0;
    transform: scale(0.8);
    transition: all 0.3s ease-in-out;
}

.mfp-zoom-in.mfp-ready .mfp-content {
    opacity: 1;
    transform: scale(1);
}

.mfp-zoom-in.mfp-removing .mfp-content {
    opacity: 0;
    transform: scale(0.8);
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    #otp-dialog {
        background: #1e1e1e;
    }

    .otp-content {
        background: #1e1e1e;
    }

    .otp-label {
        color: #e1e5e9;
    }

    .otp-input {
        background: #2d2d2d;
        border-color: #444;
        color: #e1e5e9;
    }

    .otp-input:focus {
        background: #333;
        border-color: #ff3366;
    }

    .otp-btn-cancel {
        background: #2d2d2d;
        color: #e1e5e9;
        border-color: #444;
    }

    .otp-btn-cancel:hover {
        background: #333;
        border-color: #555;
    }

    .recaptcha-container {
        background: #1e1e1e;
    }
}
</style>
