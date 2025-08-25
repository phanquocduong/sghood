<template>
    <!-- Modal hiển thị thông tin chuyển khoản -->
    <div id="otp-dialog" class="zoom-anim-dialog mfp-hide">
        <div class="small-dialog-header">
            <h3>Kiểm tra thông tin chuyển khoản</h3>
            <p class="booking-subtitle">Có thể quét mã QR để kiểm tra thông tin chuyển khoản</p>
        </div>
        <div class="message-reply margin-top-0">
            <div class="modal-content">
                <!-- Hiển thị thông tin ngân hàng -->
                <p v-if="checkout?.bank_info" class="mt-3">
                    <strong>Thông tin ngân hàng:</strong>
                    <br />
                    <span v-html="formatBankInfo(checkout?.bank_info)"></span>
                </p>
                <!-- Hiển thị mã QR chuyển khoản -->
                <div v-if="checkout?.bank_info" class="qr-code">
                    <h5>Test mã QR chuyển khoản</h5>
                    <img
                        :src="`https://qr.sepay.vn/img?bank=${checkout.bank_info.bank_name}&acc=${checkout.bank_info.account_number}&amount=&des=&template=qronly`"
                        alt="Mã QR"
                        style="max-width: 400px; margin: 0 auto; display: block"
                    />
                </div>
                <!-- Hiển thị ảnh biên lai (nếu có) -->
                <div v-if="checkout?.receipt_path" class="receipt">
                    <h5>Ảnh biên lai</h5>
                    <img
                        :src="useRuntimeConfig().public.baseUrl + '/storage/' + checkout.receipt_path"
                        alt="Biên lai"
                        style="max-width: 400px; margin: 0 auto; display: block"
                    />
                </div>
                <p class="mt-3"><em>Nếu thông tin không đúng, vui lòng chỉnh sửa:</em></p>
                <!-- Nút mở modal chỉnh sửa thông tin chuyển khoản -->
                <button class="button gray" @click="$emit('open-edit-bank-modal')">Chỉnh sửa thông tin chuyển khoản</button>
            </div>
            <div class="booking-actions">
                <!-- Nút đóng modal -->
                <button @click="$emit('close')" class="button gray" type="button"><i class="fa fa-times"></i> Đóng</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useRuntimeConfig } from '#app';

// Định nghĩa props
defineProps({
    checkout: { type: Object, required: true } // Thông tin yêu cầu trả phòng
});

// Định nghĩa các sự kiện emit
defineEmits(['close', 'open-edit-bank-modal']);

// Định dạng thông tin ngân hàng
const formatBankInfo = bankInfo => {
    if (!bankInfo || typeof bankInfo !== 'object') return 'Không có thông tin';
    const fields = [
        bankInfo.bank_name ? `Ngân hàng: ${bankInfo.bank_name}` : '',
        bankInfo.account_number ? `Số tài khoản: ${bankInfo.account_number}` : '',
        bankInfo.account_holder ? `Chủ tài khoản: ${bankInfo.account_holder}` : ''
    ].filter(Boolean);
    return fields.join('<br>'); // Nối các trường thông tin bằng thẻ <br>
};
</script>

<style scoped>
@import '~/public/css/bank-info-modal.css';
</style>
