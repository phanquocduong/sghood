<template>
    <h4>Quản lý lịch xem nhà trọ</h4>

    <!-- Hiển thị loading spinner -->
    <Loading :is-loading="isLoading" />

    <ul v-if="!isLoading">
        <li v-for="item in items" :key="item.id" :class="getItemClass(item.status)">
            <div class="list-box-listing bookings">
                <div class="list-box-listing-img">
                    <NuxtLink :to="`/nha-tro/${item.motel_slug}`" target="_blank">
                        <img :src="config.public.baseUrl + item.motel_image" alt="" />
                    </NuxtLink>
                </div>
                <div class="list-box-listing-content">
                    <div class="inner">
                        <h3>
                            {{ item.motel_name }}
                            <span :class="getStatusClass(item.status)">{{
                                item.status === 'Hoàn thành' ? 'Đã hoàn thành' : item.status
                            }}</span>
                        </h3>
                        <div class="inner-booking-list">
                            <h5>Địa chỉ:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ item.motel_address }}</li>
                            </ul>
                        </div>
                        <div class="inner-booking-list">
                            <h5>Ngày:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDate(item.scheduled_at) }}</li>
                            </ul>
                        </div>
                        <div class="inner-booking-list">
                            <h5>Giờ:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatTime(item.scheduled_at) }}</li>
                            </ul>
                        </div>
                        <div v-if="item.message" class="inner-booking-list">
                            <h5>Lời nhắn của bạn:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ item.message }}</li>
                            </ul>
                        </div>
                        <div v-if="item.cancellation_reason && item.status === 'Huỷ bỏ'" class="inner-booking-list">
                            <h5>Lý do bị từ chối:</h5>
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
                <a
                    v-if="item.status === 'Hoàn thành'"
                    href="#"
                    @click.prevent="openPopup(item.motel_id)"
                    class="button gray approve popup-with-zoom-anim"
                >
                    <i class="im im-icon-Folder-Bookmark"></i> Đặt phòng
                </a>
            </div>
        </li>
        <div v-if="!items.length" class="col-md-12 text-center">
            <p>Chưa có lịch xem nhà trọ nào.</p>
        </div>
    </ul>
</template>

<script setup>
import Swal from 'sweetalert2';
import { useFormatDate } from '~/composables/useFormatDate';

const { formatDate, formatTime } = useFormatDate();
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

const emit = defineEmits(['rejectItem', 'openPopup']);

const getItemClass = status => {
    switch (status) {
        case 'Chờ xác nhận':
            return 'pending-booking';
        case 'Đã xác nhận':
        case 'Hoàn thành':
            return 'approved-booking';
        case 'Huỷ bỏ':
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
        title: 'Xác nhận hủy lịch xem nhà trọ',
        text: 'Bạn có chắc chắn muốn hủy lịch xem nhà trọ này?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xác nhận',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#f91942',
        cancelButtonColor: '#e0e0e0'
    });

    if (result.isConfirmed) {
        emit('rejectItem', { id });
    }
};

const openPopup = motelId => {
    emit('openPopup', motelId);
};
</script>

<style>
.bookings .list-box-listing-img {
    max-width: 150px;
    max-height: none;
    border-radius: 4px;
}

.swal2-container {
    z-index: 10000 !important;
}

.swal2-popup {
    width: 40em !important;
    border-radius: 10px !important;
    background-color: #ffffff !important;
    padding: 20px !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
}

.swal2-title {
    font-size: 2.2rem !important;
    color: #333 !important;
    font-weight: 600 !important;
}

.swal2-html-container {
    font-size: 1.6rem !important;
    color: #555 !important;
    line-height: 2.2rem !important;
}

.swal2-confirm {
    background-color: #f91942 !important;
    color: #ffffff !important;
    font-size: 1.4rem !important;
    padding: 9px 18px !important;
    border-radius: 5px !important;
    font-weight: 500 !important;
    transition: all 0.3s ease !important;
}

.swal2-confirm:hover {
    background-color: #d81438 !important;
}

.swal2-cancel {
    background-color: #e0e0e0 !important;
    color: #333 !important;
    font-size: 1.4rem !important;
    padding: 9px 18px !important;
    border-radius: 5px !important;
    border: 1px solid #ccc !important;
    font-weight: 500 !important;
    transition: all 0.3s ease !important;
}

.swal2-cancel:hover {
    background-color: #d0d0d0 !important;
}

.swal2-icon.swal2-warning {
    border-color: #f91942 !important;
    color: #f91942 !important;
}
</style>
