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
                        <p class="status">Trạng thái: Chờ thanh toán... <span class="spinner"></span></p>
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
                                <span class="value">PHAN QUOC DUONG</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Số tài khoản:</span>
                                <span class="value">{{ useRuntimeConfig().public.sepayAccountNumber }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Số tiền:</span>
                                <span class="value">{{ formatCurrency(invoice?.total_amount) }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Nội dung CK:</span>
                                <span class="value">{{ invoice?.code }}</span>
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
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';
import { useNuxtApp } from '#app';

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

const formatCurrency = amount => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
};

const checkPaymentStatus = async () => {
    try {
        const response = await $api(`/invoices/${invoice.value.code}/status`);
        if (response.status === 'Đã trả') {
            clearInterval(paymentInterval.value);
            if (response.type === 'Đặt cọc') {
                toast.success('Thanh toán tiền cọc thành công! Hợp đồng đã được kích hoạt.');
                router.push('/quan-ly/hop-dong');
            } else {
                toast.success('Thanh toán hoá đơn thành công!');
                router.push('/quan-ly/hoa-don');
            }
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
        paymentInterval.value = setInterval(checkPaymentStatus, 2000);
    } catch (error) {
        const data = error.response?._data;
        toast.error(data?.error || 'Đã có lỗi xảy ra khi lấy chi tiết hóa đơn.');
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchInvoice();
});

onUnmounted(() => {
    if (paymentInterval.value) clearInterval(paymentInterval.value);
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
    max-width: 400px;
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

.info-item .value {
    font-size: 14px;
    font-weight: 600;
    color: #333;
}

.note {
    background: #e9ecef;
    padding: 10px;
    border-radius: 4px;
    font-size: 13px;
    color: #555;
    gap: 8px;
}

.note i {
    color: #007bff;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

@media (max-width: 768px) {
    .methods-container {
        flex-direction: column;
        gap: 15px;
    }

    .method {
        min-width: 100%;
    }

    .payment-section {
        padding: 20px;
    }
}
</style>
