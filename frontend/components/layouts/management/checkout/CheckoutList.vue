<template>
    <h4>Quản lý yêu cầu trả phòng</h4>

    <!-- Hiển thị loading spinner -->
    <Loading :is-loading="isLoading" />

    <ul v-if="!isLoading">
        <li v-for="item in items" :key="item.id" :class="getItemClass(item.inventory_status)">
            <div class="list-box-listing bookings">
                <div class="list-box-listing-img">
                    <img :src="config.public.baseUrl + item.room_image" alt="" />
                </div>
                <div class="list-box-listing-content">
                    <div class="inner">
                        <h3>
                            Hợp đồng #{{ item.contract_id }} [{{ item.room_name }} - {{ item.motel_name }}]
                            <span :class="getInventoryStatusClass(item.inventory_status)">{{ item.inventory_status }}</span>
                            <span
                                v-if="item.inventory_status === 'Đã kiểm kê'"
                                :class="getUserConfirmationStatusClass(item.user_confirmation_status)"
                                >{{ getUserConfirmationStatusText(item.user_confirmation_status) }}</span
                            >
                        </h3>
                        <div v-if="item.user_rejection_reason" class="inner-booking-list">
                            <h5>Lý do từ chối:</h5>
                            <ul class="booking-list">
                                <li>{{ item.user_rejection_reason }}</li>
                            </ul>
                        </div>
                        <div class="inner-booking-list">
                            <h5>Ngày dự kiến rời phòng:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDate(item.check_out_date) }}</li>
                            </ul>
                        </div>
                        <div class="inner-booking-list">
                            <h5>Trạng thái rời phòng:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ item.has_left ? 'Đã rời' : 'Chưa rời' }}</li>
                            </ul>
                        </div>
                        <div v-if="item.deduction_amount" class="inner-booking-list">
                            <h5>Số tiền khấu trừ:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatPrice(item.deduction_amount) }}</li>
                            </ul>
                        </div>
                        <div v-if="item.final_refunded_amount" class="inner-booking-list">
                            <h5>Số tiền hoàn lại cuối cùng:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatPrice(item.final_refunded_amount) }}</li>
                            </ul>
                        </div>
                        <div v-if="item.note" class="inner-booking-list">
                            <h5>Ghi chú:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ item.note }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="buttons-to-right">
                <a
                    v-if="item.inventory_status === 'Đã kiểm kê'"
                    href="#"
                    @click.prevent="emitOpenInventoryPopup(item)"
                    class="button gray approve"
                >
                    <i class="im im-icon-Check"></i> Xem kiểm kê
                </a>
                <a
                    v-if="item.inventory_status === 'Chờ kiểm kê'"
                    href="#"
                    @click.prevent="openConfirmCancelPopup(item.id)"
                    class="button gray reject"
                >
                    <i class="sl sl-icon-close"></i> Hủy bỏ
                </a>
            </div>
        </li>
        <div v-if="!items.length" class="col-md-12 text-center">
            <p>Chưa có yêu cầu trả phòng nào.</p>
        </div>
    </ul>
</template>

<script setup>
import Swal from 'sweetalert2';
import { useRuntimeConfig } from '#app';
import { useFormatPrice } from '~/composables/useFormatPrice';
import { useFormatDate } from '~/composables/useFormatDate';

const { formatDate } = useFormatDate();
const { formatPrice } = useFormatPrice();
const config = useRuntimeConfig();

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

const emit = defineEmits(['cancelCheckout', 'openInventoryPopup']);

const getItemClass = status => {
    switch (status) {
        case 'Chờ kiểm kê':
        case 'Kiểm kê lại':
            return 'pending-booking';
        case 'Đã kiểm kê':
            return 'approved-booking';
        case 'Huỷ bỏ':
            return 'canceled-booking';
        default:
            return '';
    }
};

const getInventoryStatusClass = status => {
    let statusClass = 'booking-status';
    switch (status) {
        case 'Chờ kiểm kê':
        case 'Kiểm kê lại':
            statusClass += ' pending';
            break;
        case 'Đã kiểm kê':
            statusClass += ' approved';
            break;
        case 'Huỷ bỏ':
            statusClass += ' canceled';
            break;
    }
    return statusClass;
};

const getUserConfirmationStatusClass = status => {
    let statusClass = 'booking-status';
    switch (status) {
        case 'Chưa xác nhận':
            statusClass += ' pending user-confirmation-status';
            break;
        case 'Đồng ý':
            statusClass += ' approved';
            break;
        case 'Từ chối':
            statusClass += ' canceled user-confirmation-status';
            break;
    }
    return statusClass;
};

const getUserConfirmationStatusText = status => {
    switch (status) {
        case 'Chưa xác nhận':
            return 'Chờ xác nhận từ bạn';
        case 'Đồng ý':
            return 'Bạn đã đồng ý với kết quả';
        case 'Từ chối':
            return 'Bạn đã từ chối kết quả';
        default:
            return '';
    }
};

const openConfirmCancelPopup = async id => {
    const result = await Swal.fire({
        title: 'Xác nhận hủy yêu cầu trả phòng',
        text: 'Bạn có chắc chắn muốn hủy yêu cầu trả phòng này?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xác nhận',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#f91942',
        cancelButtonColor: '#e0e0e0',
        customClass: {
            confirmButton: 'button',
            cancelButton: 'button gray'
        }
    });

    if (result.isConfirmed) {
        emit('cancelCheckout', id);
    }
};

const emitOpenInventoryPopup = item => {
    emit('openInventoryPopup', item);
};
</script>

<style scoped>
.bookings .list-box-listing-img {
    max-width: 150px;
    max-height: none;
    border-radius: 4px;
}

.booking-status.pending.user-confirmation-status {
    background-color: #2196f3 !important;
}

.booking-status.canceled.user-confirmation-status {
    background-color: #e42929 !important;
}
</style>
