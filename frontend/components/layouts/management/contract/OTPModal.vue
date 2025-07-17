<template>
    <div v-if="show" class="modal-overlay">
        <div class="modal-content">
            <h4>Xác minh OTP</h4>
            <p>
                Mã OTP đã được gửi đến số điện thoại {{ maskedPhone }} <br />
                Vui lòng nhập mã OTP:
            </p>
            <input v-model="otpCode" type="text" class="form-control" placeholder="Nhập mã OTP" maxlength="6" />
            <div class="modal-actions">
                <button class="btn btn-clear" @click="$emit('close')">Hủy</button>
                <button class="btn btn-confirm" @click="$emit('confirm')" :disabled="otpCode.length !== 6 || loading">
                    <span v-if="loading" class="button-spinner"></span>
                    {{ loading ? 'Đang xác minh...' : 'Xác minh' }}
                </button>
            </div>
            <div id="recaptcha-container" class="recaptcha-container"></div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, nextTick } from 'vue';

const prop = defineProps({
    show: { type: Boolean, required: true },
    phoneNumber: { type: String, required: true },
    loading: { type: Boolean, required: true }
});

defineEmits(['close', 'confirm']);

const otpCode = defineModel('otpCode', { type: String, default: '' });

const maskedPhone = computed(() => {
    if (!prop.phoneNumber) return '';
    const phone = prop.phoneNumber.replace(/^\+84/, '0');
    return phone.slice(0, 3) + '****' + phone.slice(-4);
});

onMounted(async () => {
    if (prop.show) {
        await nextTick();
        const recaptchaContainer = document.getElementById('recaptcha-container');
        if (!recaptchaContainer) {
            console.error('recaptcha-container not found in DOM');
        } else {
            console.log('recaptcha-container found:', recaptchaContainer);
        }
    }
});
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6); /* Tăng độ mờ cho nền */
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 2000;
    animation: fadeIn 0.3s ease-in-out;
}

.modal-content {
    background: #fff;
    border-radius: 16px;
    padding: 32px;
    max-width: 450px;
    width: 90%;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    text-align: center;
    animation: slideUp 0.3s ease-in-out;
}

.modal-content h4 {
    font-size: 2.2rem;
    font-weight: 700;
    color: #f91942; /* Màu chủ đạo cho tiêu đề */
    margin-bottom: 12px;
}

.modal-content p {
    font-size: 1.4rem;
    color: #4b5563;
    margin-bottom: 20px;
    line-height: 2.4rem;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 1.4rem;
    margin-bottom: 24px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: #f91942; /* Màu chủ đạo khi focus */
    box-shadow: 0 0 0 3px rgba(249, 25, 66, 0.2);
}

.modal-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
}

.btn {
    flex: 1;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 1.3rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-clear {
    background: #fff;
    border: 2px solid #d1d5db;
    color: #4b5563;
}

.btn-clear:hover:not(:disabled) {
    background: #f3f4f6;
    border: 2px solid #ccc;
}

.btn-clear:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(249, 25, 66, 0.2);
}

.btn-confirm {
    background: #f91942; /* Màu chủ đạo */
    color: #fff;
    border: none;
}

.btn-confirm:hover:not(:disabled) {
    background: #d11336; /* Màu chủ đạo tối hơn khi hover */
    color: white;
}

.btn-confirm:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(249, 25, 66, 0.3);
}

.btn-confirm:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.button-spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid white;
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

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@keyframes slideUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@media (max-width: 480px) {
    .modal-content {
        padding: 24px;
    }

    .modal-content h4 {
        font-size: 1.5rem;
    }

    .modal-content p {
        font-size: 1rem;
    }

    .form-control {
        font-size: 1rem;
    }

    .btn {
        font-size: 1rem;
        padding: 10px;
    }
}
</style>