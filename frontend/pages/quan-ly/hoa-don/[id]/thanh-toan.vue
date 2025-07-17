<template>
    <Loading :is-loading="isLoading" />
    <div v-if="!isLoading" class="payment-section">
        <div class="header">
            <div v-if="invoice?.type === 'Đặt cọc' && invoice?.status === 'Chưa trả'">
                <h2><i class="sl sl-icon-check"></i> Hợp đồng đã được ký thành công</h2>
                <p class="contract-id">Mã hợp đồng #{{ invoice?.contract.id }}</p>
            </div>
            <h2 v-else><i class="im im-icon-Billing"></i> Hoá đơn #{{ invoice?.code }}</h2>
        </div>
        <div class="payment-methods">
            <h3>Hướng dẫn thanh toán hoá đơn</h3>
            <div class="methods-container">
                <div class="method qr-method">
                    <h4>Cách 1: Quét mã QR</h4>
                    <p>Mở ứng dụng ngân hàng và quét mã QR để thanh toán.</p>
                    <div class="qr-code">
                        <img :src="qrCodeUrl" alt="QR Code" />
                        <button class="download-btn" @click="downloadQRCode" title="Tải mã QR">
                            <i class="fa fa-download"></i> Tải mã QR
                        </button>
                        <p class="status success" v-if="isPaymentProcessed"><i class="sl sl-icon-check"></i> Thanh toán thành công</p>
                        <p class="status" v-else>Trạng thái: Chờ thanh toán... <span class="spinner"></span></p>
                    </div>
                </div>
                <div class="method manual-method">
                    <h4>Cách 2: Chuyển khoản thủ công</h4>
                    <p>Chuyển khoản theo thông tin ngân hàng dưới đây.</p>
                    <div class="bank-info">
                        <img src="https://qr.sepay.vn/assets/img/banklogo/ACB.png" alt="ACB Logo" class="bank-logo" />
                        <p class="bank-name">Ngân hàng ACB Á Châu</p>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="label">Chủ tài khoản:</span>
                                <div class="value-wrapper">
                                    <span class="value">PHAN QUOC DUONG</span>
                                    <button class="copy-btn" @click="copyToClipboard('PHAN QUOC DUONG')" title="Sao chép">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="info-item">
                                <span class="label">Số tài khoản:</span>
                                <div class="value-wrapper">
                                    <span class="value">{{ useRuntimeConfig().public.sepayAccountNumber }}</span>
                                    <button
                                        class="copy-btn"
                                        @click="copyToClipboard(useRuntimeConfig().public.sepayAccountNumber)"
                                        title="Sao chép"
                                    >
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="info-item">
                                <span class="label">Số tiền:</span>
                                <div class="value-wrapper">
                                    <span class="value">{{ formatPrice(invoice?.total_amount) }}</span>
                                    <button class="copy-btn" @click="copyToClipboard(invoice?.total_amount.toString())" title="Sao chép">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="info-item">
                                <span class="label">Nội dung CK:</span>
                                <div class="value-wrapper">
                                    <span class="value">{{ invoice?.code }}</span>
                                    <button class="copy-btn" @click="copyToClipboard(invoice?.code)" title="Sao chép">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <p class="note">
                            <i class="im im-icon-Information"></i> Lưu ý: Vui lòng giữ nguyên nội dung chuyển khoản
                            <strong>{{ invoice?.code }}</strong> để hệ thống tự động xác nhận thanh toán.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';
import { useNuxtApp } from '#app';
import { useFormatPrice } from '~/composables/useFormatPrice';

definePageMeta({
    layout: 'blank'
});

const { $api } = useNuxtApp();
const route = useRoute();
const toast = useToast();
const invoice = ref(null);
const qrCodeUrl = ref(null);
const router = useRouter();
const paymentInterval = ref(null);
const isLoading = ref(false);
const isPaymentProcessed = ref(false);
const toastId = 'payment-success';
const { formatPrice } = useFormatPrice();

const copyToClipboard = async text => {
    try {
        await navigator.clipboard.writeText(text);
        toast.success('Đã sao chép vào clipboard!', { id: 'copy-success' });
    } catch (err) {
        toast.error('Không thể sao chép. Vui lòng thử lại.', { id: 'copy-error' });
        console.error('Lỗi sao chép:', err);
    }
};

const downloadQRCode = async () => {
    try {
        // Tải ảnh từ URL
        const response = await fetch(qrCodeUrl.value);
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);

        // Tạo thẻ <a> để tải file
        const link = document.createElement('a');
        link.href = url;
        link.download = `QRCode_${invoice.value.code}.png`; // Đặt tên file theo mã hóa đơn
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Giải phóng URL object
        window.URL.revokeObjectURL(url);

        toast.success('Đã tải mã QR thành công!', { id: 'download-success' });
    } catch (error) {
        toast.error('Không thể tải mã QR. Vui lòng thử lại.', { id: 'download-error' });
        console.error('Lỗi tải mã QR:', error);
    }
};

const checkPaymentStatus = async () => {
    if (isPaymentProcessed.value) {
        return;
    }

    try {
        const response = await $api(`/invoices/${invoice.value.code}/status`);
        if (response.status === 'Đã trả') {
            isPaymentProcessed.value = true;
            if (paymentInterval.value) {
                clearInterval(paymentInterval.value);
                paymentInterval.value = null;
            }

            // Hiển thị thông báo với toastId để ngăn lặp
            const message =
                response.type === 'Đặt cọc'
                    ? 'Thanh toán tiền cọc thành công! Hợp đồng đã được kích hoạt.'
                    : 'Thanh toán hoá đơn thành công!';
            toast.success(message, { id: toastId });

            if (response.type === 'Đặt cọc') {
                try {
                    await $api(`/contracts/${invoice.value.contract.id}/download-pdf`, { method: 'GET' });
                } catch (error) {
                    console.error(error);
                }
            }

            // Chuyển hướng sau một chút delay để đảm bảo toast hiển thị
            setTimeout(() => {
                router.push(response.type === 'Đặt cọc' ? '/quan-ly/hop-dong' : '/quan-ly/hoa-don');
            }, 500);
        }
    } catch (error) {
        console.error('Lỗi kiểm tra trạng thái thanh toán:', error);
        handleBackendError(error);
    }
};

const fetchInvoice = async () => {
    isLoading.value = true;
    try {
        const response = await $api(`/invoices/${route.params.id}`, { method: 'GET' });
        invoice.value = response.data;
        qrCodeUrl.value = `https://qr.sepay.vn/img?bank=${useRuntimeConfig().public.sepayBank}&acc=${
            useRuntimeConfig().public.sepayAccountNumber
        }&template=compact&amount=${invoice.value.total_amount}&des=${invoice.value.code}`;

        // Khởi tạo interval nếu chưa có và chưa xử lý thanh toán
        if (!paymentInterval.value && !isPaymentProcessed.value) {
            paymentInterval.value = setInterval(checkPaymentStatus, 2000);
        }
    } catch (error) {
        const data = error.response?._data;
        toast.error(data?.error || 'Đã có lỗi xảy ra khi lấy chi tiết hóa đơn.', { id: 'fetch-error' });
        console.error('Lỗi lấy hóa đơn:', error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchInvoice();
});

onUnmounted(() => {
    if (paymentInterval.value) {
        clearInterval(paymentInterval.value);
        paymentInterval.value = null;
    }
});
</script>

<style scoped>
.payment-section {
    max-width: 1000px;
    margin: 50px auto;
    padding: 30px;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    font-family: 'Arial', sans-serif;
}

.header {
    text-align: center;
    margin-bottom: 30px;
}

.header h2 {
    color: #28a745;
    font-size: 24px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.header h2 i {
    font-size: 28px;
}

.contract-id {
    font-size: 16px;
    color: #6c757d;
    margin-top: 8px;
}

.payment-methods {
    text-align: center;
}

.payment-methods h3 {
    font-size: 20px;
    font-weight: 600;
    color: #333;
    margin-bottom: 20px;
}

.methods-container {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    justify-content: center;
}

.method {
    flex: 1;
    min-width: 300px;
    background: #fff;
    border-radius: 8px;
    padding: 30px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: 1px solid #d9d9d9;
}

.method:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
}

.method h4 {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
}

.method p {
    color: #555;
    font-size: 14px;
    margin-bottom: 15px;
}

.qr-code img {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
    margin-bottom: 10px;
}

.qr-code .status {
    font-size: 14px;
    color: #555;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.qr-code .status.success {
    color: #28a745;
    font-weight: bold;
}

.qr-code .spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #28a745;
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.bank-info {
    text-align: center;
}

.bank-logo {
    max-height: 50px;
}

.bank-name {
    font-size: 16px;
    font-weight: 600;
    color: #333;
    margin-bottom: 20px;
}

.info-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 10px;
    margin-bottom: 15px;
    text-align: left;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-item .label {
    font-size: 14px;
    color: #6c757d;
}

.value-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-item .value {
    font-size: 14px;
    font-weight: 600;
    color: #333;
}

.copy-btn {
    background: transparent;
    border: none;
    cursor: pointer;
    color: #007bff;
    transition: color 0.3s ease;
    padding: 2px;
}

.copy-btn:hover {
    color: #0056b3;
}

.copy-btn i {
    font-size: 16px;
}

.note {
    background: #e9ecef;
    padding: 10px;
    border-radius: 4px;
    font-size: 13px;
    color: #555;
}

.note i {
    color: #007bff;
}

.download-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px;
    padding: 8px 16px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    margin: 10px auto;
    transition: background 0.3s ease;
}

.download-btn:hover {
    background: #0056b3;
}

.download-btn i {
    font-size: 16px;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Responsive styles */
@media (max-width: 992px) {
    .payment-section {
        max-width: 90%;
        margin: 30px auto;
        padding: 20px;
    }

    .header h2 {
        font-size: 22px;
    }

    .header h2 i {
        font-size: 24px;
    }

    .contract-id {
        font-size: 14px;
    }

    .payment-methods h3 {
        font-size: 18px;
    }

    .methods-container {
        gap: 15px;
    }

    .method {
        min-width: 100%;
        padding: 20px;
    }

    .qr-code img {
        max-width: 300px;
    }

    .copy-btn i {
        font-size: 15px;
    }
}

@media (max-width: 768px) {
    .payment-section {
        margin: 20px auto;
        padding: 15px;
    }

    .header h2 {
        font-size: 20px;
    }

    .header h2 i {
        font-size: 22px;
    }

    .contract-id {
        font-size: 13px;
    }

    .payment-methods h3 {
        font-size: 16px;
        margin-bottom: 15px;
    }

    .method h4 {
        font-size: 16px;
    }

    .method p {
        font-size: 13px;
    }

    .qr-code img {
        max-width: 250px;
    }

    .qr-code .status {
        font-size: 13px;
    }

    .bank-logo {
        max-height: 40px;
    }

    .bank-name {
        font-size: 14px;
        margin-bottom: 15px;
    }

    .info-item .label,
    .info-item .value {
        font-size: 13px;
    }

    .copy-btn i {
        font-size: 14px;
    }

    .note {
        font-size: 12px;
        padding: 8px;
    }

    .download-btn {
        padding: 6px 12px;
        font-size: 13px;
    }

    .download-btn i {
        font-size: 14px;
    }
}

@media (max-width: 576px) {
    .payment-section {
        margin: 15px auto;
        padding: 10px;
    }

    .header h2 {
        font-size: 18px;
    }

    .header h2 i {
        font-size: 20px;
    }

    .contract-id {
        font-size: 12px;
    }

    .payment-methods h3 {
        font-size: 14px;
        margin-bottom: 10px;
    }

    .method {
        padding: 15px;
    }

    .method h4 {
        font-size: 14px;
    }

    .method p {
        font-size: 12px;
        margin-bottom: 10px;
    }

    .qr-code img {
        max-width: 200px;
    }

    .qr-code .status {
        font-size: 12px;
    }

    .bank-logo {
        max-height: 35px;
    }

    .bank-name {
        font-size: 13px;
        margin-bottom: 10px;
    }

    .info-item .label,
    .info-item .value {
        font-size: 12px;
    }

    .copy-btn i {
        font-size: 13px;
    }

    .note {
        font-size: 11px;
        padding: 6px;
    }

    .note i {
        font-size: 12px;
    }

    .download-btn {
        padding: 5px 10px;
        font-size: 12px;
    }

    .download-btn i {
        font-size: 13px;
    }
}

@media (max-width: 480px) {
    .payment-section {
        margin: 10px auto;
        padding: 8px;
    }

    .header h2 {
        font-size: 16px;
    }

    .header h2 i {
        font-size: 18px;
    }

    .contract-id {
        font-size: 11px;
    }

    .payment-methods h3 {
        font-size: 13px;
    }

    .method {
        padding: 10px;
    }

    .method h4 {
        font-size: 13px;
    }

    .method p {
        font-size: 11px;
    }

    .qr-code img {
        max-width: 150px;
    }

    .qr-code .status {
        font-size: 11px;
    }

    .qr-code .spinner {
        width: 14px;
        height: 14px;
    }

    .bank-logo {
        max-height: 30px;
    }

    .bank-name {
        font-size: 12px;
        margin-bottom: 8px;
    }

    .info-item .label,
    .info-item .value {
        font-size: 11px;
    }

    .copy-btn i {
        font-size: 12px;
    }

    .note {
        font-size: 10px;
        padding: 5px;
    }

    .note i {
        font-size: 11px;
    }

    .download-btn {
        padding: 4px 8px;
        font-size: 11px;
    }

    .download-btn i {
        font-size: 12px;
    }
}
</style>
