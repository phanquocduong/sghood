<!-- Template cho component form đặt lịch xem trọ -->
<template>
    <div id="booking-widget-anchor" class="boxed-widget booking-widget message-vendor">
        <h3><i class="fa fa-calendar-check-o"></i> Đặt lịch xem trọ</h3>
        <div class="row with-forms margin-top-0">
            <!-- Trường chọn ngày -->
            <div class="col-lg-12">
                <input type="text" id="date-picker" placeholder="Chọn ngày" readonly="readonly" />
            </div>

            <!-- Dropdown chọn khung giờ (desktop) -->
            <div class="col-lg-12 desktop-time-slot">
                <div class="panel-dropdown time-slots-dropdown">
                    <a href="#" :class="{ active: isTimeSlotDropdownOpen }" @click.prevent="toggleTimeSlotDropdown">
                        {{ selectedTimeSlot || 'Chọn khung giờ' }}
                    </a>
                    <div class="panel-dropdown-content padding-reset" v-show="isTimeSlotDropdownOpen">
                        <div class="panel-dropdown-scrollable">
                            <div class="time-slot" v-for="(slot, index) in timeSlots" :key="index">
                                <input
                                    type="radio"
                                    :name="'time-slot-' + index"
                                    :id="'time-slot-' + index"
                                    :value="slot"
                                    v-model="formData.timeSlot"
                                    @change="selectTimeSlot(slot)"
                                />
                                <label :for="'time-slot-' + index">
                                    <strong>{{ slot }}</strong>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dropdown chọn khung giờ (mobile) -->
            <div class="col-lg-12 mobile-time-slot">
                <select v-model="formData.timeSlot" class="custom-select time-slot-select" @change="onMobileTimeSlotChange">
                    <option value="" disabled>Chọn khung giờ</option>
                    <option v-for="(slot, index) in timeSlots" :key="index" :value="slot">
                        {{ slot }}
                    </option>
                </select>
            </div>

            <!-- Trường nhập lời nhắn -->
            <div class="col-lg-12">
                <textarea cols="10" rows="2" placeholder="Thêm lời nhắn (không bắt buộc)..." v-model="formData.message"></textarea>
            </div>
        </div>
        <!-- Nút gửi form -->
        <button
            class="button book-now fullwidth margin-top-5"
            :class="{ disabled: !formData.date || !formData.timeSlot }"
            :disabled="loading"
            @click.prevent="submitForm"
        >
            <span v-if="loading" class="spinner"></span>
            {{ loading ? 'Đang đặt...' : 'Đặt lịch' }}
        </button>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import { useAppToast } from '~/composables/useToast';
import { useAuthStore } from '~/stores/auth';

const toast = useAppToast(); // Composable hiển thị thông báo
const authStore = useAuthStore(); // Store xác thực người dùng
const { $api } = useNuxtApp(); // Lấy đối tượng API từ Nuxt
const loading = ref(false); // Trạng thái đang tải
const config = useState('configs'); // Lấy cấu hình từ state toàn cục

const timeSlots = ref([]); // Danh sách khung giờ
const formData = ref({
    date: '',
    timeSlot: '',
    message: ''
}); // Dữ liệu form
const isTimeSlotDropdownOpen = ref(false); // Trạng thái mở dropdown khung giờ
const selectedTimeSlot = ref(''); // Khung giờ được chọn

const props = defineProps({
    motelId: {
        type: [Number, String],
        required: true
    }
});

// Hàm bật/tắt dropdown khung giờ
const toggleTimeSlotDropdown = () => {
    isTimeSlotDropdownOpen.value = !isTimeSlotDropdownOpen.value;
};

// Hàm chọn khung giờ
const selectTimeSlot = time => {
    selectedTimeSlot.value = time; // Lưu khung giờ
    isTimeSlotDropdownOpen.value = false; // Đóng dropdown
    formData.value.timeSlot = time; // Cập nhật form
};

// Hàm xử lý thay đổi khung giờ trên mobile
const onMobileTimeSlotChange = () => {
    selectedTimeSlot.value = formData.value.timeSlot; // Cập nhật khung giờ
};

// Hàm xử lý click ngoài dropdown để đóng
const handleClickOutside = event => {
    const dropdown = event.target.closest('.time-slots-dropdown');
    if (!dropdown && isTimeSlotDropdownOpen.value) {
        isTimeSlotDropdownOpen.value = false;
    }
};

// Hàm xử lý lỗi từ backend
const handleBackendError = error => {
    const data = error.response?._data;
    if (data?.error) {
        toast.error(data.error); // Hiển thị lỗi chung
        return;
    }
    if (data?.errors) {
        Object.values(data.errors).forEach(err => toast.error(err[0])); // Hiển thị lỗi cụ thể
        return;
    }
    toast.error('Đã có lỗi xảy ra. Vui lòng thử lại.'); // Lỗi mặc định
};

// Hàm gửi form đặt lịch
const submitForm = async () => {
    if (!authStore.user) {
        toast.error('Vui lòng đăng nhập!'); // Kiểm tra đăng nhập
        return;
    }
    if (!formData.value.date || !formData.value.timeSlot) {
        toast.error('Vui lòng chọn ngày và khung giờ!'); // Kiểm tra dữ liệu bắt buộc
        return;
    }

    try {
        loading.value = true; // Bật trạng thái đang tải
        await $api('/schedules', {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value // Gửi CSRF token
            },
            body: {
                date: formData.value.date,
                timeSlot: formData.value.timeSlot,
                message: formData.value.message,
                user_id: authStore.user.id,
                motel_id: props.motelId
            }
        });

        toast.success('Đặt lịch xem nhà trọ thành công!'); // Thông báo thành công
        formData.value = { date: '', timeSlot: '', message: '' }; // Reset form
        selectedTimeSlot.value = ''; // Reset khung giờ
    } catch (error) {
        handleBackendError(error); // Xử lý lỗi
    } finally {
        loading.value = false; // Tắt trạng thái đang tải
    }
};

// Theo dõi cấu hình để cập nhật danh sách khung giờ
watch(
    () => config.value?.time_slots_viewing_schedule,
    newValue => {
        if (newValue) {
            try {
                timeSlots.value = JSON.parse(newValue) || []; // Parse danh sách khung giờ
            } catch (error) {
                console.error('Lỗi khi parse time_slots_viewing_schedule:', error); // Ghi log lỗi
                timeSlots.value = [];
            }
        }
    },
    { immediate: true }
);

// Khởi tạo datepicker và xử lý sự kiện click ngoài
onMounted(() => {
    document.addEventListener('click', handleClickOutside); // Thêm sự kiện click ngoài

    nextTick(() => {
        if (window.jQuery && window.jQuery.fn.daterangepicker && window.moment) {
            const tomorrow = window.moment().add(2, 'days'); // Ngày tối thiểu là ngày mai
            const $datePicker = window.jQuery('#date-picker');
            $datePicker
                .daterangepicker({
                    opens: 'left',
                    singleDatePicker: true,
                    minDate: tomorrow,
                    locale: {
                        format: 'DD/MM/YYYY',
                        applyLabel: 'Xác nhận',
                        cancelLabel: 'Hủy',
                        daysOfWeek: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
                        monthNames: [
                            'Tháng 1',
                            'Tháng 2',
                            'Tháng 3',
                            'Tháng 4',
                            'Tháng 5',
                            'Tháng 6',
                            'Tháng 7',
                            'Tháng 8',
                            'Tháng 9',
                            'Tháng 10',
                            'Tháng 11',
                            'Tháng 12'
                        ]
                    }
                })
                .on('apply.daterangepicker', (ev, picker) => {
                    formData.value.date = picker.startDate.format('DD/MM/YYYY'); // Cập nhật ngày
                    $datePicker.val(picker.startDate.format('DD/MM/YYYY')); // Hiển thị ngày
                })
                .on('cancel.daterangepicker', () => {
                    formData.value.date = ''; // Xóa ngày
                    $datePicker.val(''); // Xóa input
                })
                .on('showCalendar.daterangepicker', () => {
                    window.jQuery('.daterangepicker').addClass('calendar-animated'); // Hiệu ứng hiển thị lịch
                })
                .on('show.daterangepicker', () => {
                    window.jQuery('.daterangepicker').addClass('calendar-visible').removeClass('calendar-hidden');
                })
                .on('hide.daterangepicker', () => {
                    window.jQuery('.daterangepicker').removeClass('calendar-visible').addClass('calendar-hidden');
                });

            $datePicker.val(formData.value.date || ''); // Khởi tạo giá trị
        } else {
            console.error('jQuery, Moment hoặc daterangepicker không được tải'); // Ghi log lỗi
        }
    });
});
</script>

<!-- CSS tùy chỉnh cho component -->
<style scoped>
@import '~/public/css/schedule-form.css';
</style>
