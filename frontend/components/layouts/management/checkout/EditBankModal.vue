<template>
    <div id="edit-schedule-dialog" class="zoom-anim-dialog mfp-hide">
        <div class="small-dialog-header">
            <h3>Chỉnh sửa thông tin chuyển khoản</h3>
            <p class="booking-subtitle">Vui lòng cập nhật thông tin tài khoản ngân hàng</p>
        </div>
        <div class="message-reply margin-top-0">
            <div class="booking-form-grid">
                <div class="form-row">
                    <div class="form-col">
                        <p><strong>Số hợp đồng:</strong> {{ checkout?.contract_id }}</p>
                        <p><strong>Tiền cọc:</strong> {{ formatPrice(checkout?.contract?.deposit_amount) }}</p>
                        <hr />
                        <h5>
                            <strong
                                ><em>Thông tin tài khoản ngân hàng <span class="text-danger">(*)</span></em></strong
                            >
                        </h5>
                        <label><i class="fa fa-university"></i> Ngân hàng thụ hưởng:</label>
                        <select id="bank_name" v-model="editBankForm.bank_name" class="modal-bank-select" ref="bankSelect" required>
                            <option value="">Chọn ngân hàng</option>
                            <option v-for="bank in banks" :key="bank.value" :value="bank.value">
                                {{ bank.label }}
                            </option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <label><i class="fa fa-credit-card"></i> Số tài khoản:</label>
                        <input
                            id="account_number"
                            v-model="editBankForm.account_number"
                            type="text"
                            class="form-control"
                            placeholder="Số tài khoản"
                            required
                        />
                    </div>
                    <div class="form-col">
                        <label><i class="fa fa-user"></i> Tên chủ tài khoản:</label>
                        <input
                            id="account_holder"
                            v-model="editBankForm.account_holder"
                            type="text"
                            class="form-control"
                            placeholder="Tên chủ tài khoản"
                            required
                        />
                    </div>
                </div>
            </div>
            <div class="booking-actions">
                <button @click="emit('close')" class="button gray" type="button"><i class="fa fa-times"></i> Hủy</button>
                <button @click="handleUpdateBankInfo" class="button" :disabled="updateLoading">
                    <span v-if="updateLoading" class="spinner"></span>
                    <i v-else class="fa fa-check"></i>
                    {{ updateLoading ? 'Đang cập nhật...' : 'Cập nhật thông tin' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick, onUnmounted } from 'vue';
import { useToast } from 'vue-toastification';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';
import { useFormatPrice } from '~/composables/useFormatPrice';

const { formatPrice } = useFormatPrice();
const toast = useToast();

const props = defineProps({
    checkout: { type: Object, required: true },
    banks: { type: Array, required: true },
    updateLoading: { type: Boolean, default: false }
});

const emit = defineEmits(['close', 'update-bank-info']);

const editBankForm = ref({
    bank_name: '',
    account_number: '',
    account_holder: ''
});

const bankSelect = ref(null);
let tomSelectInstance = null;

watch(
    () => props.checkout,
    newCheckout => {
        editBankForm.value = {
            bank_name: newCheckout?.bank_info?.bank_name || '',
            account_number: newCheckout?.bank_info?.account_number || '',
            account_holder: newCheckout?.bank_info?.account_holder || ''
        };
        if (tomSelectInstance && editBankForm.value.bank_name) {
            tomSelectInstance.setValue(editBankForm.value.bank_name);
        }
    },
    { immediate: true }
);

const validateBankForm = () => {
    const form = editBankForm.value;
    return form.bank_name && form.account_number && form.account_holder;
};

const handleUpdateBankInfo = async () => {
    if (!validateBankForm()) {
        toast.error('Vui lòng nhập đầy đủ và đúng định dạng thông tin tài khoản ngân hàng.');
        return;
    }
    emit('update-bank-info', {
        id: props.checkout.id,
        bankInfo: { ...editBankForm.value }
    });
};

const initTomSelect = () => {
    if (!bankSelect.value) return;
    tomSelectInstance = new TomSelect(bankSelect.value, {
        plugins: ['dropdown_input'],
        valueField: 'value',
        labelField: 'label',
        searchField: ['label'],
        options: props.banks,
        render: {
            option: (data, escape) => `
                <span style="display: flex; align-items: center;">
                    <img style="max-width: 79px; margin-right: 8px; border-radius: 4px;" 
                         src="${escape(data.logo || '')}" 
                         alt="${escape(data.label)} logo" 
                         onerror="this.style.display='none'"/>
                    <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: calc(100% - 87px);">
                        ${escape(data.label)}
                    </span>
                </span>`,
            item: (data, escape) => `
                <span style="display: flex; align-items: center;">
                    <img style="max-width: 79px; margin-right: 8px; border-radius: 4px;" 
                         src="${escape(data.logo || '')}" 
                         alt="${escape(data.label)} logo" 
                         onerror="this.style.display='none'"/>
                    <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: calc(100% - 87px);">
                        ${escape(data.label)}
                    </span>
                </span>`,
            no_results: () => '<div class="no-results">Không tìm thấy ngân hàng</div>'
        },
        onChange: value => {
            editBankForm.value.bank_name = value;
        }
    });
    if (editBankForm.value.bank_name) {
        tomSelectInstance.setValue(editBankForm.value.bank_name);
    }
};

onMounted(() => {
    nextTick(() => {
        initTomSelect();
    });
});

onUnmounted(() => {
    if (tomSelectInstance) {
        tomSelectInstance.destroy();
        tomSelectInstance = null;
    }
});
</script>

<style scoped>
@import '~/public/css/viewing-schedules.css';

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

.form-col input {
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

.form-col input:focus {
    border-color: #f91942;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    transform: translateY(-1px);
}

.form-col input:hover {
    border-color: #cbd5e0;
}

/* Tùy chỉnh style cho TomSelect */
.tom-select .ts-control {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 16px;
    font-size: 14px;
    background: #ffffff;
    transition: all 0.3s ease;
}

.tom-select .ts-control:hover {
    border-color: #cbd5e0;
}

.tom-select .ts-control:focus-within {
    border-color: #f91942;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    transform: translateY(-1px);
}

.tom-select .ts-dropdown {
    border: 2px solid #f91942;
    border-radius: 8px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    margin-top: 4px;
}
</style>
