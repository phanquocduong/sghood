<template>
    <h4>Đặt phòng</h4>

    <!-- Hiển thị loading spinner -->
    <div v-if="isLoading" class="loading-overlay">
        <div class="spinner"></div>
        <p>Đang tải...</p>
    </div>

    <ul v-else>
        <li v-for="booking in bookings" :key="booking.id" :class="getBookingClass(booking.status)">
            <div class="list-box-listing bookings">
                <div class="list-box-listing-img">
                    <img :src="config.public.baseUrl + booking.room_image" alt="" />
                </div>
                <div class="list-box-listing-content">
                    <div class="inner">
                        <h3>
                            {{ booking.room_name }} - {{ booking.motel_name }}
                            <span :class="getStatusClass(booking.status)">{{ booking.status }}</span>
                        </h3>
                        <div class="inner-booking-list">
                            <h5>Ngày bắt đầu:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDate(booking.start_date) }}</li>
                            </ul>
                        </div>
                        <div class="inner-booking-list">
                            <h5>Ngày kết thúc:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDate(booking.end_date) }}</li>
                            </ul>
                        </div>
                        <div v-if="booking.note" class="inner-booking-list">
                            <h5>Lời nhắn:</h5>
                            <ul class="booking-list">
                                <li>{{ booking.note }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="buttons-to-right">
                <a href="#" v-if="booking.status === 'Chờ xác nhận'" @click.prevent="rejectBooking(booking.id)" class="button gray reject">
                    <i class="sl sl-icon-close"></i> Huỷ bỏ
                </a>
            </div>
        </li>
        <div v-if="!bookings.length" class="col-md-12 text-center">
            <p>Chưa có yêu cầu đặt phòng nào.</p>
        </div>
    </ul>
</template>

<script setup>
const config = useRuntimeConfig();
const props = defineProps({
    bookings: {
        type: Array,
        required: true
    },
    isLoading: {
        type: Boolean,
        required: true
    }
});

const emit = defineEmits(['rejectBooking', 'openPopup']);

const formatDate = dateString => {
    const date = new Date(dateString);
    return date.toISOString().split('T')[0];
};

const getBookingClass = status => {
    switch (status) {
        case 'Chờ xác nhận':
            return 'pending-booking';
        case 'Chấp nhận':
            return 'approved-booking';
        case 'Từ chối':
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

const rejectBooking = id => {
    emit('rejectBooking', id);
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
    background: white; /* Nền tối hơn để che hoàn toàn */
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999; /* Đảm bảo overlay hiển thị trên cùng */
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
</style>
