<!-- ExtendModal.vue -->
<template>
    <div id="small-dialog" class="zoom-anim-dialog mfp-hide">
        <div class="small-dialog-header">
            <h3>Gia hạn hợp đồng</h3>
            <p class="booking-subtitle">Vui lòng chọn thời gian gia hạn</p>
        </div>
        <div class="message-reply margin-top-0">
            <div class="booking-form-grid">
                <div class="form-row">
                    <div class="form-col">
                        <p><strong>Số hợp đồng:</strong> {{ contract.id }}</p>
                        <p><strong>Phòng:</strong> {{ contract.room_name }} - {{ contract.motel_name }}</p>
                        <p><strong>Ngày kết thúc hiện tại:</strong> {{ formatDate(contract.end_date) }}</p>
                        <hr />
                        <h5>
                            <strong
                                ><em>Thông tin gia hạn <span class="text-danger">(*)</span></em></strong
                            >
                        </h5>
                        <label><i class="fa fa-clock-o"></i> Thời gian gia hạn (tháng):</label>
                        <select v-model.number="extendForm.months" class="modal-duration-select" ref="durationSelect" required>
                            <option value="" disabled>Chọn thời gian</option>
                            <option v-for="month in [1, 3, 6, 12, 24]" :value="month">{{ month }} tháng</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <p><strong>Ngày kết thúc mới:</strong> {{ formatDate(calculatedNewEndDate) }}</p>
                        <p><strong>Giá thuê phòng mới:</strong> {{ formatPrice(contract.room_price) }}</p>
                        <p>Các điều khoản khác của hợp đồng gốc vẫn giữ nguyên hiệu lực.</p>
                    </div>
                </div>
            </div>
            <div class="booking-actions">
                <button @click="emit('close')" class="button gray" type="button"><i class="fa fa-times"></i> Hủy</button>
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
import { useToast } from 'vue-toastification';
import { useFormatPrice } from '~/composables/useFormatPrice';
import { useFormatDate } from '~/composables/useFormatDate';

const { formatDate } = useFormatDate();
const { formatPrice } = useFormatPrice();
const toast = useToast();

const props = defineProps({
    contract: {
        type: Object,
        required: true
    },
    otpLoading: { type: Boolean, default: false }
});

const emit = defineEmits(['close', 'confirm']);
const extendForm = ref({
    months: 6
});
const durationSelect = ref(null);

const isExtendFormValid = computed(() => extendForm.value.months >= 1);

const calculatedNewEndDate = computed(() => {
    if (!props.contract.end_date || !extendForm.value.months) return '';
    const currentEndDate = new Date(props.contract.end_date);
    return new Date(currentEndDate.setMonth(currentEndDate.getMonth() + extendForm.value.months));
});

const handleConfirm = () => {
    if (!isExtendFormValid.value) {
        toast.error('Vui lòng chọn thời gian gia hạn hợp lệ.');
        return;
    }
    emit('confirm', extendForm.value.months);
};

const initChosenSelect = () => {
    if (!window.jQuery || !window.jQuery.fn.chosen) {
        console.error('jQuery hoặc Chosen không được tải');
        return;
    }
    window
        .jQuery(durationSelect.value)
        .chosen({
            width: '100%',
            no_results_text: 'Không tìm thấy kết quả',
            disable_search: true
        })
        .on('change', event => {
            extendForm.value.months = parseInt(event.target.value) || 6;
        });
};

onMounted(() => {
    nextTick(() => {
        initChosenSelect();
    });
});
</script>

<style scoped>
@import '~/public/css/viewing-schedules.css';
</style>
