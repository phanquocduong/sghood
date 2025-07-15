<template>
    <div class="custom-modal-overlay">
        <div class="custom-modal">
            <div class="custom-modal-header">
                <h3>Gia hạn hợp đồng</h3>
                <button class="close-button" @click="emit('close')">×</button>
            </div>
            <div class="custom-modal-body">
                <div class="modal-content">
                    <p><strong>Số hợp đồng:</strong> {{ contract.id }}</p>
                    <p><strong>Phòng:</strong> {{ contract.room_name }} - {{ contract.motel_name }}</p>
                    <p><strong>Ngày kết thúc hiện tại:</strong> {{ formatDate(contract.end_date) }}</p>
                    <hr />
                    <h5>
                        <strong
                            ><em>Thông tin gia hạn <span class="text-danger">(*)</span></em></strong
                        >
                    </h5>
                    <div class="form-group">
                        <label for="extension_months" class="form-label">Thời gian gia hạn:</label>
                        <select id="extension_months" v-model.number="extendForm.months" class="form-control custom-select" required>
                            <option value="" disabled>Chọn thời gian gia hạn</option>
                            <option value="6">6 tháng</option>
                            <option value="12">1 năm</option>
                        </select>
                    </div>
                    <p><strong>Ngày kết thúc mới:</strong> {{ formatDate(calculatedNewEndDate) }}</p>
                    <p><strong>Giá thuê phòng mới:</strong> {{ formatPrice(contract.room_price) }}đ</p>
                    <p>Các điều khoản khác của hợp đồng gốc vẫn giữ nguyên hiệu lực.</p>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button class="button gray" @click="emit('close')">Hủy</button>
                <button class="button confirm" @click="handleConfirm" :disabled="!isExtendFormValid">Xác nhận gia hạn</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useFormatPrice } from '~/composables/useFormatPrice';
import { useFormatDate } from '~/composables/useFormatDate';

const { formatDate } = useFormatDate();
const { formatPrice } = useFormatPrice();

const props = defineProps({
    contract: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['close', 'confirm']);

const extendForm = ref({
    months: 6
});

const isExtendFormValid = computed(() => extendForm.value.months >= 1);

const calculatedNewEndDate = computed(() => {
    if (!props.contract.end_date || !extendForm.value.months) return '';
    const currentEndDate = new Date(props.contract.end_date);
    return new Date(currentEndDate.setMonth(currentEndDate.getMonth() + extendForm.value.months));
});

const handleConfirm = () => {
    if (!isExtendFormValid.value) {
        return;
    }
    emit('confirm', extendForm.value.months); // Emit months
};
</script>

<style scoped>
/* Style giữ nguyên từ file gốc */
</style>
