<template>
    <div id="booking-widget-anchor" class="boxed-widget booking-widget message-vendor margin-top-35">
        <h3><i class="fa fa-calendar-check-o"></i> Đặt lịch xem phòng</h3>
        <div class="row with-forms margin-top-0">
            <!-- Date Picker -->
            <div class="col-lg-12">
                <input type="text" id="date-picker" placeholder="Chọn ngày" readonly="readonly" />
            </div>
            <!-- Time Slots Dropdown -->
            <div class="col-lg-12">
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
                                    :value="slot.time"
                                    v-model="formData.timeSlot"
                                    @change="selectTimeSlot(slot.time)"
                                />
                                <label :for="'time-slot-' + index">
                                    <strong>{{ slot.time }}</strong>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Message -->
            <div class="col-lg-12">
                <textarea cols="10" rows="2" placeholder="Lời nhắn (tùy chọn)" v-model="formData.message"></textarea>
            </div>
        </div>
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
import { ref, onMounted, nextTick } from 'vue';
import { useToast } from 'vue-toastification';
import { useAuthStore } from '~/stores/auth';
import { useRoute } from 'vue-router';

const toast = useToast();
const authStore = useAuthStore();
const route = useRoute();
const { $api } = useNuxtApp();
const loading = ref(false);

// Dữ liệu mẫu cho time slots
const timeSlots = ref([
    { time: '8:00 sáng - 8:30 sáng' },
    { time: '9:00 sáng - 9:30 sáng' },
    { time: '10:00 sáng - 10:30 sáng' },
    { time: '11:00 sáng - 11:30 sáng' },
    { time: '13:00 sáng - 13:30 chiều' },
    { time: '14:00 chiều - 14:30 chiều' },
    { time: '15:00 chiều - 15:30 chiều' },
    { time: '16:00 chiều - 16:30 chiều' },
    { time: '17:00 chiều - 17:30 chiều' }
]);

// Dữ liệu form
const formData = ref({
    date: '',
    timeSlot: '',
    message: ''
});

// Trạng thái dropdown
const isTimeSlotDropdownOpen = ref(false);
const selectedTimeSlot = ref('');

// Hàm toggle dropdown
const toggleTimeSlotDropdown = () => {
    isTimeSlotDropdownOpen.value = !isTimeSlotDropdownOpen.value;
};

// Hàm chọn time slot
const selectTimeSlot = time => {
    selectedTimeSlot.value = time;
    isTimeSlotDropdownOpen.value = false;
};

const handleBackendError = error => {
    const data = error.response?._data;
    if (data?.error) {
        toast.error(data.error);
        return;
    }
    if (data?.errors) {
        Object.values(data.errors).forEach(err => toast.error(err[0]));
        return;
    }
    toast.error('Đã có lỗi xảy ra. Vui lòng thử lại.');
};

// Hàm gửi form
const submitForm = async () => {
    if (!authStore.user) {
        toast.error('Vui lòng đăng nhập!');
        return;
    }
    if (!formData.value.date || !formData.value.timeSlot) {
        toast.error('Vui lòng chọn ngày và khung giờ!');
        return;
    }

    try {
        loading.value = true;
        await $api('/schedules-bookings', {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            },
            params: {
                type: 'schedule'
            },
            body: {
                date: formData.value.date,
                timeSlot: formData.value.timeSlot,
                message: formData.value.message,
                user_id: authStore.user.id,
                room_id: route.params.id
            }
        });

        toast.success('Đặt lịch thành công!');
        formData.value = { date: '', timeSlot: '', message: '' };
        selectedTimeSlot.value = '';
    } catch (error) {
        handleBackendError(error);
    } finally {
        loading.value = false;
    }
};

// Khởi tạo date picker
onMounted(() => {
    nextTick(() => {
        if (window.jQuery && window.jQuery.fn.daterangepicker && window.moment) {
            // Tính ngày mai
            const tomorrow = window.moment().add(2, 'days');
            window
                .jQuery('#date-picker')
                .daterangepicker({
                    opens: 'left',
                    singleDatePicker: true,
                    minDate: tomorrow, // Chỉ cho phép chọn từ ngày mai trở đi
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
                    formData.value.date = picker.startDate.format('DD/MM/YYYY');
                });

            window.jQuery('#date-picker').on('showCalendar.daterangepicker', () => {
                window.jQuery('.daterangepicker').addClass('calendar-animated');
            });
            window.jQuery('#date-picker').on('show.daterangepicker', () => {
                window.jQuery('.daterangepicker').addClass('calendar-visible').removeClass('calendar-hidden');
            });
            window.jQuery('#date-picker').on('hide.daterangepicker', () => {
                window.jQuery('.daterangepicker').removeClass('calendar-visible').addClass('calendar-hidden');
            });
        } else {
            console.error('jQuery, Moment hoặc daterangepicker không được tải');
        }
    });
});
</script>

<style scoped>
.panel-dropdown-content {
    position: absolute;
    top: 44px;
    left: 0;
    width: 100%;
}

.spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
    margin-right: 8px;
    vertical-align: middle;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
