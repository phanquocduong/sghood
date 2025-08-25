<template>
    <!-- Modal gia hạn hợp đồng -->
    <div id="small-dialog" class="zoom-anim-dialog mfp-hide">
        <div class="small-dialog-header">
            <h3>Gia hạn hợp đồng</h3>
            <!-- Tiêu đề modal -->
            <p class="booking-subtitle">Vui lòng chọn thời gian gia hạn</p>
            <!-- Phụ đề -->
        </div>
        <div class="message-reply margin-top-0">
            <div class="booking-form-grid">
                <div class="form-row">
                    <div class="form-col">
                        <!-- Thông tin hợp đồng -->
                        <p><strong>Số hợp đồng:</strong> {{ contract.id }}</p>
                        <p><strong>Phòng:</strong> {{ contract.room_name }} - {{ contract.motel_name }}</p>
                        <p><strong>Ngày kết thúc hiện tại:</strong> {{ formatDate(contract.end_date) }}</p>
                        <hr />
                        <h5>
                            <strong
                                ><em>Thông tin gia hạn <span class="text-danger">(*)</span></em></strong
                            >
                            <!-- Tiêu đề thông tin gia hạn -->
                        </h5>
                        <label><i class="fa fa-clock-o"></i> Thời gian gia hạn (tháng):</label>
                        <select v-model.number="extendForm.months" class="modal-duration-select" ref="durationSelect" required>
                            <option value="" disabled>Chọn thời gian</option>
                            <option v-for="month in monthOptions" :key="month" :value="month">{{ month }} tháng</option>
                        </select>
                        <!-- Select box chọn thời gian gia hạn -->
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <!-- Thông tin gia hạn -->
                        <p><strong>Ngày kết thúc mới:</strong> {{ formatDate(calculatedNewEndDate) }}</p>
                        <p><strong>Giá thuê phòng mới:</strong> {{ formatPrice(contract.room_price) }}</p>
                        <p>Các điều khoản khác của hợp đồng gốc vẫn giữ nguyên hiệu lực.</p>
                    </div>
                </div>
            </div>
            <div class="booking-actions">
                <!-- Nút hủy -->
                <button @click="emit('close')" class="button gray" type="button"><i class="fa fa-times"></i> Hủy</button>
                <!-- Nút xác nhận gia hạn -->
                <button @click="handleConfirm" class="button" :disabled="otpLoading || !isExtendFormValid">
                    <span v-if="otpLoading" class="spinner"></span>
                    <i v-else class="fa fa-check"></i>
                    {{ otpLoading ? 'Đang gửi OTP...' : 'Xác nhận gia hạn' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { useAppToast } from '~/composables/useToast';
import { useFormatPrice } from '~/composables/useFormatPrice';
import { useFormatDate } from '~/composables/useFormatDate';

// Lấy composables
const { formatDate } = useFormatDate();
const { formatPrice } = useFormatPrice();
const toast = useAppToast();
const config = useState('configs');

// Định nghĩa props
const props = defineProps({
    contract: {
        type: Object,
        required: true // Thông tin hợp đồng
    },
    otpLoading: { type: Boolean, default: false } // Trạng thái loading OTP
});

// Định nghĩa emits
const emit = defineEmits(['close', 'confirm']);

// Khởi tạo các biến reactive
const monthOptions = ref([]); // Danh sách tùy chọn tháng gia hạn
const extendForm = ref({
    months: 6 // Số tháng gia hạn mặc định
});
const durationSelect = ref(null); // Ref cho select box

// Kiểm tra tính hợp lệ của form gia hạn
const isExtendFormValid = computed(() => extendForm.value.months >= 1);

// Tính ngày kết thúc mới
const calculatedNewEndDate = computed(() => {
    if (!props.contract.end_date || !extendForm.value.months) return '';
    const currentEndDate = new Date(props.contract.end_date);
    return new Date(currentEndDate.setMonth(currentEndDate.getMonth() + extendForm.value.months)); // Tính ngày kết thúc mới
});

// Xử lý xác nhận gia hạn
const handleConfirm = () => {
    if (!isExtendFormValid.value) {
        toast.error('Vui lòng chọn thời gian gia hạn hợp lệ.'); // Thông báo lỗi nếu form không hợp lệ
        return;
    }
    emit('confirm', extendForm.value.months); // Emit sự kiện xác nhận gia hạn
};

// Khởi tạo Chosen select
const initChosenSelect = () => {
    if (!window.jQuery || !window.jQuery.fn.chosen) {
        console.error('jQuery hoặc Chosen không được tải'); // Báo lỗi nếu thư viện không tải
        return;
    }
    window
        .jQuery(durationSelect.value)
        .chosen({
            width: '100%',
            no_results_text: 'Không tìm thấy kết quả',
            disable_search: true // Tắt tìm kiếm trong select
        })
        .on('change', event => {
            extendForm.value.months = parseInt(event.target.value) || 6; // Cập nhật số tháng gia hạn
        });
};

// Khởi tạo khi component được mount
onMounted(() => {
    if (config.value?.extend_month_options) {
        monthOptions.value = JSON.parse(config.value.extend_month_options) || []; // Lấy danh sách tháng từ config
    }

    nextTick(() => {
        initChosenSelect(); // Khởi tạo Chosen select
    });
});
</script>

<style scoped>
@import '~/public/css/modal.css'; /* Nhập CSS cho modal */
</style>
