<template>
    <h4>Quản lý yêu cầu trả phòng</h4>

    <!-- Hiển thị loading spinner -->
    <Loading :is-loading="isLoading" />

    <ul v-if="!isLoading">
        <li v-for="item in items" :key="item.id" :class="getItemClass(item.status)">
            <div class="list-box-listing bookings">
                <div class="list-box-listing-img">
                    <img :src="config.public.baseUrl + item.room_image" alt="" />
                </div>
                <div class="list-box-listing-content">
                    <div class="inner">
                        <h3>
                            Hợp đồng #{{ item.contract_id }} [{{ item.room_name }} - {{ item.motel_name }}]
                            <span :class="getStatusClass(item.status)">{{ item.status }}</span>
                        </h3>
                        <div class="inner-booking-list">
                            <h5>Ngày trả phòng:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDate(item.check_out_date) }}</li>
                            </ul>
                        </div>
                        <div class="inner-booking-list">
                            <h5>Trạng thái hoàn tiền cọc:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ item.deposit_refunded ? 'Đã hoàn' : 'Chưa hoàn' }}</li>
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
                <a v-if="item.status === 'Đã kiểm kê'" href="#" class="button gray approve">
                    <i class="im im-icon-Check"></i> Chi tiết kiểm kê
                </a>
                <a
                    v-if="item.status === 'Chờ kiểm kê'"
                    href="#"
                    @click.prevent="openConfirmRejectPopup(item.id)"
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

const emit = defineEmits(['rejectItem']);

const getItemClass = status => {
    switch (status) {
        case 'Chờ kiểm kê':
            return 'pending-booking';
        case 'Đã kiểm kê':
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
        case 'Chờ kiểm kê':
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

const openConfirmRejectPopup = async id => {
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
        emit('rejectItem', id);
    }
};
</script>

<style>
.bookings .list-box-listing-img {
    max-width: 150px;
    max-height: none;
    border-radius: 4px;
}
</style>
