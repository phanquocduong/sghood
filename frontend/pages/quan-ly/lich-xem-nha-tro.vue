<template>
    <!-- Tiêu đề trang -->
    <Titlebar title="Lịch xem nhà trọ" />
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="dashboard-list-box margin-top-0">
                <!-- Bộ lọc lịch xem -->
                <ScheduleFilter v-model:filter="filter" @update:filter="fetchSchedules" />
                <!-- Danh sách lịch xem -->
                <ScheduleList
                    :schedules="schedules"
                    :is-loading="isLoading"
                    @cancel-schedule="cancelSchedule"
                    @open-popup="openPopup"
                    @edit-schedule="openEditSchedulePopup"
                />
            </div>
        </div>
    </div>
    <!-- Modal đặt phòng -->
    <BookingModal
        :rooms="rooms"
        :durations="durations"
        :formData="formData"
        @update:formData="formData = $event"
        @update:buttonLoading="buttonLoading = $event"
        @close="closeModal"
        @submit="fetchSchedules"
    />
    <!-- Modal chỉnh sửa lịch xem -->
    <EditScheduleModal
        :timeSlots="timeSlots"
        :editFormData="editFormData"
        @update:editFormData="editFormData = $event"
        @update:buttonLoading="buttonLoading = $event"
        @close="closeModal"
        @submit="fetchSchedules"
    />
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAppToast } from '~/composables/useToast';
import { useApi } from '~/composables/useApi';

definePageMeta({ layout: 'management' }); // Cấu hình layout cho trang là 'management'

const { $api } = useNuxtApp(); // Lấy instance của API từ NuxtApp
const { handleBackendError } = useApi(); // Hàm xử lý lỗi từ backend
const toast = useAppToast(); // Hàm hiển thị thông báo
const config = useState('configs'); // Lấy cấu hình từ state

// Khởi tạo các biến reactive
const schedules = ref([]); // Danh sách lịch xem nhà trọ
const filter = ref({ sort: 'default', status: '' }); // Bộ lọc (sắp xếp và trạng thái)
const isLoading = ref(false); // Trạng thái loading khi tải dữ liệu
const buttonLoading = ref(false); // Trạng thái loading của nút trong modal
const formData = ref({ room_id: null, date: '', duration: '', note: '' }); // Dữ liệu form đặt phòng
const editFormData = ref({ id: null, date: '', timeSlot: '', message: '' }); // Dữ liệu form chỉnh sửa lịch
const rooms = ref([]); // Danh sách phòng
const durations = ref([]); // Danh sách thời gian thuê
const timeSlots = ref([]); // Danh sách khung giờ xem

// Hàm lấy danh sách lịch xem từ API
const fetchSchedules = async () => {
    isLoading.value = true; // Bật trạng thái loading
    try {
        const { data } = await $api('/schedules', { params: filter.value }); // Gọi API để lấy lịch
        schedules.value = data; // Cập nhật danh sách lịch
    } catch (error) {
        handleBackendError(error, toast); // Xử lý lỗi nếu có
    } finally {
        isLoading.value = false; // Tắt trạng thái loading
    }
};

// Hàm hủy lịch xem
const cancelSchedule = async id => {
    isLoading.value = true; // Bật trạng thái loading
    try {
        await $api(`/schedules/${id}/cancel`, {
            method: 'POST',
            headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value },
            body: { _method: 'PATCH' } // Sử dụng _method để mô phỏng PATCH request
        });
        await fetchSchedules(); // Làm mới danh sách lịch
        toast.success('Hủy lịch xem nhà trọ thành công'); // Hiển thị thông báo thành công
    } catch (error) {
        handleBackendError(error, toast); // Xử lý lỗi nếu có
    } finally {
        isLoading.value = false; // Tắt trạng thái loading
    }
};

// Hàm lấy danh sách phòng của một nhà trọ
const fetchRooms = async motelId => {
    try {
        const response = await $api(`/motels/${motelId}/rooms`); // Gọi API để lấy danh sách phòng
        rooms.value = Array.isArray(response) ? response : []; // Gán danh sách phòng hoặc mảng rỗng
        if (rooms.value.length === 0) {
            toast.error('Không tìm thấy phòng nào cho nhà trọ này'); // Thông báo nếu không có phòng
        }
    } catch (error) {
        handleBackendError(error, toast); // Xử lý lỗi nếu có
        rooms.value = []; // Đặt lại danh sách phòng rỗng
    }
};

// Hàm đóng modal
const closeModal = () => {
    if (window.jQuery && window.jQuery.fn.magnificPopup) {
        window.jQuery.magnificPopup.close(); // Đóng modal sử dụng Magnific Popup
    }
};

// Hàm mở modal đặt phòng
const openPopup = async motelId => {
    formData.value = { room_id: null, date: '', duration: '', note: '' }; // Reset dữ liệu form
    await fetchRooms(motelId); // Lấy danh sách phòng

    if (!window.jQuery || !window.jQuery.fn.magnificPopup) {
        console.error('Magnific Popup không được tải'); // Báo lỗi nếu thư viện Magnific Popup không tải được
        return;
    }

    if (rooms.value.length === 0) {
        console.warn('No rooms available, modal will not open'); // Cảnh báo nếu không có phòng
        return;
    }

    // Cấu hình và mở modal Magnific Popup
    window.jQuery.magnificPopup.open({
        items: { src: '#small-dialog', type: 'inline' },
        fixedContentPos: false,
        fixedBgPos: true,
        overflowY: 'auto',
        closeBtnInside: true,
        preloader: false,
        midClick: true,
        removalDelay: 300,
        mainClass: 'my-mfp-zoom-in',
        closeOnBgClick: false
    });
};

// Hàm mở modal chỉnh sửa lịch xem
const openEditSchedulePopup = async schedule => {
    // Gán dữ liệu lịch xem vào form chỉnh sửa
    editFormData.value = {
        id: schedule.id,
        date: schedule.scheduled_at ? window.moment(schedule.scheduled_at).format('DD/MM/YYYY') : '',
        timeSlot: schedule.scheduled_at ? formatTimeSlot(schedule.scheduled_at) : '',
        message: schedule.message || ''
    };

    if (!window.jQuery || !window.jQuery.fn.magnificPopup) {
        console.error('Magnific Popup không được tải'); // Báo lỗi nếu thư viện Magnific Popup không tải được
        return;
    }

    // Cấu hình và mở modal chỉnh sửa
    window.jQuery.magnificPopup.open({
        items: { src: '#edit-schedule-dialog', type: 'inline' },
        fixedContentPos: false,
        fixedBgPos: true,
        overflowY: 'auto',
        closeBtnInside: true,
        preloader: false,
        midClick: true,
        removalDelay: 300,
        mainClass: 'my-mfp-zoom-in',
        closeOnBgClick: false
    });
};

// Hàm định dạng khung giờ từ thời gian lịch xem
const formatTimeSlot = scheduledAt => {
    const date = window.moment(scheduledAt); // Chuyển đổi thời gian sang định dạng moment
    const hour = date.hour();
    const minute = date.minute();
    const period = hour >= 12 ? 'chiều' : 'sáng'; // Xác định buổi sáng/chiều

    const startHour = hour;
    const startMinute = minute.toString().padStart(2, '0');
    const endDate = date.clone().add(30, 'minutes'); // Tính thời gian kết thúc (30 phút sau)
    const endHour = endDate.hour();
    const endMinute = endDate.minute().toString().padStart(2, '0');
    const endPeriod = endHour >= 12 ? 'chiều' : 'sáng';

    return `${startHour}:${startMinute} ${period} - ${endHour}:${endMinute} ${endPeriod}`; // Trả về khung giờ định dạng
};

// Khởi tạo khi component được mount
onMounted(() => {
    // Gán danh sách khung giờ từ cấu hình
    if (config.value?.time_slots_viewing_schedule) {
        timeSlots.value = JSON.parse(config.value.time_slots_viewing_schedule) || [];
    }

    // Gán danh sách thời gian thuê từ cấu hình
    if (config.value?.booking_durations) {
        durations.value = JSON.parse(config.value.booking_durations) || [];
    }

    fetchSchedules(); // Lấy danh sách lịch xem khi trang được tải
});
</script>
