<template>
    <div id="otp-dialog" class="zoom-anim-dialog mfp-hide">
        <div class="small-dialog-header">
            <h3>Kiểm tra thông tin chuyển khoản</h3>
            <p class="booking-subtitle">Có thể quét mã QR để kiểm tra thông tin chuyển khoản</p>
        </div>
        <div class="message-reply margin-top-0">
            <div class="modal-content">
                <p v-if="checkout?.bank_info" class="mt-3">
                    <strong>Thông tin ngân hàng:</strong>
                    <br />
                    <span v-html="formatBankInfo(checkout?.bank_info)"></span>
                </p>
                <div v-if="checkout?.bank_info" class="qr-code">
                    <h5>Test mã QR chuyển khoản</h5>
                    <img
                        :src="`https://qr.sepay.vn/img?bank=${checkout.bank_info.bank_name}&acc=${checkout.bank_info.account_number}&amount=&des=&template=qronly`"
                        alt="Mã QR"
                        style="max-width: 400px; margin: 0 auto; display: block"
                    />
                </div>
                <div v-if="checkout?.receipt_path" class="receipt">
                    <h5>Ảnh biên lai</h5>
                    <img
                        :src="useRuntimeConfig().public.baseUrl + '/storage/' + checkout.receipt_path"
                        alt="Biên lai"
                        style="max-width: 400px; margin: 0 auto; display: block"
                    />
                </div>
                <p class="mt-3"><em>Nếu thông tin không đúng, vui lòng chỉnh sửa:</em></p>
                <button class="button gray" @click="$emit('open-edit-bank-modal')">Chỉnh sửa thông tin chuyển khoản</button>
            </div>
            <div class="booking-actions">
                <button @click="$emit('close')" class="button gray" type="button"><i class="fa fa-times"></i> Đóng</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useRuntimeConfig } from '#app';

defineProps({
    checkout: { type: Object, required: true }
});

defineEmits(['close', 'open-edit-bank-modal']);

const formatBankInfo = bankInfo => {
    if (!bankInfo || typeof bankInfo !== 'object') return 'Không có thông tin';
    const fields = [
        bankInfo.bank_name ? `Ngân hàng: ${bankInfo.bank_name}` : '',
        bankInfo.account_number ? `Số tài khoản: ${bankInfo.account_number}` : '',
        bankInfo.account_holder ? `Chủ tài khoản: ${bankInfo.account_holder}` : ''
    ].filter(Boolean);
    return fields.join('<br>');
};
</script>

<style scoped>
/* Refund Information */
.modal-content {
    padding: 16px;
}

.modal-content p {
    font-size: 14px;
    color: #4a5568;
    margin-bottom: 8px;
}

.qr-code,
.receipt {
    margin-top: 16px;
    text-align: center;
}

.qr-code h5,
.receipt h5 {
    font-size: 14px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
}

/* Modal Header */
.small-dialog-header {
    background: linear-gradient(135deg, #f91942 0%, #ff5f7e 100%);
    padding: 25px 30px;
    color: white;
    position: relative;
    overflow: hidden;
    margin-bottom: 0;
}

.small-dialog-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Ccircle cx='30' cy='30' r='4'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")
        repeat;
}

.small-dialog-header h3 {
    margin: 0 0 5px 0;
    font-size: 22px;
    font-weight: 600;
    position: relative;
    z-index: 1;
    color: white;
    font-weight: bolder;
}

.booking-subtitle {
    margin: 0;
    opacity: 0.9;
    font-size: 14px;
    position: relative;
    z-index: 1;
    color: white;
    font-weight: 500;
}

/* Button Actions */
.booking-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 20px;
}

.button {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 120px;
    justify-content: center;
    text-decoration: none;
}

.button.gray {
    background: #f7fafc;
    color: #4a5568;
    border: 2px solid #e2e8f0;
}

.button.gray:hover {
    background: #edf2f7;
    border-color: #cbd5e0;
    transform: translateY(-1px);
}

.button:not(.gray) {
    background: linear-gradient(135deg, #f91942 0%, #ff5f7e 100%);
    color: white;
    border: 2px solid transparent;
}

.button:not(.gray):hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Responsive Design */
@media (max-width: 768px) {
    .booking-actions {
        flex-direction: column;
        gap: 10px;
    }

    .button {
        width: 100%;
    }

    .small-dialog-header {
        padding: 20px;
    }
}
</style>
