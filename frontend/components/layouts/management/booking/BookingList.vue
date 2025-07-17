<template>
    <h4>Quản lý đặt phòng</h4>

    <Loading :is-loading="isLoading" />

    <ul>
        <li v-for="item in items" :key="item.id" :class="getItemClass(item.status)">
            <div class="list-box-listing bookings">
                <div class="list-box-listing-img">
                    <NuxtLink :to="`/nha-tro/${item.motel_slug}`" target="_blank">
                        <img :src="config.public.baseUrl + item.room_image" :alt="item.room_name - item.motel_name" />
                    </NuxtLink>
                </div>
                <div class="list-box-listing-content">
                    <div class="inner">
                        <h3>
                            {{ item.room_name }} - {{ item.motel_name }}
                            <span :class="getStatusClass(item.status)">{{
                                item.status === 'Chấp nhận' ? 'Đã được chấp nhận' : item.status
                            }}</span>
                        </h3>
                        <div class="inner-booking-list">
                            <h5>Ngày bắt đầu:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDate(item.start_date) }}</li>
                            </ul>
                        </div>
                        <div class="inner-booking-list">
                            <h5>Ngày kết thúc:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDate(item.end_date) }}</li>
                            </ul>
                        </div>
                        <div class="inner-booking-list">
                            <h5>Thời gian thuê:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ calculateRentalYears(item.start_date, item.end_date) }}</li>
                            </ul>
                        </div>
                        <div v-if="item.note" class="inner-booking-list">
                            <h5>Ghi chú:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ item.note }}</li>
                            </ul>
                        </div>
                        <div v-if="item.cancellation_reason && item.status === 'Huỷ bỏ'" class="inner-booking-list">
                            <h5>Lý do huỷ:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ item.cancellation_reason }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="buttons-to-right">
                <a
                    v-if="item.status === 'Chờ xác nhận'"
                    href="#"
                    @click.prevent="openConfirmRejectPopup(item.id)"
                    class="button gray reject"
                >
                    <i class="sl sl-icon-close"></i> Hủy bỏ
                </a>
            </div>
        </li>
        <div v-if="!items.length" class="col-md-12 text-center">
            <p>Chưa có đặt phòng nào.</p>
        </div>
    </ul>
</template>

<script setup>
import Swal from 'sweetalert2';
import { useFormatDate } from '~/composables/useFormatDate';

const { formatDate } = useFormatDate();
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

const calculateRentalYears = (startDate, endDate) => {
    if (!startDate || !endDate) return 'Không xác định';
    const start = new Date(startDate);
    const end = new Date(endDate);
    const diffInYears = end.getFullYear() - start.getFullYear();

    if (diffInYears < 0) return 'Ngày không hợp lệ';
    return `${diffInYears} năm`;
};

const getItemClass = status => {
    switch (status) {
        case 'Chờ xác nhận':
            return 'pending-booking';
        case 'Đã xác nhận':
        case 'Chấp nhận':
            return 'approved-booking';
        case 'Huỷ bỏ':
        case 'Từ chối':
            return 'canceled-booking';
        default:
            return '';
    }
};

const getStatusClass = status => {
    let statusClass = 'booking-status';
    if (status === 'Chờ xác nhận') {
        statusClass += ' pending';
    }
    return statusClass;
};

const openConfirmRejectPopup = async id => {
    const result = await Swal.fire({
        title: 'Xác nhận hủy đặt phòng',
        text: 'Bạn có chắc chắn muốn hủy đặt phòng này?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xác nhận',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#f91942',
        cancelButtonColor: '#e0e0e0'
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
