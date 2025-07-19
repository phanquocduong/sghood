<template>
    <div>
        <Titlebar title="Lịch xem nhà trọ" />

        <!-- Modal Dialog for Booking -->
        <div id="small-dialog" class="zoom-anim-dialog mfp-hide">
            <div class="small-dialog-header">
                <h3>Đặt phòng trọ</h3>
                <p class="booking-subtitle">Vui lòng điền đầy đủ thông tin để đặt phòng</p>
            </div>
            <div class="message-reply margin-top-0">
                <div class="booking-form-grid">
                    <div class="form-row">
                        <div class="form-col">
                            <label><i class="fa fa-bed"></i> Phòng:</label>
                            <select v-model="formData.room_id" class="modal-room-select" ref="roomSelect">
                                <option value="">Chọn phòng</option>
                                <option v-for="room in rooms" :key="room.id" :value="room.id">{{ room.name }}</option>
                            </select>
                        </div>
                        <div class="form-col">
                            <label><i class="fa fa-calendar"></i> Ngày bắt đầu:</label>
                            <div class="date-input-container">
                                <input type="text" id="date-picker" placeholder="Chọn ngày bắt đầu" readonly="readonly" />
                            </div>
                        </div>
                        <div class="form-col">
                            <label><i class="fa fa-clock-o"></i> Thời gian thuê:</label>
                            <select v-model="formData.duration" class="modal-duration-select" ref="durationSelect">
                                <option value="">Chọn thời gian</option>
                                <option v-for="duration in durations" :value="duration">{{ duration }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="booking-note-section">
                    <label><i class="fa fa-sticky-note"></i> Ghi chú:</label>
                    <textarea v-model="formData.note" cols="40" rows="3" placeholder="Thêm ghi chú (không bắt buộc)..."></textarea>
                </div>
                <div class="booking-actions">
                    <button @click="closeModal" class="button gray" type="button"><i class="fa fa-times"></i> Hủy</button>
                    <button @click.prevent="submitBooking" class="button" :disabled="buttonLoading">
                        <span v-if="buttonLoading" class="spinner"></span>
                        <i v-else class="fa fa-check"></i>
                        {{ buttonLoading ? 'Đang xử lý...' : 'Đặt phòng' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Dialog for Editing Schedule -->
        <div id="edit-schedule-dialog" class="zoom-anim-dialog mfp-hide">
            <div class="small-dialog-header">
                <h3>Chỉnh sửa lịch xem nhà trọ</h3>
                <p class="booking-subtitle">Vui lòng cập nhật thông tin lịch xem</p>
            </div>
            <div class="message-reply margin-top-0">
                <div class="booking-form-grid">
                    <div class="form-row">
                        <div class="form-col">
                            <label><i class="fa fa-calendar"></i> Ngày xem:</label>
                            <div class="date-input-container">
                                <input type="text" id="edit-date-picker" placeholder="Chọn ngày xem" readonly="readonly" />
                            </div>
                        </div>
                        <div class="form-col">
                            <label><i class="fa fa-clock-o"></i> Khung giờ:</label>
                            <select v-model="editFormData.timeSlot" class="modal-time-select" ref="timeSelect">
                                <option value="">Chọn khung giờ</option>
                                <option v-for="slot in timeSlots" :value="slot">{{ slot }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="booking-note-section">
                    <label><i class="fa fa-sticky-note"></i> Lời nhắn:</label>
                    <textarea v-model="editFormData.message" cols="40" rows="3" placeholder="Thêm lời nhắn (không bắt buộc)..."></textarea>
                </div>
                <div class="booking-actions">
                    <button @click="closeModal" class="button gray" type="button"><i class="fa fa-times"></i> Hủy</button>
                    <button @click.prevent="submitEditSchedule" class="button" :disabled="buttonLoading">
                        <span v-if="buttonLoading" class="spinner"></span>
                        <i v-else class="fa fa-check"></i>
                        {{ buttonLoading ? 'Đang xử lý...' : 'Cập nhật' }}
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="dashboard-list-box margin-top-0">
                    <ScheduleFilter v-model:filter="filter" @update:filter="fetchSchedules" />
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
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import { useToast } from 'vue-toastification';
import { useApi } from '~/composables/useApi';
import { useRouter } from 'vue-router';

definePageMeta({ layout: 'management' });

const { $api } = useNuxtApp();
const { handleBackendError } = useApi();
const toast = useToast();
const router = useRouter();

const schedules = ref([]);
const filter = ref({ sort: 'default', status: '' });
const isLoading = ref(false);
const buttonLoading = ref(false);
const formData = ref({ room_id: null, date: '', duration: '', note: '' });
const editFormData = ref({ id: null, date: '', timeSlot: '', message: '' });
const rooms = ref([]);
const roomSelect = ref(null);
const timeSelect = ref(null);
const durations = ref(['1 năm', '2 năm', '3 năm', '4 năm', '5 năm']);
const durationSelect = ref(null);
const timeSlots = ref([
    '8:00 sáng - 8:30 sáng',
    '9:00 sáng - 9:30 sáng',
    '10:00 sáng - 10:30 sáng',
    '11:00 sáng - 11:30 sáng',
    '13:00 chiều - 13:30 chiều',
    '14:00 chiều - 14:30 chiều',
    '15:00 chiều - 15:30 chiều',
    '16:00 chiều - 16:30 chiều',
    '17:00 chiều - 17:30 chiều'
]);

const fetchSchedules = async () => {
    isLoading.value = true;
    try {
        const { data } = await $api('/schedules', { params: filter.value });
        schedules.value = data;
    } catch (error) {
        handleBackendError(error, toast);
    } finally {
        isLoading.value = false;
    }
};

const cancelSchedule = async id => {
    isLoading.value = true;
    try {
        await $api(`/schedules/${id}/cancel`, {
            method: 'POST',
            headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value },
            body: { _method: 'PATCH' }
        });
        await fetchSchedules();
        toast.success('Hủy lịch xem nhà trọ thành công');
    } catch (error) {
        handleBackendError(error, toast);
    } finally {
        isLoading.value = false;
    }
};

const fetchRooms = async motelId => {
    try {
        rooms.value = await $api(`/motels/${motelId}/rooms`);
    } catch (error) {
        handleBackendError(error, toast);
    }
};

const submitBooking = async () => {
    console.log(formData.value);
    if (!formData.value.room_id || !formData.value.date || !formData.value.duration) {
        return toast.error('Vui lòng chọn phòng, ngày bắt đầu và thời gian thuê');
    }

    buttonLoading.value = true;
    try {
        await $api('/bookings', {
            method: 'POST',
            headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value },
            body: {
                room_id: formData.value.room_id,
                start_date: formData.value.date,
                duration: formData.value.duration,
                note: formData.value.note
            }
        });
        toast.success('Đặt phòng thành công');
        formData.value = { room_id: null, date: '', duration: '', note: '' };
        rooms.value = [];
        window.jQuery.magnificPopup.close();
        router.push('/quan-ly/dat-phong');
    } catch (error) {
        handleBackendError(error, toast);
    } finally {
        buttonLoading.value = false;
    }
};

const submitEditSchedule = async () => {
    if (!editFormData.value.date || !editFormData.value.timeSlot) {
        return toast.error('Vui lòng chọn ngày và khung giờ');
    }

    buttonLoading.value = true;
    try {
        await $api(`/schedules/${editFormData.value.id}`, {
            method: 'POST',
            headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value },
            body: {
                date: editFormData.value.date,
                timeSlot: editFormData.value.timeSlot,
                message: editFormData.value.message,
                _method: 'PATCH'
            }
        });
        toast.success('Cập nhật lịch xem thành công');
        editFormData.value = { id: null, date: '', timeSlot: '', message: '' };
        window.jQuery.magnificPopup.close();
        await fetchSchedules();
    } catch (error) {
        handleBackendError(error, toast);
    } finally {
        buttonLoading.value = false;
    }
};

const closeModal = () => {
    if (window.jQuery && window.jQuery.magnificPopup) {
        window.jQuery.magnificPopup.close();
    }
};

const initChosenSelect = (selectRef, options = {}) => {
    if (!window.jQuery || !window.jQuery.fn.chosen) {
        console.error('jQuery hoặc Chosen không được tải');
        return;
    }
    const $select = window.jQuery(selectRef.value).chosen({
        width: '100%',
        no_results_text: 'Không tìm thấy kết quả',
        ...options
    });
    return $select;
};

const initDatePicker = (elementId, field) => {
    if (!window.jQuery || !window.jQuery.fn.daterangepicker || !window.moment) {
        console.error('jQuery, Moment hoặc Daterangepicker không được tải');
        return;
    }

    const tomorrow = window.moment().add(2, 'days');
    const $datePicker = window.jQuery(`#${elementId}`);
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
            field.value.date = picker.startDate.format('DD/MM/YYYY');
            $datePicker.val(picker.startDate.format('DD/MM/YYYY'));
        })
        .on('cancel.daterangepicker', () => {
            field.value.date = '';
            $datePicker.val('');
        })
        .on('showCalendar.daterangepicker', () => {
            window.jQuery('.daterangepicker').addClass('calendar-animated');
        })
        .on('show.daterangepicker', () => {
            window.jQuery('.daterangepicker').removeClass('calendar-hidden').addClass('calendar-visible');
        })
        .on('hide.daterangepicker', () => {
            window.jQuery('.daterangepicker').removeClass('calendar-visible').addClass('calendar-hidden');
        });

    $datePicker.val(field.value.date || '');
};

const openPopup = async motelId => {
    await fetchRooms(motelId);
    formData.value.room_id = null;

    if (!window.jQuery || !window.jQuery.fn.magnificPopup) {
        console.error('Magnific Popup không được tải');
        return;
    }

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
        closeOnBgClick: false,
        callbacks: {
            open: () => {
                initChosenSelect(roomSelect, { placeholder_text_single: 'Chọn phòng', allow_single_deselect: true });
                initChosenSelect(durationSelect, { disable_search: true });
            }
        }
    });
};

const openEditSchedulePopup = async schedule => {
    editFormData.value = {
        id: schedule.id,
        date: schedule.scheduled_at ? window.moment(schedule.scheduled_at).format('DD/MM/YYYY') : '',
        timeSlot: schedule.scheduled_at ? formatTimeSlot(schedule.scheduled_at) : '',
        message: schedule.message || ''
    };
    console.log(editFormData.value);

    if (!window.jQuery || !window.jQuery.fn.magnificPopup) {
        console.error('Magnific Popup không được tải');
        return;
    }

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
        closeOnBgClick: false,
        callbacks: {
            open: () => {
                initDatePicker('edit-date-picker', editFormData);
                initChosenSelect(timeSelect, { disable_search: true });
                updateChosenSelect(timeSelect, editFormData.value.timeSlot);
            }
        }
    });
};

const formatTimeSlot = scheduledAt => {
    const date = window.moment(scheduledAt);
    const hour = date.hour();
    const minute = date.minute();
    const period = hour >= 12 ? 'chiều' : 'sáng';

    // Thời gian bắt đầu
    const startHour = hour; // Giữ nguyên giờ ở định dạng 24 giờ
    const startMinute = minute.toString().padStart(2, '0');

    // Thời gian kết thúc (thêm 30 phút)
    const endDate = date.clone().add(30, 'minutes');
    const endHour = endDate.hour();
    const endMinute = endDate.minute().toString().padStart(2, '0');
    const endPeriod = endHour >= 12 ? 'chiều' : 'sáng';

    return `${startHour}:${startMinute} ${period} - ${endHour}:${endMinute} ${endPeriod}`;
};

onMounted(() => {
    fetchSchedules();
    nextTick(() => {
        initDatePicker('date-picker', formData);
        initChosenSelect(roomSelect, { placeholder_text_single: 'Chọn phòng', allow_single_deselect: true }).on('change', event => {
            formData.value.room_id = event.target.value;
        });
        initChosenSelect(durationSelect, { disable_search: true }).on('change', event => {
            formData.value.duration = event.target.value;
        });
        initChosenSelect(timeSelect, { placeholder_text_single: 'Chọn khung giờ', disable_search: true }).on('change', event => {
            editFormData.value.timeSlot = event.target.value;
        });
    });
});

const updateChosenSelect = (selectRef, value = null) => {
    nextTick(() => {
        if (window.jQuery && selectRef.value) {
            const $select = window.jQuery(selectRef.value);
            if ($select.data('chosen')) {
                $select.trigger('chosen:updated');
                if (value !== null) {
                    $select.val(value).trigger('chosen:updated');
                }
            }
        }
    });
};

watch(
    rooms,
    () => {
        updateChosenSelect(roomSelect);
    },
    { deep: true }
);
</script>

<style scoped>
@import '~/public/css/viewing-schedules.css';
</style>
