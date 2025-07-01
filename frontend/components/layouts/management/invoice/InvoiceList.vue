<template>
    <h4>Quản lý hoá đơn</h4>

    <!-- Hiển thị loading spinner -->
    <Loading :is-loading="isLoading" />

    <ul v-if="!isLoading">
        <li v-for="item in items" :key="item.id" :class="getItemClass(item.status, item.type)">
            <div class="list-box-listing bookings">
                <div class="list-box-listing-content">
                    <div class="inner">
                        <h3>
                            Hoá đơn tháng {{ item.month }} năm {{ item.year }}
                            <span :class="getStatusClass(item.status)">{{ item.status }}</span>
                            <span class="item-type">[{{ item.type }}]</span>
                        </h3>
                        <div class="inner-booking-list">
                            <h5>Mã hoá đơn:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ item.code }}</li>
                            </ul>
                        </div>
                        <div class="inner-booking-list">
                            <h5>Tổng tiền:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatCurrency(item.total_amount) }}đ</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="buttons-to-right">
                <NuxtLink :to="`/quan-ly/hoa-don/${item.id}`" class="button gray approve">
                    <i class="im im-icon-Preview"></i> Xem chi tiết
                </NuxtLink>
                <NuxtLink v-if="item.status !== 'Đã trả'" to="/" class="button gray approve">
                    <i class="im im-icon-Paypal"></i> Thanh toán
                </NuxtLink>
            </div>
        </li>
        <div v-if="!items.length" class="col-md-12 text-center">
            <p>Chưa có hoá đơn nào.</p>
        </div>
    </ul>
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

const getItemClass = status => {
    switch (status) {
        case 'Chưa trả':
            return 'pending-booking';
        case 'Đã trả':
            return 'approved-booking';
        case 'Đã hoàn tiền':
            return 'canceled-booking';
        default:
            return '';
    }
};

const getStatusClass = status => {
    let statusClass = 'booking-status';
    if (status === 'Chưa trả') {
        statusClass += ' pending';
    }
    return statusClass;
};
</script>

<style scoped>
.item-type {
    font-size: 12px;
    color: #888;
    margin-left: 10px;
}
</style>
