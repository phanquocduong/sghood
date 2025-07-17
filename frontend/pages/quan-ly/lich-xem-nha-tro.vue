```vue
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
                        :items="schedules"
                        :is-loading="isLoading"
                        @reject-item="rejectSchedule"
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

const rejectSchedule = async id => {
    isLoading.value = true;
    try {
        await $api(`/schedules/${id}/reject`, {
            method: 'POST',
            headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value }
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
/* Enhanced Modal Header */
.small-dialog-header {
    background: linear-gradient(135deg, #f91942 0%, #ff5f7e 100%);
    padding: 25px 30px;
    color: white;
    position: relative;
    overflow: hidden;
}

.small-dialog-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Ccircle cx='30' cy='30' r='4'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")
        repeat;
}

.small-dialog-header h3 {
    margin: 0 0 5px 0;
    font-size: 22px;
    font-weight: 600;
    position: relative;
    z-index: 1;
    color: white;
    font-weight: bolder;
}

.booking-subtitle {
    margin: 0;
    opacity: 0.9;
    font-size: 14px;
    position: relative;
    z-index: 1;
    color: white;
    font-weight: 500;
}

/* Enhanced Form Layout */
.booking-form-grid {
    padding: 0;
}

.form-row {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 20px;
}

.form-col {
    flex: 1;
    min-width: 200px;
}

.form-col label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
    font-size: 14px;
}

.form-col label i {
    color: #f91942;
    font-size: 16px;
}

/* Enhanced Input Styles */
.form-col select,
.date-input-container input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #ffffff;
    outline: none;
    box-sizing: border-box;
}

.form-col select:focus,
.date-input-container input:focus {
    border-color: #f91942;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    transform: translateY(-1px);
}

.form-col select:hover,
.date-input-container input:hover {
    border-color: #cbd5e0;
}

.date-input-container {
    position: relative;
}

.date-input-container::after {
    content: '\e075';
    font-family: 'Simple-Line-Icons';
    font-style: normal;
    font-weight: normal;
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #a0aec0;
    pointer-events: none;
}

/* Note Section */
.booking-note-section {
    margin-top: 10px;
    margin-bottom: 20px;
}

.booking-note-section label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
    font-size: 14px;
}

.booking-note-section label i {
    color: #f91942;
    font-size: 16px;
}

.booking-note-section textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #ffffff;
    outline: none;
    resize: vertical;
    min-height: 80px;
    font-family: inherit;
    box-sizing: border-box;
}

.booking-note-section textarea:focus {
    border-color: #f91942;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    transform: translateY(-1px);
}

.booking-note-section textarea:hover {
    border-color: #cbd5e0;
}

/* Enhanced Button Actions */
.booking-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}

.button {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 120px;
    justify-content: center;
    text-decoration: none;
}

.button.gray {
    background: #f7fafc;
    color: #4a5568;
    border: 2px solid #e2e8f0;
}

.button.gray:hover {
    background: #edf2f7;
    border-color: #cbd5e0;
    transform: translateY(-1px);
}

.button:not(.gray) {
    background: linear-gradient(135deg, #f91942 0%, #ff5f7e 100%);
    color: white;
    border: 2px solid transparent;
}

.button:not(.gray):hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Spinner */
.spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: #ffffff;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Enhanced Chosen Select Styles */
.chosen-container {
    border-radius: 8px !important;
}

.chosen-container-single .chosen-single {
    border: 2px solid #e2e8f0 !important;
    border-radius: 8px !important;
    padding: 12px 16px !important;
    height: auto !important;
    line-height: 1.4 !important;
    background: #ffffff !important;
    transition: all 0.3s ease !important;
}

.chosen-container-single .chosen-single:hover {
    border-color: #cbd5e0 !important;
}

.chosen-container-active .chosen-single {
    border-color: #f91942 !important;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1) !important;
    transform: translateY(-1px) !important;
}

.chosen-container .chosen-drop {
    border: 2px solid #f91942 !important;
    border-radius: 8px !important;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1) !important;
    margin-top: 4px !important;
}

/* Calendar Styles */
.calendar-animated {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.calendar-visible {
    opacity: 1;
    transform: translateY(0);
}

.calendar-hidden {
    opacity: 0;
    transform: translateY(-10px);
}

/* Responsive Design */
@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
        gap: 15px;
    }

    .form-col {
        min-width: auto;
    }

    .small-dialog-header {
        padding: 20px;
    }

    .booking-actions {
        flex-direction: column;
        gap: 10px;
    }

    .button {
        width: 100%;
    }
}

/* Date picker input specific styles */
input#date-picker,
input#edit-date-picker {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #ffffff;
    outline: none;
    box-sizing: border-box;
    cursor: pointer;
}

input#edit-date-picker {
    border: 2px solid #e2e8f0;
}

input#date-picker:focus,
input#edit-date-picker:focus {
    border-color: #f91942;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    transform: translateY(-1px);
}

input#date-picker:hover,
input#edit-date-picker:hover {
    border-color: #cbd5e0;
}
</style>
