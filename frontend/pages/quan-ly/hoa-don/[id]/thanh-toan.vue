<template>
    <!-- Hiển thị spinner khi đang tải dữ liệu -->
    <Loading :is-loading="isLoading" />
    <div v-if="!isLoading" class="payment-section">
        <!-- Phần tiêu đề -->
        <div class="header">
            <!-- Tiêu đề cho hóa đơn đặt cọc -->
            <div v-if="invoice?.type === 'Đặt cọc' && invoice?.status === 'Chưa trả'">
                <h2><i class="sl sl-icon-check"></i> Hợp đồng đã được ký thành công</h2>
                <p class="contract-id">Mã hợp đồng #{{ invoice?.contract.id }}</p>
            </div>
            <!-- Tiêu đề cho hóa đơn thông thường -->
            <h2 v-else><i class="im im-icon-Billing"></i> Hoá đơn #{{ invoice?.code }}</h2>
        </div>
        <!-- Phần phương thức thanh toán -->
        <div class="payment-methods">
            <h3>Hướng dẫn thanh toán hoá đơn</h3>
            <div class="methods-container">
                <!-- Phương thức quét mã QR -->
                <div class="method qr-method">
                    <h4>Cách 1: Quét mã QR</h4>
                    <p>Mở ứng dụng ngân hàng và quét mã QR để thanh toán.</p>
                    <div class="qr-code">
                        <!-- Hiển thị mã QR -->
                        <img :src="qrCodeUrl" alt="QR Code" />
                        <!-- Nút tải mã QR -->
                        <button class="download-btn" @click="downloadQRCode" title="Tải mã QR">
                            <i class="fa fa-download"></i> Tải mã QR
                        </button>
                        <!-- Trạng thái thanh toán thành công -->
                        <p class="status success" v-if="isPaymentProcessed"><i class="sl sl-icon-check"></i> Thanh toán thành công</p>
                        <!-- Trạng thái chờ thanh toán -->
                        <p class="status" v-else>Trạng thái: Chờ thanh toán... <span class="spinner"></span></p>
                    </div>
                </div>
                <!-- Phương thức chuyển khoản thủ công -->
                <div class="method manual-method">
                    <h4>Cách 2: Chuyển khoản thủ công</h4>
                    <p>Chuyển khoản theo thông tin ngân hàng dưới đây.</p>
                    <div class="bank-info">
                        <!-- Logo ngân hàng -->
                        <img src="https://qr.sepay.vn/assets/img/banklogo/ACB.png" alt="ACB Logo" class="bank-logo" />
                        <!-- Tên ngân hàng -->
                        <p class="bank-name">Ngân hàng ACB Á Châu</p>
                        <div class="info-grid">
                            <!-- Thông tin chủ tài khoản -->
                            <div class="info-item">
                                <span class="label">Chủ tài khoản:</span>
                                <div class="value-wrapper">
                                    <span class="value">PHAN QUOC DUONG</span>
                                    <!-- Nút sao chép chủ tài khoản -->
                                    <button class="copy-btn" @click="copyToClipboard('PHAN QUOC DUONG')" title="Sao chép">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- Thông tin số tài khoản -->
                            <div class="info-item">
                                <span class="label">Số tài khoản:</span>
                                <div class="value-wrapper">
                                    <span class="value">{{ useRuntimeConfig().public.sepayAccountNumber }}</span>
                                    <!-- Nút sao chép số tài khoản -->
                                    <button
                                        class="copy-btn"
                                        @click="copyToClipboard(useRuntimeConfig().public.sepayAccountNumber)"
                                        title="Sao chép"
                                    >
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- Thông tin số tiền -->
                            <div class="info-item">
                                <span class="label">Số tiền:</span>
                                <div class="value-wrapper">
                                    <span class="value">{{ formatPrice(invoice?.total_amount) }}</span>
                                    <!-- Nút sao chép số tiền -->
                                    <button class="copy-btn" @click="copyToClipboard(invoice?.total_amount.toString())" title="Sao chép">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- Thông tin nội dung chuyển khoản -->
                            <div class="info-item">
                                <span class="label">Nội dung CK:</span>
                                <div class="value-wrapper">
                                    <span class="value">{{ invoice?.code }}</span>
                                    <!-- Nút sao chép nội dung chuyển khoản -->
                                    <button class="copy-btn" @click="copyToClipboard(invoice?.code)" title="Sao chép">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <!-- Ghi chú về nội dung chuyển khoản -->
                        <p class="note">
                            <i class="im im-icon-Information"></i> Lưu ý: Vui lòng giữ nguyên nội dung chuyển khoản
                            <strong>{{ invoice?.code }}</strong> để hệ thống tự động xác nhận thanh toán.
                        </p>
                    </div>
                </div>
                <!-- Phương thức thanh toán tiền mặt -->
                <div class="method cash-method">
                    <h4><i class="fa fa-money-bill"></i> Cách 3: Thanh toán tiền mặt</h4>
                    <p>Vui lòng mang hóa đơn đến văn phòng SGHood để nộp tiền mặt.</p>
                    <div class="cash-info">
                        <!-- Địa chỉ văn phòng -->
                        <p><strong>Địa chỉ:</strong> {{ config.office_address }}</p>
                        <!-- Giờ làm việc -->
                        <p><strong>Giờ làm việc:</strong> {{ config.working_time }}</p>
                        <!-- Số tiền cần thanh toán -->
                        <p><strong>Số tiền:</strong> {{ formatPrice(invoice?.total_amount) }}</p>
                        <!-- Mã hóa đơn -->
                        <p><strong>Mã hóa đơn:</strong> {{ invoice?.code }}</p>
                        <!-- Ghi chú về mã hóa đơn -->
                        <p class="note">
                            <i class="im im-icon-Information"></i> Lưu ý: Mang theo mã hóa đơn <strong>{{ invoice?.code }}</strong> để xác
                            nhận thanh toán.
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
import { useAppToast } from '~/composables/useToast';
import { useNuxtApp } from '#app';
import { useFormatPrice } from '~/composables/useFormatPrice';

// Cấu hình metadata cho trang, sử dụng layout 'blank'
definePageMeta({
    layout: 'blank'
});

const { $api } = useNuxtApp(); // Lấy instance API từ Nuxt
const config = useState('configs'); // Lấy cấu hình từ state
const route = useRoute(); // Lấy thông tin route hiện tại
const toast = useAppToast(); // Sử dụng composable để hiển thị thông báo
const invoice = ref(null); // Biến lưu thông tin hóa đơn
const qrCodeUrl = ref(null); // URL của mã QR thanh toán
const router = useRouter(); // Sử dụng router để điều hướng
const paymentInterval = ref(null); // Interval để kiểm tra trạng thái thanh toán
const isLoading = ref(false); // Trạng thái loading khi thực hiện các thao tác API
const isPaymentProcessed = ref(false); // Trạng thái thanh toán đã được xử lý
const toastId = 'payment-success'; // ID cho thông báo thanh toán thành công
const { formatPrice } = useFormatPrice(); // Sử dụng composable để định dạng giá

// Hàm sao chép nội dung vào clipboard
const copyToClipboard = async text => {
    try {
        await navigator.clipboard.writeText(text); // Sao chép nội dung
        toast.success('Đã sao chép vào clipboard!', { id: 'copy-success' }); // Hiển thị thông báo thành công
    } catch (err) {
        toast.error('Không thể sao chép. Vui lòng thử lại.', { id: 'copy-error' }); // Hiển thị thông báo lỗi
        console.error('Lỗi sao chép:', err);
    }
};

// Hàm tải mã QR về máy
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
    } catch (error) {
        console.error('Lỗi tải mã QR:', error); // Ghi log lỗi
    }
};

// Hàm kiểm tra trạng thái thanh toán
const checkPaymentStatus = async () => {
    if (isPaymentProcessed.value) {
        return; // Thoát nếu thanh toán đã được xử lý
    }

    try {
        // Gửi yêu cầu GET để kiểm tra trạng thái thanh toán
        const response = await $api(`/invoices/${invoice.value.code}/status`);
        if (response.status === 'Đã trả') {
            isPaymentProcessed.value = true; // Cập nhật trạng thái thanh toán
            if (paymentInterval.value) {
                clearInterval(paymentInterval.value); // Xóa interval
                paymentInterval.value = null;
            }

            // Hiển thị thông báo với toastId để ngăn lặp
            const message =
                response.type === 'Đặt cọc'
                    ? 'Thanh toán tiền cọc thành công! Hợp đồng đã được kích hoạt.'
                    : 'Thanh toán hoá đơn thành công!';
            toast.success(message, { id: toastId }); // Hiển thị thông báo thành công

            if (response.type === 'Đặt cọc') {
                try {
                    // Tải PDF hợp đồng nếu là hóa đơn đặt cọc
                    await $api(`/contracts/${invoice.value.contract.id}/download-pdf`, { method: 'GET' });
                } catch (error) {
                    console.error(error); // Ghi log lỗi
                }
            }

            // Chuyển hướng sau một chút delay để đảm bảo toast hiển thị
            setTimeout(() => {
                router.push(response.type === 'Đặt cọc' ? '/quan-ly/hop-dong' : '/quan-ly/hoa-don'); // Điều hướng về trang phù hợp
            }, 500);
        }
    } catch (error) {
        console.error('Lỗi kiểm tra trạng thái thanh toán:', error); // Ghi log lỗi
        handleBackendError(error); // Xử lý lỗi backend
    }
};

// Hàm lấy chi tiết hóa đơn từ server
const fetchInvoice = async () => {
    isLoading.value = true; // Bật trạng thái loading
    try {
        // Gửi yêu cầu GET để lấy chi tiết hóa đơn
        const response = await $api(`/invoices/${route.params.id}`, { method: 'GET' });
        invoice.value = response.data; // Cập nhật thông tin hóa đơn
        // Tạo URL mã QR thanh toán
        qrCodeUrl.value = `https://qr.sepay.vn/img?bank=${useRuntimeConfig().public.sepayBank}&acc=${
            useRuntimeConfig().public.sepayAccountNumber
        }&template=compact&amount=${invoice.value.total_amount}&des=${invoice.value.code}`;

        // Khởi tạo interval để kiểm tra trạng thái thanh toán nếu chưa xử lý
        if (!paymentInterval.value && !isPaymentProcessed.value) {
            paymentInterval.value = setInterval(checkPaymentStatus, 2000); // Kiểm tra mỗi 2 giây
        }
    } catch (error) {
        const data = error.response?._data;
        toast.error(data?.error || 'Đã có lỗi xảy ra khi lấy chi tiết hóa đơn.', { id: 'fetch-error' }); // Hiển thị thông báo lỗi
        console.error('Lỗi lấy hóa đơn:', error); // Ghi log lỗi
    } finally {
        isLoading.value = false; // Tắt trạng thái loading
    }
};

// Tải chi tiết hóa đơn khi component được mount
onMounted(() => {
    fetchInvoice();
});

// Xóa interval khi component bị hủy
onUnmounted(() => {
    if (paymentInterval.value) {
        clearInterval(paymentInterval.value);
        paymentInterval.value = null;
    }
});
</script>

<style scoped>
@import '~/public/css/payment.css';
</style>
