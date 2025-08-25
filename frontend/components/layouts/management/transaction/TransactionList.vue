<template>
    <!-- Hiển thị loading spinner khi đang tải dữ liệu -->
    <Loading :is-loading="isLoading" />

    <!-- Danh sách giao dịch -->
    <div v-if="!isLoading" class="dashboard-list-box invoices with-icons margin-top-0">
        <ul>
            <!-- Lặp qua từng giao dịch trong danh sách -->
            <li v-for="item in items" :key="item.id">
                <!-- Icon ví tiền -->
                <i class="list-box-icon sl sl-icon-wallet"></i>
                <!-- Hiển thị số tiền giao dịch với định dạng và màu sắc theo loại -->
                <strong :class="getTypeClass(item.transfer_type)"
                    >{{ item.transfer_type === 'in' ? '-' : '+' }}{{ formatPrice(item.transfer_amount) }}</strong
                >
                <!-- Thông tin chi tiết giao dịch -->
                <ul style="margin-top: 5px">
                    <li>Thời gian: {{ formatDateTime(item.transaction_date) }}</li>
                    <li>Hoá đơn: {{ item.invoice_code }}</li>
                    <li>Mã tham chiếu: {{ item.reference_code }}</li>
                </ul>
            </li>
            <!-- Hiển thị thông báo khi không có giao dịch -->
            <div v-if="!items.length" class="col-md-12 text-center">
                <p>Chưa có giao dịch nào.</p>
            </div>
        </ul>
    </div>
</template>

<script setup>
import { useFormatPrice } from '~/composables/useFormatPrice';
import { useFormatDate } from '~/composables/useFormatDate';

// Sử dụng composable để định dạng giá và ngày tháng
const { formatPrice } = useFormatPrice();
const { formatDateTime } = useFormatDate();

// Nhận dữ liệu từ component cha
const props = defineProps({
    items: {
        type: Array,
        required: true
    },
    isLoading: {
        type: Boolean,
        required: true
    }
});

// Hàm xác định class CSS dựa trên loại giao dịch
const getTypeClass = type => {
    switch (type) {
        case 'in':
            return 'paid_out'; // Giao dịch chi
        case 'out':
            return 'paid_in'; // Giao dịch thu
        default:
            return ''; // Không có loại
    }
};
</script>

<style scoped>
/* Style cho giao dịch chi */
.paid_out {
    color: #d9534f !important;
}

/* Style cho giao dịch thu */
.paid_in {
    color: #4bbf73 !important;
}

/* Responsive style cho màn hình nhỏ */
@media (max-width: 576px) {
    .dashboard-list-box.invoices ul ul li:after {
        height: 0;
        width: 0;
        margin: 0;
    }

    .dashboard-list-box.invoices > ul > li > ul > li + li {
        margin-top: 5px;
    }
}
</style>
