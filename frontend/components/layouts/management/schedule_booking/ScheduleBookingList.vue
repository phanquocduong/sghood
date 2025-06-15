<template>
    <h4>Quản lý lịch xem phòng/yêu cầu đặt phòng</h4>

    <!-- Hiển thị loading spinner -->
    <div v-if="isLoading" class="loading-overlay">
        <div class="spinner"></div>
        <p>Đang tải...</p>
    </div>

    <ul v-else>
        <li v-for="item in items" :key="item.id" :class="getItemClass(item.status, item.type)">
            <div class="list-box-listing bookings">
                <div class="list-box-listing-img">
                    <img :src="config.public.baseUrl + item.room_image" alt="" />
                </div>
                <div class="list-box-listing-content">
                    <div class="inner">
                        <h3>
                            {{ item.room_name }} - {{ item.motel_name }}
                            <span :class="getStatusClass(item.status)">{{ item.status }}</span>
                            <span class="item-type">[{{ item.type === 'schedule' ? 'Lịch xem phòng' : 'Đặt phòng' }}]</span>
                        </h3>
                        <div v-if="item.type === 'schedule'" class="inner-booking-list">
                            <h5>Ngày:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDate(item.scheduled_at) }}</li>
                            </ul>
                        </div>
                        <div v-if="item.type === 'schedule'" class="inner-booking-list">
                            <h5>Thời gian:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatTime(item.scheduled_at) }}</li>
                            </ul>
                        </div>
                        <div v-if="item.type === 'booking'" class="inner-booking-list">
                            <h5>Ngày bắt đầu:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDate(item.start_date) }}</li>
                            </ul>
                        </div>
                        <div v-if="item.type === 'booking'" class="inner-booking-list">
                            <h5>Ngày kết thúc:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDate(item.end_date) }}</li>
                            </ul>
                        </div>
                        <div v-if="item.message || item.note" class="inner-booking-list">
                            <h5>Lời nhắn/ghi chú:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ item.message || item.note }}</li>
                            </ul>
                        </div>
                        <div v-if="item.cancellation_reason" class="inner-booking-list">
                            <h5>Lý do hủy:</h5>
                            <ul class="booking-list">
                                <li>{{ item.cancellation_reason }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="buttons-to-right">
                <a
                    v-if="item.status === 'Chờ xác nhận'"
                    href="#"
                    @click.prevent="openConfirmRejectPopup(item.id, item.type)"
                    class="button gray reject"
                >
                    <i class="sl sl-icon-close"></i> Hủy bỏ
                </a>
                <a
                    v-if="item.type === 'schedule' && item.status === 'Hoàn thành' && !item.has_booked"
                    href="#"
                    @click.prevent="openPopup(item.room_id)"
                    class="button gray approve popup-with-zoom-anim"
                >
                    <i class="im im-icon-Folder-Bookmark"></i> Đặt phòng
                </a>
            </div>
        </li>
        <div v-if="!items.length" class="col-md-12 text-center">
            <p>Chưa có lịch xem phòng hoặc đặt phòng nào.</p>
        </div>
    </ul>
</template>

<script setup>
import Swal from 'sweetalert2';

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

const formatDate = dateString => {
    const date = new Date(dateString);
    return date.toISOString().split('T')[0];
};

const formatTime = dateString => {
    const date = new Date(dateString);
    return date.toISOString().split('T')[1].split('.')[0];
};

const getItemClass = (status, type) => {
    switch (status) {
        case 'Chờ xác nhận':
            return 'pending-booking';
        case 'Đã xác nhận':
        case 'Hoàn thành':
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

const openConfirmRejectPopup = async (id, type) => {
    const result = await Swal.fire({
        title: `Xác nhận hủy ${type === 'schedule' ? 'lịch xem phòng' : 'đặt phòng'}`,
        text: `Bạn có chắc chắn muốn hủy ${type === 'schedule' ? 'lịch xem phòng' : 'đặt phòng'} này?`,
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
        emit('rejectItem', { id, type });
    }
};

const openPopup = roomId => {
    emit('openPopup', roomId);
};
</script>

<style scoped>
.bookings .list-box-listing-img {
    max-width: 150px;
    max-height: none;
    border-radius: 4px;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #f91942;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

p {
    color: #333;
    margin-top: 10px;
    font-size: 16px;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

.item-type {
    font-size: 12px;
    color: #888;
    margin-left: 10px;
}
</style>
