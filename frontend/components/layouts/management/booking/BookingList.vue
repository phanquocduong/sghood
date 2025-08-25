<template>
    <!-- Tiêu đề danh sách đặt phòng -->
    <h4>Quản lý đặt phòng</h4>

    <!-- Hiển thị loading khi đang tải dữ liệu -->
    <Loading :is-loading="isLoading" />

    <!-- Danh sách đặt phòng -->
    <ul>
        <li v-for="item in bookings" :key="item.id" :class="getItemClass(item.status)">
            <div class="list-box-listing bookings">
                <!-- Hình ảnh phòng -->
                <div class="list-box-listing-img">
                    <NuxtLink :to="`/danh-sach-nha-tro/${item.motel_slug}`" target="_blank" style="height: 150px">
                        <img :src="config.public.baseUrl + item.room_image" :alt="item.room_name - item.motel_name" />
                        <!-- Hình ảnh phòng -->
                    </NuxtLink>
                </div>
                <!-- Nội dung đặt phòng -->
                <div class="list-box-listing-content">
                    <div class="inner">
                        <h3>
                            {{ item.room_name }} - {{ item.motel_name }}
                            <!-- Tên phòng và nhà trọ -->
                            <span :class="getStatusClass(item.status)">{{
                                item.status === 'Chấp nhận' ? 'Đã được chấp nhận' : item.status
                            }}</span>
                            <!-- Trạng thái đặt phòng -->
                        </h3>
                        <!-- Lý do từ chối (nếu có) -->
                        <div v-if="item.rejection_reason && item.status === 'Từ chối'" class="inner-booking-list">
                            <h5>Lý do QTV từ chối:</h5>
                            <ul class="booking-list">
                                <li>{{ item.rejection_reason }}</li>
                            </ul>
                        </div>
                        <!-- Ngày bắt đầu -->
                        <div class="inner-booking-list">
                            <h5>Ngày bắt đầu:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDate(item.start_date) }}</li>
                            </ul>
                        </div>
                        <!-- Ngày kết thúc -->
                        <div class="inner-booking-list">
                            <h5>Ngày kết thúc:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDate(item.end_date) }}</li>
                            </ul>
                        </div>
                        <!-- Thời gian thuê -->
                        <div class="inner-booking-list">
                            <h5>Thời gian thuê:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ calculateRentalYears(item.start_date, item.end_date) }}</li>
                            </ul>
                        </div>
                        <!-- Ghi chú (nếu có) -->
                        <div v-if="item.note" class="inner-booking-list">
                            <h5>Ghi chú:</h5>
                            <ul class="booking-list">
                                <li>{{ item.note }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Các nút hành động -->
            <div class="buttons-to-right">
                <!-- Nút hủy đặt phòng (hiển thị khi trạng thái là "Chờ xác nhận") -->
                <a
                    v-if="item.status === 'Chờ xác nhận'"
                    href="#"
                    @click.prevent="openConfirmRejectPopup(item.id)"
                    class="button gray reject"
                >
                    <i class="sl sl-icon-close"></i> Hủy bỏ
                </a>
                <!-- Nút xem hợp đồng (hiển thị khi trạng thái là "Chấp nhận") -->
                <NuxtLink v-if="item.status === 'Chấp nhận'" :to="`/quan-ly/hop-dong/${item.contract_id}`" class="button gray approve">
                    <i class="im im-icon-Folder-Bookmark"></i> Xem hợp đồng
                </NuxtLink>
            </div>
        </li>
        <!-- Thông báo khi không có đặt phòng -->
        <div v-if="!bookings.length" class="col-md-12 text-center">
            <p>Chưa có đặt phòng nào.</p>
        </div>
    </ul>
</template>

<script setup>
import Swal from 'sweetalert2';
import { useFormatDate } from '~/composables/useFormatDate';

const { formatDate } = useFormatDate(); // Hàm định dạng ngày
const config = useRuntimeConfig(); // Lấy cấu hình runtime
const props = defineProps({
    bookings: {
        type: Array,
        required: true // Danh sách đặt phòng
    },
    isLoading: {
        type: Boolean,
        required: true // Trạng thái loading
    }
});

const emit = defineEmits(['cancelBooking']); // Emit sự kiện hủy đặt phòng

// Hàm tính thời gian thuê (theo năm)
const calculateRentalYears = (startDate, endDate) => {
    if (!startDate || !endDate) return 'Không xác định'; // Kiểm tra dữ liệu hợp lệ
    const start = new Date(startDate);
    const end = new Date(endDate);
    const diffInYears = end.getFullYear() - start.getFullYear(); // Tính chênh lệch năm

    if (diffInYears < 0) return 'Ngày không hợp lệ'; // Kiểm tra ngày hợp lệ
    return `${diffInYears} năm`; // Trả về thời gian thuê
};

// Hàm lấy class cho item đặt phòng
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
            return ''; // Trả về rỗng nếu trạng thái không xác định
    }
};

// Hàm lấy class cho trạng thái
const getStatusClass = status => {
    let statusClass = 'booking-status';
    if (status === 'Chờ xác nhận') {
        statusClass += ' pending'; // Thêm class pending nếu trạng thái là "Chờ xác nhận"
    }
    return statusClass;
};

// Hàm mở popup xác nhận hủy đặt phòng
const openConfirmRejectPopup = async id => {
    const result = await Swal.fire({
        title: 'Xác nhận hủy đặt phòng',
        text: 'Bạn có chắc chắn muốn hủy đặt phòng này?', // Thông báo xác nhận
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xác nhận',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#f91942',
        cancelButtonColor: '#e0e0e0'
    });

    if (result.isConfirmed) {
        emit('cancelBooking', id); // Emit sự kiện hủy nếu xác nhận
    }
};
</script>

<style>
/* CSS cho hình ảnh trong danh sách đặt phòng */
.bookings .list-box-listing-img {
    max-width: 150px;
    max-height: none;
    border-radius: 4px; /* Bo góc cho hình ảnh */
}
</style>
