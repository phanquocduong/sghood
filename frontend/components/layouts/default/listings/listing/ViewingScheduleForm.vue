<template>
    <div id="booking-widget-anchor" class="boxed-widget booking-widget message-vendor">
        <h3><i class="fa fa-calendar-check-o"></i> Đặt lịch xem trọ</h3>
        <div class="row with-forms margin-top-0">
            <div class="col-lg-12">
                <input type="text" id="date-picker" placeholder="Chọn ngày" readonly="readonly" />
            </div>

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

            <div class="col-lg-12 mobile-time-slot">
                <select v-model="formData.timeSlot" class="custom-select time-slot-select" @change="onMobileTimeSlotChange">
                    <option value="" disabled>Chọn khung giờ</option>
                    <option v-for="(slot, index) in timeSlots" :key="index" :value="slot">
                        {{ slot }}
                    </option>
                </select>
            </div>

            <div class="col-lg-12">
                <textarea cols="10" rows="2" placeholder="Thêm lời nhắn (không bắt buộc)..." v-model="formData.message"></textarea>
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
import { ref, onMounted, nextTick, watch } from 'vue';
import { useAppToast } from '~/composables/useToast';
import { useAuthStore } from '~/stores/auth';

const toast = useAppToast();
const authStore = useAuthStore();
const { $api } = useNuxtApp();
const loading = ref(false);
const config = useState('configs');

const timeSlots = ref([]);
const formData = ref({
    date: '',
    timeSlot: '',
    message: ''
});
const isTimeSlotDropdownOpen = ref(false);
const selectedTimeSlot = ref('');

const props = defineProps({
    motelId: {
        type: [Number, String],
        required: true
    }
});

const toggleTimeSlotDropdown = () => {
    isTimeSlotDropdownOpen.value = !isTimeSlotDropdownOpen.value;
};

const selectTimeSlot = time => {
    selectedTimeSlot.value = time;
    isTimeSlotDropdownOpen.value = false;
    formData.value.timeSlot = time;
};

const onMobileTimeSlotChange = () => {
    selectedTimeSlot.value = formData.value.timeSlot;
};

const handleClickOutside = event => {
    const dropdown = event.target.closest('.time-slots-dropdown');
    if (!dropdown && isTimeSlotDropdownOpen.value) {
        isTimeSlotDropdownOpen.value = false;
    }
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
        await $api('/schedules', {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            },
            body: {
                date: formData.value.date,
                timeSlot: formData.value.timeSlot,
                message: formData.value.message,
                user_id: authStore.user.id,
                motel_id: props.motelId
            }
        });

        toast.success('Đặt lịch xem nhà trọ thành công!');
        formData.value = { date: '', timeSlot: '', message: '' };
        selectedTimeSlot.value = '';
    } catch (error) {
        handleBackendError(error);
    } finally {
        loading.value = false;
    }
};

// Theo dõi config để cập nhật timeSlots
watch(
    () => config.value?.time_slots_viewing_schedule,
    newValue => {
        if (newValue) {
            try {
                timeSlots.value = JSON.parse(newValue) || [];
            } catch (error) {
                console.error('Lỗi khi parse time_slots_viewing_schedule:', error);
                timeSlots.value = [];
            }
        }
    },
    { immediate: true }
);

onMounted(() => {
    document.addEventListener('click', handleClickOutside);

    nextTick(() => {
        if (window.jQuery && window.jQuery.fn.daterangepicker && window.moment) {
            const tomorrow = window.moment().add(2, 'days');
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
                    formData.value.date = picker.startDate.format('DD/MM/YYYY');
                    $datePicker.val(picker.startDate.format('DD/MM/YYYY'));
                })
                .on('cancel.daterangepicker', () => {
                    formData.value.date = '';
                    $datePicker.val('');
                })
                .on('showCalendar.daterangepicker', () => {
                    window.jQuery('.daterangepicker').addClass('calendar-animated');
                })
                .on('show.daterangepicker', () => {
                    window.jQuery('.daterangepicker').addClass('calendar-visible').removeClass('calendar-hidden');
                })
                .on('hide.daterangepicker', () => {
                    window.jQuery('.daterangepicker').removeClass('calendar-visible').addClass('calendar-hidden');
                });

            $datePicker.val(formData.value.date || '');
        } else {
            console.error('jQuery, Moment hoặc daterangepicker không được tải');
        }
    });
});
</script>

<style scoped>
.desktop-time-slot {
    display: block;
}

.mobile-time-slot {
    display: none;
}

.panel-dropdown-content {
    position: absolute;
    top: 44px;
    left: 0;
    width: 100%;
    z-index: 1000;
}

.custom-select {
    width: 100%;
    padding: 15px 20px;
    font-size: 16px;
    font-family: inherit;
    font-weight: 400;
    line-height: 1.5;
    color: #333;
    background: #fff;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23f91942' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 12px center;
    background-repeat: no-repeat;
    background-size: 16px 12px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    transition: all 0.15s ease-in-out;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    cursor: pointer;
}

.custom-select:focus {
    outline: none;
    border-color: #f91942;
    box-shadow: 0 0 0 3px rgba(249, 25, 66, 0.15);
}

.custom-select:hover {
    border-color: #f91942;
}

.custom-select option {
    padding: 12px 16px;
    font-size: 16px;
    color: #333;
    background-color: #fff;
}

.custom-select option:hover {
    background-color: #fff;
}

.custom-select option:checked {
    background-color: #f91942;
    color: #fff;
}

/* Tablet styles */
@media (max-width: 1024px) {
    .desktop-time-slot {
        display: none;
    }

    .mobile-time-slot {
        display: block;
    }

    .custom-select {
        line-height: 2rem;
        padding: 9px 18px;
        font-size: 15px;
        background-size: 14px 10px;
        background-position: right 14px center;
    }
}

@media (max-width: 768px) {
    .custom-select {
        padding: 10px 20px;
        font-size: 16px;
        border-radius: 10px;
        background-size: 16px 12px;
        background-position: right 16px center;
        min-height: 56px;
        border-width: 2px;
    }

    .custom-select:focus {
        box-shadow: 0 0 0 4px rgba(249, 25, 66, 0.15);
    }

    /* iOS specific styles */
    .custom-select option {
        font-size: 16px;
        padding: 16px;
    }
}

@media (max-width: 480px) {
    .custom-select {
        line-height: 2rem;
        font-size: 17px;
        border-radius: 8px;
        min-height: 60px;
        background-position: right 18px center;
    }
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

* {
    transition: all 0.15s ease-in-out;
}

.custom-select:focus-visible {
    outline: 2px solid #f91942;
    outline-offset: 2px;
}
</style>
