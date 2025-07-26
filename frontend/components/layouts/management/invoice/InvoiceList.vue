<template>
    <!-- Hiển thị loading spinner -->
    <Loading :is-loading="isLoading" />

    <div v-if="!isLoading" class="dashboard-list-box invoices with-icons margin-top-0">
        <ul>
            <li v-for="item in items" :key="item.id">
                <i class="list-box-icon sl sl-icon-doc"></i>
                <strong v-if="item.type === 'Hàng tháng'">Tiền phòng trọ tháng {{ item.month }}/{{ item.year }}</strong>
                <strong v-else>Đặt cọc hợp đồng</strong>
                <ul>
                    <li :class="getStatusClass(item.status)">
                        {{ item.status
                        }}<em style="color: #ee3535" v-if="item.refunded_at"> / Đã hoàn tiền lúc {{ formatDateTime(item.refunded_at) }}</em>
                    </li>
                    <li>Mã: {{ item.code }}</li>
                    <li>Tổng tiền: {{ formatPrice(item.total_amount) }}</li>
                    <li>Ngày tạo: {{ formatDate(item.created_at) }}</li>
                </ul>
                <div class="buttons-to-right">
                    <NuxtLink :to="`/quan-ly/hoa-don/${item.code}`" class="button gray">Xem hoá đơn</NuxtLink>
                    <NuxtLink v-if="item.status !== 'Đã trả'" :to="`/quan-ly/hoa-don/${item?.code}/thanh-toan`" class="button gray"
                        >Thanh toán</NuxtLink
                    >
                </div>
            </li>
        </ul>
    </div>
</template>

<script setup>
import { useFormatPrice } from '~/composables/useFormatPrice';
import { useFormatDate } from '~/composables/useFormatDate';

const { formatPrice } = useFormatPrice();
const { formatDate, formatDateTime } = useFormatDate();

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

const getStatusClass = status => {
    switch (status) {
        case 'Chưa trả':
            return 'unpaid';
        case 'Đã trả':
        case 'Đã hoàn tiền':
            return 'paid';
        default:
            return '';
    }
};
</script>

<style scoped></style>
