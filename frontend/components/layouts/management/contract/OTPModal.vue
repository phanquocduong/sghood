<template>
    <div id="otp-dialog" class="mfp-hide white-popup">
        <div class="otp-header">
            <div class="otp-icon">
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
            <p class="otp-subtitle">Mã OTP đã được gửi đến số điện thoại</p>
            <p class="otp-phone">{{ maskedPhone }}</p>
        </div>

        <div class="otp-content">
            <div class="otp-input-group">
                <label for="otp-input" class="otp-label">Nhập mã OTP của bạn:</label>
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
                    <span v-for="n in 6" :key="n" :class="['otp-dot', { filled: n <= otpCode.length }]">
                        <span class="otp-dot-inner"></span>
                    </span>
                </div>
            </div>

            <div class="otp-actions">
                <button @click="closeModal" class="otp-btn otp-btn-cancel" type="button">
                    <span class="btn-icon">✕</span>
                    <span class="btn-text">Hủy</span>
                </button>
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
/* Enhanced modern design with bright colors */
#otp-dialog {
    position: relative;
    background: #fff;
    padding: 0;
    width: auto;
    max-width: 480px;
    margin: 20px auto;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08), 0 4px 16px rgba(0, 0, 0, 0.04), 0 0 0 1px rgba(255, 255, 255, 0.05);
}

.otp-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    color: white;
    padding: 50px 30px 40px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.otp-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.2) 0%, transparent 40%),
        radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.15) 0%, transparent 40%),
        linear-gradient(45deg, transparent 30%, rgba(255, 255, 255, 0.05) 50%, transparent 70%);
    pointer-events: none;
}

.otp-icon {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
    position: relative;
    z-index: 1;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255, 255, 255, 0.3);
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%,
    100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

.otp-title {
    font-size: 32px;
    font-weight: 800;
    margin: 0 0 16px;
    position: relative;
    z-index: 1;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.otp-subtitle {
    font-size: 16px;
    margin: 0 0 12px;
    opacity: 0.95;
    position: relative;
    z-index: 1;
    font-weight: 500;
}

.otp-phone {
    font-size: 20px;
    font-weight: 700;
    margin: 0;
    position: relative;
    z-index: 1;
    background: rgba(255, 255, 255, 0.25);
    padding: 16px 32px;
    border-radius: 30px;
    display: inline-block;
    margin-top: 16px;
    letter-spacing: 2px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2);
}

.otp-content {
    padding: 50px 40px 40px;
    background: #fff;
    position: relative;
}

.otp-input-group {
    margin-bottom: 32px;
}

.otp-label {
    display: block;
    font-size: 18px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 20px;
    text-align: center;
    background: linear-gradient(135deg, #667eea, #764ba2);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.otp-input {
    width: 100%;
    padding: 24px;
    border: 3px solid #e2e8f0;
    border-radius: 16px;
    font-size: 28px;
    font-weight: 800;
    text-align: center;
    letter-spacing: 12px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    outline: none;
    box-sizing: border-box;
    font-family: 'SF Mono', 'Monaco', 'Consolas', monospace;
    color: #2d3748;
}

.otp-input::placeholder {
    color: #cbd5e0;
    font-weight: 400;
}

.otp-input:focus {
    border-color: #667eea;
    background: linear-gradient(135deg, #fff 0%, #f7fafc 100%);
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1), 0 8px 32px rgba(102, 126, 234, 0.15);
    transform: translateY(-2px);
}

.otp-input-error {
    border-color: #ff6b6b;
    background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
    animation: shake 0.6s cubic-bezier(0.36, 0.07, 0.19, 0.97);
}

@keyframes shake {
    10%,
    90% {
        transform: translate3d(-1px, 0, 0);
    }
    20%,
    80% {
        transform: translate3d(2px, 0, 0);
    }
    30%,
    50%,
    70% {
        transform: translate3d(-4px, 0, 0);
    }
    40%,
    60% {
        transform: translate3d(4px, 0, 0);
    }
}

.otp-input-indicator {
    display: flex;
    justify-content: center;
    gap: 16px;
    margin-top: 24px;
}

.otp-dot {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #e2e8f0;
    position: relative;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: 3px solid #f7fafc;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

.otp-dot-inner {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: transparent;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.otp-dot.filled {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transform: scale(1.4);
    border-color: #fff;
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3), 0 2px 8px rgba(0, 0, 0, 0.08);
}

.otp-dot.filled .otp-dot-inner {
    background: #fff;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%,
    100% {
        opacity: 1;
        transform: translate(-50%, -50%) scale(1);
    }
    50% {
        opacity: 0.7;
        transform: translate(-50%, -50%) scale(1.2);
    }
}

.otp-timer {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-bottom: 40px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #fef5e7 0%, #fed7aa 100%);
    border-radius: 12px;
    color: #c05621;
    font-weight: 600;
    font-size: 14px;
    border: 1px solid #fbbf24;
}

.timer-icon {
    font-size: 16px;
}

.otp-actions {
    display: flex;
    gap: 16px;
    justify-content: center;
    margin-bottom: 32px;
}

.otp-btn {
    padding: 18px 36px;
    border-radius: 16px;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    border: none;
    min-width: 140px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-transform: none;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
}

.btn-icon {
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.otp-btn-cancel {
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    color: #4a5568;
    border: 2px solid #e2e8f0;
}

.otp-btn-cancel:hover {
    background: linear-gradient(135deg, #edf2f7 0%, #e2e8f0 100%);
    border-color: #cbd5e0;
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.otp-btn-confirm {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: 2px solid transparent;
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.3), 0 2px 8px rgba(0, 0, 0, 0.08);
}

.otp-btn-confirm:hover:not(:disabled) {
    transform: translateY(-3px);
    box-shadow: 0 8px 32px rgba(102, 126, 234, 0.4), 0 4px 16px rgba(0, 0, 0, 0.12);
}

.otp-btn-confirm:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
}

.otp-btn-confirm.loading {
    pointer-events: none;
}

.btn-spinner {
    width: 20px;
    height: 20px;
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
    font-weight: 700;
}

/* Responsive design */
@media (max-width: 600px) {
    #otp-dialog {
        max-width: 95%;
        margin: 10px auto;
        border-radius: 20px;
    }

    .otp-header {
        padding: 40px 24px 32px;
    }

    .otp-icon {
        width: 64px;
        height: 64px;
        margin-bottom: 20px;
    }

    .otp-icon svg {
        width: 32px;
        height: 32px;
    }

    .otp-title {
        font-size: 26px;
    }

    .otp-subtitle {
        font-size: 14px;
    }

    .otp-phone {
        font-size: 18px;
        padding: 14px 28px;
        letter-spacing: 1px;
    }

    .otp-content {
        padding: 40px 24px 32px;
    }

    .otp-input {
        padding: 20px;
        font-size: 24px;
        letter-spacing: 8px;
    }

    .otp-input-indicator {
        gap: 12px;
    }

    .otp-dot {
        width: 16px;
        height: 16px;
    }

    .otp-actions {
        flex-direction: column;
        gap: 12px;
    }

    .otp-btn {
        padding: 16px 32px;
        font-size: 16px;
        min-width: auto;
    }
}

/* Magnific Popup compatibility with enhanced styling */
.mfp-content {
    position: relative;
}

.mfp-close-btn-in .mfp-close {
    color: #fff;
    right: 12px;
    top: 12px;
    opacity: 0.9;
    padding: 0;
    width: 48px;
    height: 48px;
    line-height: 48px;
    text-align: center;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    font-size: 18px;
    font-weight: bold;
}

.mfp-close-btn-in .mfp-close:hover {
    opacity: 1;
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

/* Enhanced animation effects */
.mfp-zoom-in .mfp-content {
    opacity: 0;
    transform: scale(0.8) translateY(20px);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.mfp-zoom-in.mfp-ready .mfp-content {
    opacity: 1;
    transform: scale(1) translateY(0);
}

.mfp-zoom-in.mfp-removing .mfp-content {
    opacity: 0;
    transform: scale(0.8) translateY(-20px);
}

/* Additional bright animations */
@keyframes glow {
    0%,
    100% {
        box-shadow: 0 0 20px rgba(102, 126, 234, 0.3);
    }
    50% {
        box-shadow: 0 0 30px rgba(102, 126, 234, 0.5), 0 0 40px rgba(118, 75, 162, 0.3);
    }
}

.otp-btn-confirm:not(:disabled):hover {
    animation: glow 2s ease-in-out infinite;
}
</style>
