<template>
    <!-- Tiêu đề trang -->
    <Titlebar title="Đặt phòng" />

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="dashboard-list-box margin-top-0">
                <!-- Bộ lọc đặt phòng -->
                <BookingFilter v-model:filter="filter" @update:filter="fetchBookings" />
                <!-- Danh sách đặt phòng -->
                <BookingList :bookings="bookings" :is-loading="isLoading" @cancel-booking="cancelBooking" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAppToast } from '~/composables/useToast';
import { useApi } from '~/composables/useApi';

// Định nghĩa layout cho trang
definePageMeta({
    layout: 'management' // Sử dụng layout 'management'
});

const { $api } = useNuxtApp(); // Lấy instance của API từ NuxtApp
const { handleBackendError } = useApi(); // Hàm xử lý lỗi từ backend
const toast = useAppToast(); // Hàm hiển thị thông báo

// Khởi tạo các biến reactive
const bookings = ref([]); // Danh sách đặt phòng
const filter = ref({ sort: 'default', status: '' }); // Bộ lọc (sắp xếp và trạng thái)
const isLoading = ref(false); // Trạng thái loading khi tải dữ liệu

// Hàm lấy danh sách đặt phòng từ API
const fetchBookings = async () => {
    isLoading.value = true; // Bật trạng thái loading
    try {
        const response = await $api('/bookings', { method: 'GET', params: filter.value }); // Gọi API để lấy danh sách đặt phòng
        bookings.value = response.data; // Cập nhật danh sách đặt phòng
    } catch (error) {
        handleBackendError(error, toast); // Xử lý lỗi nếu có
    } finally {
        isLoading.value = false; // Tắt trạng thái loading
    }
};

// Hàm hủy đặt phòng
const cancelBooking = async id => {
    isLoading.value = true; // Bật trạng thái loading
    try {
        await $api(`/bookings/${id}/cancel`, {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value // Thêm token bảo mật
            },
            body: { _method: 'PATCH' } // Sử dụng _method để mô phỏng PATCH request
        });
        await fetchBookings(); // Làm mới danh sách đặt phòng
        toast.success('Hủy đặt phòng thành công'); // Hiển thị thông báo thành công
    } catch (error) {
        handleBackendError(error, toast); // Xử lý lỗi nếu có
    } finally {
        isLoading.value = false; // Tắt trạng thái loading
    }
};

// Khởi tạo khi component được mount
onMounted(() => {
    fetchBookings(); // Lấy danh sách đặt phòng khi trang được tải
});
</script>

<style scoped>
/* CSS cho input date-picker */
input#date-picker {
    border: 1px solid #dbdbdb;
    box-shadow: 0 1px 3px 0px rgba(0, 0, 0, 0.08); /* Hiệu ứng bóng */
}

/* CSS cho spinner loading */
.spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite; /* Hiệu ứng xoay */
    margin-right: 8px;
    vertical-align: middle;
}

/* Hiệu ứng xoay cho spinner */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* CSS cho nút bị vô hiệu hóa */
.button:disabled {
    opacity: 0.6;
    cursor: not-allowed; /* Biểu tượng con trỏ không cho phép */
}
</style>
