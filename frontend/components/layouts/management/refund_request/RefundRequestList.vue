<template>
    <h4>Quản lý yêu cầu hoàn tiền</h4>

    <!-- Hiển thị loading spinner -->
    <Loading :is-loading="isLoading" />

    <ul v-if="!isLoading">
        <li v-for="item in items" :key="item.id" :class="getItemClass(item.status)">
            <div class="list-box-listing bookings">
                <div class="list-box-listing-content">
                    <div class="inner">
                        <h3>
                            Yêu cầu hoàn tiền #{{ item.id }}
                            <span :class="getStatusClass(item.status)">{{ item.status }}</span>
                        </h3>
                        <div class="inner-booking-list">
                            <h5>Hợp đồng:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">#{{ item.contract_id }}</li>
                            </ul>
                        </div>
                        <div class="inner-booking-list">
                            <h5>Tiền cọc:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatPrice(item.deposit_amount) }}</li>
                            </ul>
                        </div>
                        <div v-if="item.deduction_amount" class="inner-booking-list">
                            <h5>Số tiền khấu trừ:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatPrice(item.deduction_amount) }}</li>
                            </ul>
                        </div>
                        <div v-if="item.final_amount" class="inner-booking-list">
                            <h5>Số tiền hoàn:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatPrice(item.final_amount) }}</li>
                            </ul>
                        </div>
                        <div v-if="item.bank_info" class="inner-booking-list">
                            <h5>Thông tin ngân hàng:</h5>
                            <br />
                            <ul class="booking-list">
                                <li class="highlighted bank-info" v-html="formatBankInfo(item.bank_info)"></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="buttons-to-right">
                <button v-if="item.status === 'Chờ xử lý'" class="button gray approve" @click="openQRModal(item)">
                    <i class="im im-icon-Bank"></i> Kiểm tra thông tin chuyển khoản
                </button>
            </div>
        </li>
        <div v-if="!items.length" class="col-md-12 text-center">
            <p>Chưa có yêu cầu hoàn tiền nào.</p>
        </div>
    </ul>

    <!-- Modal hiển thị mã QR -->
    <div v-if="showQRModal" class="custom-modal-overlay">
        <div class="custom-modal">
            <div class="custom-modal-header">
                <h3>Kiểm tra thông tin chuyển khoản</h3>
                <button class="close-button" @click="closeQRModal">×</button>
            </div>
            <div class="custom-modal-body">
                <div class="modal-content">
                    <p>Vui lòng quét mã QR để kiểm tra thông tin chuyển khoản:</p>
                    <img :src="selectedItem.qr_code_path" alt="Mã QR" style="max-width: 400px; margin: 0 auto; display: block" />
                    <p v-if="selectedItem.bank_info" class="mt-3">
                        <strong>Thông tin ngân hàng:</strong>
                        <br />
                        <span v-html="formatBankInfo(selectedItem.bank_info)"></span>
                    </p>
                    <p class="mt-3"><em>Nếu thông tin không đúng, vui lòng chỉnh sửa:</em></p>
                    <button class="button gray" @click="openEditBankModal">Chỉnh sửa thông tin chuyển khoản</button>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button class="button gray" @click="closeQRModal">Đóng</button>
            </div>
        </div>
    </div>

    <!-- Modal chỉnh sửa thông tin ngân hàng -->
    <div v-if="showEditBankModal" class="custom-modal-overlay">
        <div class="custom-modal">
            <div class="custom-modal-header">
                <h3>Chỉnh sửa thông tin chuyển khoản</h3>
                <button class="close-button" @click="closeEditBankModal">×</button>
            </div>
            <div class="custom-modal-body">
                <div class="modal-content">
                    <p><strong>Số hợp đồng:</strong> {{ selectedItem.contract_id }}</p>
                    <p><strong>Tiền cọc:</strong> {{ formatPrice(selectedItem.deposit_amount) }}</p>
                    <hr />
                    <h5>
                        <strong
                            ><em>Thông tin tài khoản ngân hàng <span class="text-danger">(*)</span></em></strong
                        >
                    </h5>
                    <div class="tom-select-custom form-group">
                        <label class="form-label">Ngân hàng thụ hưởng:</label>
                        <select id="bank_name" v-model="editBankForm.bank_name" class="js-select form-select" required>
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
                            v-model="editBankForm.account_number"
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
                            v-model="editBankForm.account_holder"
                            type="text"
                            class="form-control"
                            placeholder="Tên chủ tài khoản"
                            required
                        />
                    </div>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button class="button gray" @click="closeEditBankModal">Hủy</button>
                <button class="button confirm" @click="handleUpdateBankInfo" :disabled="updateLoading">
                    <span v-if="updateLoading" class="spinner"></span>
                    {{ updateLoading ? 'Đang cập nhật...' : 'Cập nhật thông tin' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';
import { useFormatPrice } from '~/composables/useFormatPrice';

const { formatPrice } = useFormatPrice();

const props = defineProps({
    items: {
        type: Array,
        required: true
    },
    isLoading: {
        type: Boolean,
        required: true
    },
    updateLoading: { type: Boolean, default: false },
    showEditBankModal: { type: Boolean, required: true }
});

const emit = defineEmits(['update:showEditBankModal', 'updateBankInfo']);

const selectedItem = ref(null);
const editBankForm = ref({
    bank_name: '',
    account_number: '',
    account_holder: ''
});
const showQRModal = ref(false);
let tomSelectInstance = null;

const formatBankInfo = bankInfo => {
    if (!bankInfo || typeof bankInfo !== 'object') return 'Không có thông tin';
    const fields = [
        bankInfo.bank_name ? `Ngân hàng: ${bankInfo.bank_name}` : '',
        bankInfo.account_number ? `Số tài khoản: ${bankInfo.account_number}` : '',
        bankInfo.account_holder ? `Chủ tài khoản: ${bankInfo.account_holder}` : ''
    ].filter(Boolean);
    return fields.join('<br>'); // Sử dụng <br> để xuống dòng
};

const getItemClass = status => {
    switch (status) {
        case 'Chờ xử lý':
            return 'pending-booking';
        case 'Đã xử lý':
            return 'approved-booking';
        case 'Huỷ bỏ':
            return 'canceled-booking';
        default:
            return '';
    }
};

const getStatusClass = status => {
    let statusClass = 'booking-status';
    switch (status) {
        case 'Chờ xử lý':
            statusClass += ' pending';
            break;
        case 'Đã xử lý':
            statusClass += ' approved';
            break;
        case 'Huỷ bỏ':
            statusClass += ' canceled';
            break;
    }
    return statusClass;
};

const openQRModal = item => {
    selectedItem.value = item;
    showQRModal.value = true;
};

const closeQRModal = () => {
    showQRModal.value = false;
    selectedItem.value = null;
};

const openEditBankModal = () => {
    editBankForm.value = {
        bank_name: selectedItem.value.bank_info?.bank_name || '',
        account_number: selectedItem.value.bank_info?.account_number || '',
        account_holder: selectedItem.value.bank_info?.account_holder || ''
    };
    showQRModal.value = false;
    emit('update:showEditBankModal', true); // Mở modal qua v-model
};

const closeEditBankModal = () => {
    emit('update:showEditBankModal', false); // Đóng modal qua v-model
    selectedItem.value = null;
    editBankForm.value = { bank_name: '', account_number: '', account_holder: '' };
};

const handleUpdateBankInfo = () => {
    emit('updateBankInfo', {
        id: selectedItem.value.id,
        bankInfo: { ...editBankForm.value }
    });
};

watch(
    () => props.showEditBankModal,
    async newValue => {
        if (newValue) {
            await nextTick();
            const selectElement = document.getElementById('bank_name');
            if (selectElement && !selectElement.tomselect) {
                tomSelectInstance = new TomSelect(selectElement, {
                    plugins: ['dropdown_input'],
                    valueField: 'value',
                    labelField: 'label',
                    searchField: ['label'],
                    options: banks.value,
                    render: {
                        option: (data, escape) => `
                        <span style="display: flex; align-items: center;">
                            <img style="max-width: 40px; margin-right: 12px; border-radius: 4px;" 
                                 src="${escape(data.logo || '')}" 
                                 alt="${escape(data.label)} logo" 
                                 onerror="this.style.display='none'"/>
                            <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: calc(100% - 52px);">
                                ${escape(data.label)}
                            </span>
                        </span>`,
                        item: (data, escape) => `
                        <span style="display: flex; align-items: center;">
                            <img style="max-width: 40px; margin-right: 12px; border-radius: 4px;" 
                                 src="${escape(data.logo || '')}" 
                                 alt="${escape(data.label)} logo" 
                                 onerror="this.style.display='none'"/>
                            <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: calc(100% - 52px);">
                                ${escape(data.label)}
                            </span>
                        </span>`,
                        no_results: () => '<div class="no-results">Không tìm thấy kết quả</div>'
                    }
                });
            }
        }
    }
);

const banks = ref([
    { value: 'ACB', label: 'ACB - Ngân hàng TMCP Á Châu', logo: 'https://qr.sepay.vn/assets/img/banklogo/ACB.png' },
    { value: 'VPBank', label: 'VPBank - Ngân hàng TMCP Việt Nam Thịnh Vượng', logo: 'https://qr.sepay.vn/assets/img/banklogo/VPB.png' },
    { value: 'TPBank', label: 'TPBank - Ngân hàng TMCP Tiên Phong', logo: 'https://qr.sepay.vn/assets/img/banklogo/TPB.png' },
    { value: 'MSB', label: 'MSB - Ngân hàng TMCP Hàng Hải', logo: 'https://qr.sepay.vn/assets/img/banklogo/MSB.png' },
    { value: 'NamABank', label: 'NamABank - Ngân hàng TMCP Nam Á', logo: 'https://qr.sepay.vn/assets/img/banklogo/NAB.png' },
    {
        value: 'LienVietPostBank',
        label: 'LienVietPostBank - Ngân hàng TMCP Bưu Điện Liên Việt',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/LPB.png'
    },
    {
        value: 'VietCapitalBank',
        label: 'VietCapitalBank - Ngân hàng TMCP Bản Việt',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/VCCB.png'
    },
    {
        value: 'BIDV',
        label: 'BIDV - Ngân hàng TMCP Đầu tư và Phát triển Việt Nam',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/BIDV.png'
    },
    { value: 'Sacombank', label: 'Sacombank - Ngân hàng TMCP Sài Gòn Thương Tín', logo: 'https://qr.sepay.vn/assets/img/banklogo/STB.png' },
    { value: 'VIB', label: 'VIB - Ngân hàng TMCP Quốc tế Việt Nam', logo: 'https://qr.sepay.vn/assets/img/banklogo/VIB.png' },
    {
        value: 'HDBank',
        label: 'HDBank - Ngân hàng TMCP Phát triển Thành phố Hồ Chí Minh',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/HDB.png'
    },
    { value: 'SeABank', label: 'SeABank - Ngân hàng TMCP Đông Nam Á', logo: 'https://qr.sepay.vn/assets/img/banklogo/SEAB.png' },
    {
        value: 'GPBank',
        label: 'GPBank - Ngân hàng Thương mại TNHH MTV Dầu Khí Toàn Cầu',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/GPB.png'
    },
    {
        value: 'PVcomBank',
        label: 'PVcomBank - Ngân hàng TMCP Đại Chúng Việt Nam',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/PVCB.png'
    },
    { value: 'NCB', label: 'NCB - Ngân hàng TMCP Quốc Dân', logo: 'https://qr.sepay.vn/assets/img/banklogo/NCB.png' },
    {
        value: 'ShinhanBank',
        label: 'ShinhanBank - Ngân hàng TNHH MTV Shinhan Việt Nam',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/SHBVN.png'
    },
    { value: 'SCB', label: 'SCB - Ngân hàng TMCP Sài Gòn', logo: 'https://qr.sepay.vn/assets/img/banklogo/SCB.png' },
    { value: 'PGBank', label: 'PGBank - Ngân hàng TMCP Xăng dầu Petrolimex', logo: 'https://qr.sepay.vn/assets/img/banklogo/PGB.png' },
    {
        value: 'Agribank',
        label: 'Agribank - Ngân hàng Nông nghiệp và Phát triển Nông thôn Việt Nam',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/VBA.png'
    },
    {
        value: 'Techcombank',
        label: 'Techcombank - Ngân hàng TMCP Kỹ thương Việt Nam',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/TCB.png'
    },
    {
        value: 'SaigonBank',
        label: 'SaigonBank - Ngân hàng TMCP Sài Gòn Công Thương',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/SGICB.png'
    },
    { value: 'DongABank', label: 'DongABank - Ngân hàng TMCP Đông Á', logo: 'https://qr.sepay.vn/assets/img/banklogo/DOB.png' },
    { value: 'BacABank', label: 'BacABank - Ngân hàng TMCP Bắc Á', logo: 'https://qr.sepay.vn/assets/img/banklogo/BAB.png' },
    {
        value: 'StandardChartered',
        label: 'StandardChartered - Ngân hàng TNHH MTV Standard Chartered Bank Việt Nam',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/SCVN.png'
    },
    {
        value: 'Oceanbank',
        label: 'Oceanbank - Ngân hàng Thương mại TNHH MTV Đại Dương',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/Oceanbank.png'
    },
    { value: 'VRB', label: 'VRB - Ngân hàng Liên doanh Việt - Nga', logo: 'https://qr.sepay.vn/assets/img/banklogo/VRB.png' },
    { value: 'ABBANK', label: 'ABBANK - Ngân hàng TMCP An Bình', logo: 'https://qr.sepay.vn/assets/img/banklogo/ABB.png' },
    { value: 'VietABank', label: 'VietABank - Ngân hàng TMCP Việt Á', logo: 'https://qr.sepay.vn/assets/img/banklogo/VAB.png' },
    {
        value: 'Eximbank',
        label: 'Eximbank - Ngân hàng TMCP Xuất Nhập khẩu Việt Nam',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/EIB.png'
    },
    {
        value: 'VietBank',
        label: 'VietBank - Ngân hàng TMCP Việt Nam Thương Tín',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/VIETBANK.png'
    },
    { value: 'IndovinaBank', label: 'IndovinaBank - Ngân hàng TNHH Indovina', logo: 'https://qr.sepay.vn/assets/img/banklogo/IVB.png' },
    { value: 'BaoVietBank', label: 'BaoVietBank - Ngân hàng TMCP Bảo Việt', logo: 'https://qr.sepay.vn/assets/img/banklogo/BVB.png' },
    {
        value: 'PublicBank',
        label: 'PublicBank - Ngân hàng TNHH MTV Public Việt Nam',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/PBVN.png'
    },
    { value: 'SHB', label: 'SHB - Ngân hàng TMCP Sài Gòn - Hà Nội', logo: 'https://qr.sepay.vn/assets/img/banklogo/SHB.png' },
    {
        value: 'CBBank',
        label: 'CBBank - Ngân hàng Thương mại TNHH MTV Xây dựng Việt Nam',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/CBB.png'
    },
    { value: 'OCB', label: 'OCB - Ngân hàng TMCP Phương Đông', logo: 'https://qr.sepay.vn/assets/img/banklogo/OCB.png' },
    { value: 'KienLongBank', label: 'KienLongBank - Ngân hàng TMCP Kiên Long', logo: 'https://qr.sepay.vn/assets/img/banklogo/KLB.png' },
    { value: 'CIMB', label: 'CIMB - Ngân hàng TNHH MTV CIMB Việt Nam', logo: 'https://qr.sepay.vn/assets/img/banklogo/CIMB.png' },
    { value: 'HSBC', label: 'HSBC - Ngân hàng TNHH MTV HSBC (Việt Nam)', logo: 'https://qr.sepay.vn/assets/img/banklogo/HSBC.png' },
    {
        value: 'DBSBank',
        label: 'DBSBank - DBS Bank Ltd - Chi nhánh Thành phố Hồ Chí Minh',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/DBS.png'
    },
    {
        value: 'Nonghyup',
        label: 'Nonghyup - Ngân hàng Nonghyup - Chi nhánh Hà Nội',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/NHB HN.png'
    },
    {
        value: 'HongLeong',
        label: 'HongLeong - Ngân hàng TNHH MTV Hong Leong Việt Nam',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/HLBVN.png'
    },
    { value: 'Woori', label: 'Woori - Ngân hàng TNHH MTV Woori Việt Nam', logo: 'https://qr.sepay.vn/assets/img/banklogo/WVN.png' },
    {
        value: 'UnitedOverseas',
        label: 'UnitedOverseas - Ngân hàng United Overseas - Chi nhánh TP. Hồ Chí Minh',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/UOB.png'
    },
    {
        value: 'KookminHN',
        label: 'KookminHN - Ngân hàng Kookmin - Chi nhánh Hà Nội',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/KBHN.png'
    },
    {
        value: 'KookminHCM',
        label: 'KookminHCM - Ngân hàng Kookmin - Chi nhánh Thành phố Hồ Chí Minh',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/KBHCM.png'
    },
    { value: 'COOPBANK', label: 'COOPBANK - Ngân hàng Hợp tác xã Việt Nam', logo: 'https://qr.sepay.vn/assets/img/banklogo/COOPBANK.png' },
    {
        value: 'VietinBank',
        label: 'VietinBank - Ngân hàng TMCP Công thương Việt Nam',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/ICB.png'
    },
    { value: 'MBBank', label: 'MBBank - Ngân hàng TMCP Quân đội', logo: 'https://qr.sepay.vn/assets/img/banklogo/MB.png' },
    {
        value: 'Vietcombank',
        label: 'Vietcombank - Ngân hàng TMCP Ngoại Thương Việt Nam',
        logo: 'https://qr.sepay.vn/assets/img/banklogo/VCB.png'
    }
]);
</script>

<style scoped>
.highlighted.bank-info {
    border-radius: 4px !important;
    padding: 8px !important;
}

.custom-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10000 !important;
}

.custom-modal {
    background: #ffffff;
    border-radius: 10px;
    width: 40em;
    max-width: 100%;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

.custom-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
}

.custom-modal-header h3 {
    margin: 0;
    font-size: 18px;
}

.close-button {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
}

.custom-modal-body {
    padding: 20px;
}

.custom-modal-footer {
    padding: 15px 20px;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.button {
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
}

.button.gray {
    background: #f1f1f1;
    color: #333;
}

.button.confirm {
    background: #007bff;
    color: white;
}

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
</style>
