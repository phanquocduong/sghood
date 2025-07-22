<template>
    <h4>Quản lý yêu cầu hoàn tiền</h4>

    <!-- Hiển thị loading spinner -->
    <Loading :is-loading="isLoading" />

    <ul v-if="!isLoading">
        <li v-for="item in items" :key="item.id" :class="getItemClass(item.status)">
            <div class="list-box-listing bookings">
                <div class="list-box-listing-content">
                    <div class="inner">
                        <h3>
                            Yêu cầu hoàn tiền #{{ item.id }}
                            <span :class="getStatusClass(item.status)">{{ item.status }}</span>
                        </h3>
                        <div class="inner-booking-list">
                            <h5>Hợp đồng:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">#{{ item.contract_id }}</li>
                            </ul>
                        </div>
                        <div class="inner-booking-list">
                            <h5>Tiền cọc:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatPrice(item.deposit_amount) }}</li>
                            </ul>
                        </div>
                        <div v-if="item.deduction_amount" class="inner-booking-list">
                            <h5>Số tiền khấu trừ:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatPrice(item.deduction_amount) }}</li>
                            </ul>
                        </div>
                        <div v-if="item.final_amount" class="inner-booking-list">
                            <h5>Số tiền hoàn:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatPrice(item.final_amount) }}</li>
                            </ul>
                        </div>
                        <div v-if="item.bank_info" class="inner-booking-list">
                            <h5>Thông tin ngân hàng:</h5>
                            <br />
                            <ul class="booking-list">
                                <li class="highlighted bank-info" v-html="formatBankInfo(item.bank_info)"></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="buttons-to-right">
                <button
                    v-if="item.status === 'Chờ xử lý'"
                    class="button gray approve popup-with-zoom-anim"
                    @click="emit('open-qr-modal', item)"
                >
                    <i class="im im-icon-Bank"></i> Kiểm tra thông tin chuyển khoản
                </button>
            </div>
        </li>
        <div v-if="!items.length" class="col-md-12 text-center">
            <p>Chưa có yêu cầu hoàn tiền nào.</p>
        </div>
    </ul>
</template>

<script setup>
import { useFormatPrice } from '~/composables/useFormatPrice';

const { formatPrice } = useFormatPrice();

const props = defineProps({
    items: { type: Array, required: true },
    isLoading: { type: Boolean, required: true },
    updateLoading: { type: Boolean, default: false },
    selectedItem: { type: Object, default: null },
    banks: { type: Array, required: true }
});

const emit = defineEmits(['open-qr-modal', 'update-bank-info']);

const formatBankInfo = bankInfo => {
    if (!bankInfo || typeof bankInfo !== 'object') return 'Không có thông tin';
    const fields = [
        bankInfo.bank_name ? `Ngân hàng: ${bankInfo.bank_name}` : '',
        bankInfo.account_number ? `Số tài khoản: ${bankInfo.account_number}` : '',
        bankInfo.account_holder ? `Chủ tài khoản: ${bankInfo.account_holder}` : ''
    ].filter(Boolean);
    return fields.join('<br>');
};

const getItemClass = status => {
    switch (status) {
        case 'Chờ xử lý':
            return 'pending-booking';
        case 'Đã xử lý':
            return 'approved-booking';
        case 'Huỷ bỏ':
            return 'canceled-booking';
        default:
            return '';
    }
};

const getStatusClass = status => {
    let statusClass = 'booking-status';
    switch (status) {
        case 'Chờ xử lý':
            statusClass += ' pending';
            break;
        case 'Đã xử lý':
            statusClass += ' approved';
            break;
        case 'Huỷ bỏ':
            statusClass += ' canceled';
            break;
    }
    return statusClass;
};
</script>

<style scoped>
.highlighted.bank-info {
    border-radius: 4px !important;
    padding: 8px !important;
}
</style>
