<!-- ReturnModal.vue -->
<template>
    <div id="edit-schedule-dialog" class="zoom-anim-dialog mfp-hide">
        <div class="small-dialog-header">
            <h3>Xác nhận trả phòng</h3>
            <p class="booking-subtitle">Vui lòng nhập thông tin để hoàn tất thủ tục trả phòng</p>
        </div>
        <div class="message-reply margin-top-0">
            <div class="booking-form-grid">
                <div class="form-row">
                    <div class="form-col">
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
                        <label><i class="fa fa-calendar"></i> Ngày dự kiến rời phòng:</label>
                        <div class="date-input-container">
                            <input
                                id="date-picker"
                                type="text"
                                class="form-control"
                                placeholder="Chọn ngày trả phòng"
                                readonly="readonly"
                            />
                        </div>
                        <small class="text-muted">
                            Vui lòng chọn ngày từ ngày mai đến tối đa 30 ngày sau ngày kết thúc hợp đồng ({{
                                formatDate(contract.end_date)
                            }}).
                        </small>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <h5>
                            <strong
                                ><em>Thông tin tài khoản ngân hàng <span class="text-danger">(*)</span></em></strong
                            >
                        </h5>
                        <label><i class="fa fa-university"></i> Ngân hàng thụ hưởng:</label>
                        <select v-model="returnForm.bank_name" id="bank_name" class="modal-bank-select" ref="bankSelect" required>
                            <option value="">Chọn ngân hàng</option>
                            <option v-for="bank in banks" :value="bank.value" :key="bank.value">{{ bank.label }}</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-col">
                        <label><i class="fa fa-credit-card"></i> Số tài khoản:</label>
                        <input
                            v-model="returnForm.account_number"
                            type="text"
                            class="form-control"
                            placeholder="Số tài khoản"
                            @input="validateAccountNumber"
                            required
                        />
                    </div>
                    <div class="form-col">
                        <label><i class="fa fa-user"></i> Tên chủ tài khoản:</label>
                        <input
                            v-model="returnForm.account_holder"
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
                <button @click="handleRequestOTP" class="button" :disabled="otpLoading || !validateReturnForm()">
                    <span v-if="otpLoading" class="spinner"></span>
                    <i v-else class="fa fa-check"></i>
                    {{ otpLoading ? 'Đang gửi OTP...' : 'Xác nhận trả phòng' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick, onUnmounted, watch } from 'vue';
import { useToast } from 'vue-toastification';
import { useFormatPrice } from '~/composables/useFormatPrice';
import { useFormatDate } from '~/composables/useFormatDate';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';

const { formatDate } = useFormatDate();
const { formatPrice } = useFormatPrice();
const toast = useToast();

const props = defineProps({
    contract: { type: Object, required: true },
    banks: { type: Array, required: true },
    today: { type: String, required: true },
    otpLoading: { type: Boolean, default: false }
});

const emit = defineEmits(['close', 'request-otp']);

const returnForm = ref({
    check_out_date: '',
    bank_name: '',
    account_number: '',
    account_holder: ''
});

const bankSelect = ref(null);
let tomSelectInstance = null;

const validateReturnForm = () => {
    const form = returnForm.value;
    return form.check_out_date && form.bank_name && form.account_number && form.account_holder;
};

const validateAccountNumber = () => {
    returnForm.value.account_number = returnForm.value.account_number.replace(/[^0-9]/g, '');
};

const handleRequestOTP = () => {
    if (!validateReturnForm()) {
        toast.error('Vui lòng nhập đầy đủ và đúng định dạng thông tin trả phòng và tài khoản ngân hàng.');
        return;
    }
    emit('request-otp', returnForm.value);
};

const initDatePicker = () => {
    if (!window.jQuery || !window.jQuery.fn.daterangepicker || !window.moment) {
        console.error('jQuery, Moment hoặc Daterangepicker không được tải');
        return;
    }

    const initialize = () => {
        const tomorrow = window.moment().add(1, 'days');
        const maxDate = window.moment(props.contract.end_date, 'YYYY-MM-DD').clone().add(30, 'days');
        window
            .jQuery('#date-picker')
            .daterangepicker({
                opens: 'left',
                singleDatePicker: true,
                minDate: tomorrow,
                maxDate: maxDate,
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
                returnForm.value.check_out_date = picker.startDate.format('DD/MM/YYYY');
            })
            .on('cancel.daterangepicker', () => {
                returnForm.value.check_out_date = '';
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
    };

    // Khởi tạo ngay nếu props.contract có sẵn
    if (props.contract && props.contract.end_date) {
        nextTick(() => {
            initialize();
        });
    }

    // Theo dõi props.contract để khởi tạo khi dữ liệu có sẵn
    watch(
        () => props.contract,
        newContract => {
            if (newContract && newContract.end_date) {
                nextTick(() => {
                    initialize();
                });
            }
        },
        { immediate: false }
    );
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
            returnForm.value.bank_name = value;
        }
    });
};

onMounted(() => {
    nextTick(() => {
        initDatePicker();
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
