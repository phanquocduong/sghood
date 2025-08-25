<template>
    <!-- Modal chỉnh sửa lịch xem -->
    <div id="edit-schedule-dialog" class="zoom-anim-dialog mfp-hide">
        <div class="small-dialog-header">
            <h3>Chỉnh sửa lịch xem nhà trọ</h3>
            <p class="booking-subtitle">Vui lòng cập nhật thông tin lịch xem</p>
        </div>
        <div class="message-reply margin-top-0">
            <!-- Form chỉnh sửa lịch -->
            <div class="booking-form-grid">
                <div class="form-row">
                    <!-- Chọn ngày xem -->
                    <div class="form-col">
                        <label><i class="fa fa-calendar"></i> Ngày xem:</label>
                        <div class="date-input-container">
                            <input type="text" id="edit-date-picker" placeholder="Chọn ngày xem" readonly="readonly" />
                        </div>
                    </div>
                    <!-- Chọn khung giờ -->
                    <div class="form-col">
                        <label><i class="fa fa-clock-o"></i> Khung giờ:</label>
                        <select v-model="editFormData.timeSlot" class="modal-time-select" ref="timeSelect">
                            <option value="">Chọn khung giờ</option>
                            <option v-for="slot in timeSlots" :value="slot">{{ slot }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <!-- Lời nhắn -->
            <div class="booking-note-section">
                <label><i class="fa fa-sticky-note"></i> Lời nhắn:</label>
                <textarea v-model="editFormData.message" cols="40" rows="3" placeholder="Thêm lời nhắn (không bắt buộc)..."></textarea>
            </div>
            <!-- Các nút hành động -->
            <div class="booking-actions">
                <button @click="closeModal" class="button gray" type="button"><i class="fa fa-times"></i> Hủy</button>
                <button @click.prevent="submitEditSchedule" class="button" :disabled="localButtonLoading">
                    <span v-if="localButtonLoading" class="spinner"></span>
                    <i v-else class="fa fa-check"></i>
                    {{ localButtonLoading ? 'Đang xử lý...' : 'Cập nhật' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import { useAppToast } from '~/composables/useToast';
import { useApi } from '~/composables/useApi';

const props = defineProps({
    timeSlots: { type: Array, required: true }, // Danh sách khung giờ
    editFormData: { type: Object, required: true } // Dữ liệu form chỉnh sửa
});

const emit = defineEmits(['update:editFormData', 'close', 'submit', 'update:buttonLoading']); // Các sự kiện emit

const { $api } = useNuxtApp(); // Lấy instance của API
const { handleBackendError } = useApi(); // Hàm xử lý lỗi backend
const toast = useAppToast(); // Hàm hiển thị thông báo

const timeSelect = ref(null); // Ref cho select khung giờ
const localButtonLoading = ref(false); // Trạng thái loading của nút

// Hàm gửi yêu cầu chỉnh sửa lịch
const submitEditSchedule = async () => {
    // Kiểm tra dữ liệu bắt buộc
    if (!props.editFormData.date || !props.editFormData.timeSlot) {
        return toast.error('Vui lòng chọn ngày và khung giờ');
    }

    localButtonLoading.value = true; // Bật trạng thái loading
    emit('update:buttonLoading', true); // Emit sự kiện loading
    try {
        await $api(`/schedules/${props.editFormData.id}`, {
            method: 'POST',
            headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value },
            body: {
                date: props.editFormData.date,
                timeSlot: props.editFormData.timeSlot,
                message: props.editFormData.message,
                _method: 'PATCH' // Mô phỏng PATCH request
            }
        });
        toast.success('Cập nhật lịch xem thành công'); // Thông báo thành công
        emit('update:editFormData', { id: null, date: '', timeSlot: '', message: '' }); // Reset form
        emit('submit'); // Emit sự kiện submit
        emit('close'); // Đóng modal
    } catch (error) {
        handleBackendError(error, toast); // Xử lý lỗi nếu có
    } finally {
        localButtonLoading.value = false; // Tắt trạng thái loading
        emit('update:buttonLoading', false); // Emit sự kiện tắt loading
    }
};

// Hàm đóng modal
const closeModal = () => {
    emit('close'); // Emit sự kiện đóng modal
};

// Khởi tạo thư viện Chosen cho select box
const initChosenSelect = (selectRef, options = {}) => {
    if (!window.jQuery || !window.jQuery.fn.chosen) {
        console.error('jQuery hoặc Chosen không được tải'); // Báo lỗi nếu thư viện không tải
        return;
    }
    const $select = window.jQuery(selectRef.value).chosen({
        width: '100%',
        no_results_text: 'Không tìm thấy kết quả',
        ...options
    });
    return $select;
};

// Khởi tạo datepicker
const initDatePicker = () => {
    if (!window.jQuery || !window.jQuery.fn.daterangepicker || !window.moment) {
        console.error('jQuery, Moment hoặc Daterangepicker không được tải'); // Báo lỗi nếu thư viện không tải
        return;
    }

    const tomorrow = window.moment().add(2, 'days'); // Ngày tối thiểu là ngày mai
    const $datePicker = window.jQuery('#edit-date-picker');
    $datePicker
        .daterangepicker({
            opens: 'left',
            singleDatePicker: true,
            minDate: tomorrow,
            startDate: props.editFormData.date ? window.moment(props.editFormData.date, 'DD/MM/YYYY') : tomorrow,
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
            const newDate = picker.startDate.format('DD/MM/YYYY');
            emit('update:editFormData', { ...props.editFormData, date: newDate }); // Cập nhật ngày
            $datePicker.val(newDate);
        })
        .on('cancel.daterangepicker', () => {
            emit('update:editFormData', { ...props.editFormData, date: '' }); // Reset ngày
            $datePicker.val('');
        })
        .on('showCalendar.daterangepicker', () => {
            window.jQuery('.daterangepicker').addClass('calendar-animated'); // Thêm hiệu ứng cho calendar
        })
        .on('show.daterangepicker', () => {
            window.jQuery('.daterangepicker').removeClass('calendar-hidden').addClass('calendar-visible'); // Hiển thị calendar
        })
        .on('hide.daterangepicker', () => {
            window.jQuery('.daterangepicker').removeClass('calendar-visible').addClass('calendar-hidden'); // Ẩn calendar
        });

    if (props.editFormData.date) {
        $datePicker.val(props.editFormData.date); // Gán giá trị ban đầu
    }
};

// Hàm cập nhật select box Chosen
const updateChosenSelect = (selectRef, value = null) => {
    if (window.jQuery && selectRef.value) {
        const $select = window.jQuery(selectRef.value);
        if ($select.data('chosen')) {
            $select.val(value).trigger('chosen:updated'); // Cập nhật giá trị select
        }
    }
};

// Khởi tạo khi component được mount
onMounted(() => {
    nextTick(() => {
        initDatePicker(); // Khởi tạo datepicker
        initChosenSelect(timeSelect, { placeholder_text_single: 'Chọn khung giờ', disable_search: true }).on('change', event => {
            emit('update:editFormData', { ...props.editFormData, timeSlot: event.target.value }); // Cập nhật khung giờ
        });
        updateChosenSelect(timeSelect, props.editFormData.timeSlot); // Cập nhật select khung giờ
    });
});

// Theo dõi thay đổi dữ liệu form chỉnh sửa
watch(
    () => props.editFormData,
    newData => {
        nextTick(() => {
            updateChosenSelect(timeSelect, newData.timeSlot); // Cập nhật select khung giờ
            const $datePicker = window.jQuery('#edit-date-picker');
            if (newData.date && window.moment(newData.date, 'DD/MM/YYYY').isValid()) {
                $datePicker.data('daterangepicker').setStartDate(window.moment(newData.date, 'DD/MM/YYYY')); // Cập nhật datepicker
                $datePicker.val(newData.date);
            } else {
                $datePicker.val('');
            }
        });
    },
    { deep: true }
);
</script>

<style scoped>
@import '~/public/css/modal.css'; /* Import CSS cho modal */
</style>
