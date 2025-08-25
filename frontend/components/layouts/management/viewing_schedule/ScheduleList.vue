<template>
    <!-- Tiêu đề danh sách lịch xem -->
    <h4>Quản lý lịch xem nhà trọ</h4>

    <!-- Hiển thị loading khi đang tải dữ liệu -->
    <Loading :is-loading="isLoading" />

    <!-- Danh sách lịch xem -->
    <ul>
        <li v-for="item in schedules" :key="item.id" :class="getItemClass(item.status)">
            <div class="list-box-listing bookings">
                <!-- Hình ảnh nhà trọ -->
                <div class="list-box-listing-img">
                    <NuxtLink :to="`/danh-sach-nha-tro/${item.motel_slug}`" target="_blank" style="height: 150px">
                        <img :src="config.public.baseUrl + item.motel_image" :alt="item.motel_name" />
                    </NuxtLink>
                </div>
                <!-- Nội dung lịch xem -->
                <div class="list-box-listing-content">
                    <div class="inner">
                        <h3>
                            {{ item.motel_name }}
                            <!-- Tên nhà trọ -->
                            <span :class="getStatusClass(item.status)">{{
                                item.status === 'Hoàn thành' ? 'Đã hoàn thành' : item.status === 'Từ chối' ? 'Bị từ chối' : item.status
                            }}</span>
                            <!-- Trạng thái lịch -->
                        </h3>
                        <!-- Lý do từ chối (nếu có) -->
                        <div v-if="item.rejection_reason && item.status === 'Từ chối'" class="inner-booking-list">
                            <h5>Lý do SGHood từ chối:</h5>
                            <ul class="booking-list">
                                <li>{{ item.rejection_reason }}</li>
                            </ul>
                        </div>
                        <!-- Địa chỉ nhà trọ -->
                        <div class="inner-booking-list">
                            <h5>Địa chỉ:</h5>
                            <ul class="booking-list">
                                <li>{{ item.motel_address }}</li>
                            </ul>
                        </div>
                        <!-- Thời gian xem -->
                        <div class="inner-booking-list">
                            <h5>Thời gian:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDateTime(item.scheduled_at) }}</li>
                            </ul>
                        </div>
                        <!-- Lời nhắn từ người dùng -->
                        <div v-if="item.message" class="inner-booking-list">
                            <h5>Lời nhắn từ bạn:</h5>
                            <ul class="booking-list">
                                <li>{{ item.message }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Các nút hành động -->
            <div class="buttons-to-right">
                <!-- Nút chỉnh sửa lịch (hiển thị khi trạng thái là "Chờ xác nhận") -->
                <a v-if="item.status === 'Chờ xác nhận'" href="#" @click.prevent="openEditSchedulePopup(item)" class="button gray edit">
                    <i class="sl sl-icon-pencil"></i> Chỉnh sửa
                </a>
                <!-- Nút hủy lịch (hiển thị khi trạng thái là "Chờ xác nhận") -->
                <a
                    v-if="item.status === 'Chờ xác nhận'"
                    href="#"
                    @click.prevent="openConfirmRejectPopup(item.id)"
                    class="button gray reject"
                >
                    <i class="sl sl-icon-close"></i> Hủy bỏ
                </a>
                <!-- Nút đặt phòng (hiển thị khi trạng thái là "Hoàn thành") -->
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
        <!-- Thông báo khi không có lịch xem -->
        <div v-if="!schedules.length" class="col-md-12 text-center">
            <p>Chưa có lịch xem nhà trọ nào.</p>
        </div>
    </ul>
</template>

<script setup>
import Swal from 'sweetalert2';
import { useFormatDate } from '~/composables/useFormatDate';

const { formatDateTime } = useFormatDate(); // Hàm định dạng thời gian
const config = useRuntimeConfig(); // Lấy cấu hình runtime
const props = defineProps({
    schedules: { type: Array, required: true }, // Danh sách lịch xem
    isLoading: { type: Boolean, required: true } // Trạng thái loading
});

const emit = defineEmits(['cancelSchedule', 'openPopup', 'editSchedule']); // Các sự kiện emit

// Các class tương ứng với trạng thái lịch
const statusClasses = {
    'Chờ xác nhận': 'pending-booking',
    'Đã xác nhận': 'approved-booking',
    'Hoàn thành': 'approved-booking',
    'Huỷ bỏ': 'canceled-booking',
    'Từ chối': 'canceled-booking'
};

// Hàm lấy class cho item lịch
const getItemClass = status => statusClasses[status] || '';

// Hàm lấy class cho trạng thái
const getStatusClass = status => `booking-status ${status === 'Chờ xác nhận' ? 'pending' : ''}`;

// Hàm mở popup xác nhận hủy lịch
const openConfirmRejectPopup = async id => {
    const { isConfirmed } = await Swal.fire({
        title: 'Xác nhận hủy lịch',
        text: 'Bạn có chắc chắn muốn hủy lịch xem nhà trọ này?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xác nhận',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#f91942',
        cancelButtonColor: '#e0e0e0'
    });
    if (isConfirmed) emit('cancelSchedule', id); // Emit sự kiện hủy lịch nếu xác nhận
};

// Hàm mở modal đặt phòng
const openPopup = motelId => emit('openPopup', motelId);

// Hàm mở modal chỉnh sửa lịch
const openEditSchedulePopup = schedule => emit('editSchedule', schedule);
</script>

<style>
.bookings .list-box-listing-img {
    max-width: 150px;
    border-radius: 4px; /* Bo góc cho hình ảnh */
}

.button.gray.edit:hover {
    background-color: #2196f3 !important; /* Màu khi hover nút chỉnh sửa */
}

.swal2-container {
    z-index: 10000 !important; /* Đảm bảo popup SweetAlert2 hiển thị trên các phần tử khác */
}

.swal2-popup {
    width: 40em !important;
    border-radius: 10px !important;
    background-color: #ffffff !important;
    padding: 20px !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important; /* Hiệu ứng bóng cho popup */
}

.swal2-title {
    font-size: 2.2rem !important;
    color: #333 !important;
    font-weight: 600 !important; /* Định dạng tiêu đề popup */
}

.swal2-html-container {
    font-size: 1.6rem !important;
    color: #555 !important;
    line-height: 2.2rem !important; /* Định dạng nội dung popup */
}

.swal2-confirm {
    background-color: #f91942 !important;
    color: #ffffff !important;
    font-size: 1.4rem !important;
    padding: 9px 18px !important;
    border-radius: 5px !important;
    font-weight: 500 !important;
    transition: all 0.3s ease !important; /* Định dạng nút xác nhận */
}

.swal2-confirm:hover {
    background-color: #d81438 !important; /* Màu khi hover nút xác nhận */
}

.swal2-cancel {
    background-color: #e0e0e0 !important;
    color: #333 !important;
    font-size: 1.4rem !important;
    padding: 9px 18px !important;
    border-radius: 5px !important;
    border: 1px solid #ccc !important;
    font-weight: 500 !important;
    transition: all 0.3sEase !important; /* Định dạng nút hủy */
}

.swal2-cancel:hover {
    background-color: #d0d0d0 !important; /* Màu khi hover nút hủy */
}

.swal2-icon.swal2-warning {
    border-color: #f91942 !important;
    color: #f91942 !important; /* Định dạng biểu tượng cảnh báo */
}
</style>
