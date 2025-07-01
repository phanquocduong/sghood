<template>
    <!-- Print Button -->
    <div style="display: flex; justify-content: center">
        <a href="javascript:window.print()" class="print-button">In hóa đơn</a>
        <NuxtLink
            v-if="invoice?.status === 'Chưa trả'"
            :to="`/quan-ly/hoa-don/${invoice?.id}/thanh-toan`"
            class="print-button payment-button"
            >Thanh toán</NuxtLink
        >
    </div>

    <!-- Invoice -->
    <div v-if="invoice" id="invoice" class="invoice-container">
        <!-- Header -->
        <div class="row">
            <div class="col-md-6">
                <div id="logo">
                    <img src="/images/sghood_logo1.png" alt="SGHood Logo" />
                </div>
            </div>
            <div class="col-md-6 text-right">
                <p id="details">
                    <strong>Mã hóa đơn:</strong> {{ invoice.code }} <br />
                    <strong>Thời gian tạo:</strong> {{ formatDate(invoice.created_at) }} <br />
                    <strong>Trạng thái:</strong> {{ invoice.status }} <br />
                </p>
            </div>
        </div>

        <!-- Client & Supplier -->
        <div class="row">
            <div class="col-md-12">
                <h2>Hóa đơn thanh toán</h2>
            </div>
            <div class="col-md-6">
                <strong class="margin-bottom-5">Chủ nhà</strong>
                <p>
                    SGHood <br />
                    SĐT: +84828283169 <br />
                    Email: sghoodvn@gmail.com
                </p>
            </div>
            <div class="col-md-6">
                <strong class="margin-bottom-5">Khách thuê</strong>
                <p>
                    {{ invoice.contract.user.name }} <br />
                    SĐT: {{ invoice.contract.user.phone || 'N/A' }} <br />
                    Email: {{ invoice.contract.user.email }}
                </p>
            </div>
        </div>

        <!-- Invoice Table -->
        <div class="row">
            <div class="col-md-12">
                <table class="invoice-table margin-top-20">
                    <thead>
                        <tr>
                            <th>Mô tả</th>
                            <th>Số tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                {{
                                    invoice.type === 'Đặt cọc'
                                        ? `Đặt cọc hợp đồng #${invoice.contract.id}`
                                        : `Hóa đơn tháng ${invoice.month} năm ${invoice.year}`
                                }}
                            </td>
                            <td>{{ formatCurrency(invoice.total_amount) }}</td>
                        </tr>
                        <tr v-if="invoice.type === 'Hàng tháng'">
                            <td>Phí điện</td>
                            <td>{{ formatCurrency(invoice.electricity_fee) }}</td>
                        </tr>
                        <tr v-if="invoice.type === 'Hàng tháng'">
                            <td>Phí nước</td>
                            <td>{{ formatCurrency(invoice.water_fee) }}</td>
                        </tr>
                        <tr v-if="invoice.type === 'Hàng tháng'">
                            <td>Phí đỗ xe</td>
                            <td>{{ formatCurrency(invoice.parking_fee) }}</td>
                        </tr>
                        <tr v-if="invoice.type === 'Hàng tháng'">
                            <td>Phí vệ sinh</td>
                            <td>{{ formatCurrency(invoice.junk_fee) }}</td>
                        </tr>
                        <tr v-if="invoice.type === 'Hàng tháng'">
                            <td>Phí internet</td>
                            <td>{{ formatCurrency(invoice.internet_fee) }}</td>
                        </tr>
                        <tr v-if="invoice.type === 'Hàng tháng'">
                            <td>Phí dịch vụ</td>
                            <td>{{ formatCurrency(invoice.service_fee) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-4 col-md-offset-8">
                <table id="totals">
                    <tr>
                        <th>Tổng tiền</th>
                        <th>
                            <span>{{ formatCurrency(invoice.total_amount) }}</span>
                        </th>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="row">
            <div class="col-md-12 text-center">
                <ul id="footer">
                    <li><span>www.sghood.com.vn</span></li>
                    <li>sghoodvn@gmail.com</li>
                    <li>(+84) 828 28 3169</li>
                </ul>
            </div>
        </div>
    </div>

    <div v-else class="text-center">
        <p>Đang tải hóa đơn...</p>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useToast } from 'vue-toastification';
import { useNuxtApp } from '#app';

definePageMeta({
    layout: 'blank'
});

const { $api } = useNuxtApp();
const route = useRoute();
const toast = useToast();
const invoice = ref(null);

const formatDate = date => {
    return new Date(date).toLocaleString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
};

const formatCurrency = amount => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
};

const fetchInvoice = async () => {
    try {
        const response = await $api(`/invoices/${route.params.id}`, { method: 'GET' });
        invoice.value = response.data;
    } catch (error) {
        const data = error.response?._data;
        toast.error(data?.error || 'Đã có lỗi xảy ra khi lấy chi tiết hóa đơn.');
    }
};

onMounted(() => {
    fetchInvoice();
});
</script>

<style scoped>
@import '~/public/css/invoice.css';

#logo img {
    max-height: 100px;
}

.print-button,
.print-button:hover {
    margin: 40px 10px 40px 10px;
}

.print-button.payment-button {
    background-color: #f91942;
    color: white;
    cursor: pointer;
}
</style>
