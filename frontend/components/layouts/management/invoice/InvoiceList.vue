<template>
    <!-- Hiển thị spinner khi đang loading -->
    <Loading :is-loading="isLoading" />

    <div v-if="!isLoading" class="dashboard-list-box invoices with-icons margin-top-0">
        <ul>
            <li v-for="item in items" :key="item.id">
                <!-- Icon biểu thị hóa đơn -->
                <i class="list-box-icon sl sl-icon-doc"></i>
                <!-- Tiêu đề hóa đơn -->
                <strong v-if="item.type === 'Hàng tháng'">Tiền phòng trọ tháng {{ item.month }}/{{ item.year }}</strong>
                <strong v-else>Đặt cọc hợp đồng</strong>
                <ul style="margin-top: 5px">
                    <!-- Trạng thái hóa đơn -->
                    <li :class="getStatusClass(item.status)">
                        {{ item.status
                        }}<em style="color: #ee3535" v-if="item.refunded_at"> / Đã hoàn tiền lúc {{ formatDateTime(item.refunded_at) }}</em>
                    </li>
                    <!-- Mã hóa đơn -->
                    <li>Mã: {{ item.code }}</li>
                    <!-- Tổng tiền -->
                    <li>Tổng tiền: {{ formatPrice(item.total_amount) }}</li>
                    <!-- Ngày tạo hóa đơn -->
                    <li>Ngày tạo: {{ formatDate(item.created_at) }}</li>
                </ul>
                <div class="buttons-to-right">
                    <!-- Liên kết xem chi tiết hóa đơn -->
                    <NuxtLink :to="`/quan-ly/hoa-don/${item.code}`" class="button gray">Xem hoá đơn</NuxtLink>
                    <!-- Liên kết thanh toán (hiển thị nếu hóa đơn chưa trả) -->
                    <NuxtLink v-if="item.status !== 'Đã trả'" :to="`/quan-ly/hoa-don/${item?.code}/thanh-toan`" class="button gray"
                        >Thanh toán</NuxtLink
                    >
                </div>
            </li>
            <!-- Hiển thị thông báo nếu không có hóa đơn -->
            <div v-if="!items.length" class="col-md-12 text-center">
                <p>Chưa có hoá đơn nào.</p>
            </div>
        </ul>
    </div>
</template>

<script setup>
import { useFormatPrice } from '~/composables/useFormatPrice';
import { useFormatDate } from '~/composables/useFormatDate';

// Sử dụng composable để định dạng giá và ngày
const { formatPrice } = useFormatPrice();
const { formatDate, formatDateTime } = useFormatDate();

// Định nghĩa props
const props = defineProps({
    items: {
        type: Array,
        required: true // Danh sách hóa đơn
    },
    isLoading: {
        type: Boolean,
        required: true // Trạng thái loading
    }
});

// Xác định class cho trạng thái hóa đơn
const getStatusClass = status => {
    switch (status) {
        case 'Chưa trả':
            return 'unpaid'; // Class cho trạng thái chưa trả
        case 'Đã trả':
        case 'Đã hoàn tiền':
            return 'paid'; // Class cho trạng thái đã trả hoặc đã hoàn tiền
        default:
            return '';
    }
};
</script>

<style scoped>
/* Thiết kế responsive cho danh sách hóa đơn */
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
