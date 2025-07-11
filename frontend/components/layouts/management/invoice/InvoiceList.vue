<template>
    <!-- Hiển thị loading spinner -->
    <Loading :is-loading="isLoading" />

    <div v-if="!isLoading" class="dashboard-list-box invoices with-icons margin-top-0">
        <ul>
            <li v-for="item in items" :key="item.id">
                <i class="list-box-icon sl sl-icon-doc"></i>
                <strong v-if="item.type === 'Hàng tháng'"
                    >Tiền {{ item.contract.room.name }} tháng {{ item.month }} năm {{ item.year }}</strong
                >
                <strong v-else>Đặt cọc {{ item.contract.room.name }} theo hợp đồng #{{ item.contract.room.id }}</strong>
                <ul>
                    <li :class="getStatusClass(item.status)">{{ item.status }}</li>
                    <li>Mã: {{ item.code }}</li>
                    <li>Tổng tiền: {{ formatCurrency(item.total_amount) }}đ</li>
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

const formatCurrency = amount => new Intl.NumberFormat('vi-VN').format(amount);

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
