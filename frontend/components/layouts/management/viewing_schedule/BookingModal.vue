<template>
    <!-- Modal đặt phòng -->
    <div id="small-dialog" class="zoom-anim-dialog mfp-hide">
        <div class="small-dialog-header" style="margin-bottom: 0">
            <h3>Đặt phòng trọ</h3>
            <p class="booking-subtitle">Vui lòng điền đầy đủ thông tin để đặt phòng.</p>
        </div>
        <div class="message-reply margin-top-0">
            <!-- Liên kết đến điều khoản hợp đồng -->
            <a
                style="text-decoration: underline !important; color: #007bff; margin: 10px 0 20px; display: inline-block"
                href="/dieu-khoan-hop-dong"
                target="_blank"
                class="terms-link"
                >Xem điều khoản hợp đồng</a
            >
            <!-- Form đặt phòng -->
            <div class="booking-form-grid">
                <div class="form-row">
                    <!-- Chọn phòng -->
                    <div class="form-col">
                        <label><i class="fa fa-bed"></i> Phòng:</label>
                        <select v-model="formData.room_id" class="modal-room-select" ref="roomSelect">
                            <option value="">Chọn phòng</option>
                            <option v-for="room in rooms" :key="room.id" :value="room.id">{{ room.name }}</option>
                        </select>
                    </div>
                    <!-- Chọn ngày bắt đầu -->
                    <div class="form-col">
                        <label><i class="fa fa-calendar"></i> Ngày bắt đầu:</label>
                        <div class="date-input-container">
                            <input type="text" id="date-picker" placeholder="Chọn ngày bắt đầu" readonly="readonly" />
                        </div>
                    </div>
                    <!-- Chọn thời gian thuê -->
                    <div class="form-col">
                        <label><i class="fa fa-clock-o"></i> Thời gian thuê:</label>
                        <select v-model="formData.duration" class="modal-duration-select" ref="durationSelect">
                            <option value="">Chọn thời gian</option>
                            <option v-for="duration in durations" :value="duration">{{ duration }}</option>
                        </select>
                    </div>
                </div>
            </div>
            <!-- Ghi chú -->
            <div class="booking-note-section">
                <label><i class="fa fa-sticky-note"></i> Ghi chú:</label>
                <textarea v-model="formData.note" cols="40" rows="3" placeholder="Thêm ghi chú (không bắt buộc)..."></textarea>
            </div>
            <!-- Các nút hành động -->
            <div class="booking-actions">
                <button @click="closeModal" class="button gray" type="button"><i class="fa fa-times"></i> Hủy</button>
                <button @click.prevent="submitBooking" class="button" :disabled="localButtonLoading">
                    <span v-if="localButtonLoading" class="spinner"></span>
                    <i v-else class="fa fa-check"></i>
                    {{ localButtonLoading ? 'Đang xử lý...' : 'Đặt phòng' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import { useAppToast } from '~/composables/useToast';
import { useApi } from '~/composables/useApi';
import { useRouter } from 'vue-router';

const props = defineProps({
    rooms: { type: Array, required: true }, // Danh sách phòng
    durations: { type: Array, required: true }, // Danh sách thời gian thuê
    formData: { type: Object, required: true } // Dữ liệu form đặt phòng
});

const emit = defineEmits(['update:formData', 'close', 'submit', 'update:buttonLoading']); // Các sự kiện emit

const { $api } = useNuxtApp(); // Lấy instance của API
const { handleBackendError } = useApi(); // Hàm xử lý lỗi backend
const toast = useAppToast(); // Hàm hiển thị thông báo
const router = useRouter(); // Router của Vue

const roomSelect = ref(null); // Ref cho select phòng
const durationSelect = ref(null); // Ref cho select thời gian thuê
const localButtonLoading = ref(false); // Trạng thái loading của nút

// Hàm gửi yêu cầu đặt phòng
const submitBooking = async () => {
    // Kiểm tra dữ liệu bắt buộc
    if (!props.formData.room_id || !props.formData.date || !props.formData.duration) {
        return toast.error('Vui lòng chọn phòng, ngày bắt đầu và thời gian thuê');
    }

    localButtonLoading.value = true; // Bật trạng thái loading
    emit('update:buttonLoading', true); // Emit sự kiện loading
    try {
        await $api('/bookings', {
            method: 'POST',
            headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value },
            body: {
                room_id: props.formData.room_id,
                start_date: props.formData.date,
                duration: props.formData.duration,
                note: props.formData.note
            }
        });
        toast.success('Đặt phòng thành công'); // Thông báo thành công
        emit('update:formData', { room_id: null, date: '', duration: '', note: '' }); // Reset form
        emit('close'); // Đóng modal
        router.push('/quan-ly/dat-phong'); // Chuyển hướng đến trang quản lý đặt phòng
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
            emit('update:formData', {
                ...props.formData,
                date: picker.startDate.format('DD/MM/YYYY') // Cập nhật ngày được chọn
            });
            $datePicker.val(picker.startDate.format('DD/MM/YYYY'));
        })
        .on('cancel.daterangepicker', () => {
            emit('update:formData', { ...props.formData, date: '' }); // Reset ngày
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

    $datePicker.val(props.formData.date || ''); // Gán giá trị ban đầu
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
        console.log('BookingModal mounted, rooms:', props.rooms); // Log danh sách phòng
        initDatePicker(); // Khởi tạo datepicker
        initChosenSelect(roomSelect, { placeholder_text_single: 'Chọn phòng', allow_single_deselect: true }).on('change', event => {
            emit('update:formData', { ...props.formData, room_id: event.target.value }); // Cập nhật room_id
        });
        initChosenSelect(durationSelect, { disable_search: true }).on('change', event => {
            emit('update:formData', { ...props.formData, duration: event.target.value }); // Cập nhật duration
        });
        updateChosenSelect(roomSelect, props.formData.room_id); // Cập nhật select phòng
        updateChosenSelect(durationSelect, props.formData.duration); // Cập nhật select thời gian thuê
    });
});

// Theo dõi thay đổi danh sách phòng
watch(
    () => props.rooms,
    newRooms => {
        console.log('Rooms updated:', newRooms); // Log khi danh sách phòng thay đổi
        nextTick(() => {
            updateChosenSelect(roomSelect, props.formData.room_id); // Cập nhật select phòng
        });
    },
    { deep: true, immediate: true }
);
</script>

<style scoped>
@import '~/public/css/modal.css'; /* Import CSS cho modal */
</style>
