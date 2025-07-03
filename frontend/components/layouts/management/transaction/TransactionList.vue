<template>
    <!-- Hiển thị loading spinner -->
    <Loading :is-loading="isLoading" />

    <div v-if="!isLoading" class="dashboard-list-box invoices with-icons margin-top-0">
        <ul>
            <li v-for="item in items" :key="item.id">
                <i class="list-box-icon sl sl-icon-wallet"></i>
                <strong :class="getTypeClass(item.transfer_type)"
                    >{{ item.transfer_type === 'in' ? '-' : '+' }}{{ formatCurrency(item.transfer_amount) }}đ</strong
                >
                <ul>
                    <li>Thời gian: {{ formatDate(item.transaction_date) }}</li>
                    <li>Hoá đơn: {{ item.invoice_code }}</li>
                    <li>Mã tham chiếu: {{ item.reference_code }}</li>
                </ul>
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

const getTypeClass = type => {
    switch (type) {
        case 'in':
            return 'paid_out';
        case 'out':
            return 'paid_in';
        default:
            return '';
    }
};
</script>

<style scoped>
.paid_out {
    color: #d9534f !important;
}

.paid_in {
    color: #4bbf73 !important;
}
</style>
