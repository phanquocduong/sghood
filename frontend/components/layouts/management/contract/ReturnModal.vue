<template>
    <div class="custom-modal-overlay">
        <div class="custom-modal">
            <div class="custom-modal-header">
                <h3>Xác nhận trả phòng</h3>
                <button class="close-button" @click="emit('close')">×</button>
            </div>
            <div class="custom-modal-body">
                <div class="modal-content">
                    <p><strong>Số hợp đồng:</strong> {{ contract.id }}</p>
                    <p><strong>Phòng:</strong> {{ contract.room_name }} - {{ contract.motel_name }}</p>
                    <p><strong>Ngày kết thúc:</strong> {{ formatDate(contract.end_date) }}</p>
                    <p><strong>Tiền cọc:</strong> {{ formatPrice(contract.deposit_amount) }}</p>
                    <hr />
                    <h5>
                        <strong
                            ><em>Thông tin trả phòng <span class="text-danger">(*)</span></em></strong
                        >
                    </h5>
                    <div class="form-group">
                        <label for="check_out_date" class="form-label">Ngày dự kiến rời phòng:</label>
                        <input
                            id="check_out_date"
                            v-model="returnForm.check_out_date"
                            type="date"
                            :min="today"
                            class="form-control"
                            required
                        />
                    </div>
                    <h5>
                        <strong
                            ><em>Thông tin tài khoản ngân hàng <span class="text-danger">(*)</span></em></strong
                        >
                    </h5>
                    <div class="tom-select-custom form-group">
                        <label class="form-label">Ngân hàng thụ hưởng:</label>
                        <select id="bank_name" v-model="returnForm.bank_name" class="js-select form-select" required>
                            <option value="">Chọn ngân hàng</option>
                            <option
                                v-for="bank in banks"
                                :key="bank.value"
                                :value="bank.value"
                                :data-option-template="`<span style='display: flex; align-items: center;'><img style='max-width: 79px; margin-right: 8px;' src='${bank.logo}' alt='${bank.label} logo' /><span style='overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: calc(100% - 87px);'>${bank.label}</span></span>`"
                            >
                                {{ bank.label }}
                            </option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="account_number" class="form-label">Số tài khoản:</label>
                        <input
                            id="account_number"
                            v-model="returnForm.account_number"
                            type="text"
                            class="form-control"
                            placeholder="Số tài khoản"
                            required
                        />
                    </div>
                    <div class="form-group">
                        <label for="account_holder" class="form-label">Tên chủ tài khoản:</label>
                        <input
                            id="account_holder"
                            v-model="returnForm.account_holder"
                            type="text"
                            class="form-control"
                            placeholder="Tên chủ tài khoản"
                            required
                        />
                    </div>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button class="button gray" @click="emit('close')">Hủy</button>
                <button class="button confirm" @click="handleRequestOTP" :disabled="otpLoading">
                    <span v-if="otpLoading" class="button-spinner"></span>
                    {{ otpLoading ? 'Đang gửi OTP...' : 'Xác nhận trả phòng' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useFormatPrice } from '~/composables/useFormatPrice';
import { useFormatDate } from '~/composables/useFormatDate';

const { formatDate } = useFormatDate();
const { formatPrice } = useFormatPrice();

const props = defineProps({
    contract: {
        type: Object,
        required: true
    },
    banks: {
        type: Array,
        required: true
    },
    today: {
        type: String,
        required: true
    },
    otpLoading: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close', 'request-otp']);

const returnForm = ref({
    check_out_date: '',
    bank_name: '',
    account_number: '',
    account_holder: ''
});

const validateReturnForm = () => {
    const form = returnForm.value;
    return form.check_out_date && form.bank_name && form.account_number && form.account_holder;
};

const handleRequestOTP = () => {
    if (!validateReturnForm()) {
        return;
    }
    emit('request-otp', returnForm.value); // Emit dữ liệu form
};
</script>

<style scoped>
/* Style giữ nguyên từ file gốc */
</style>
