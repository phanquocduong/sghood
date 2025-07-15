<template>
    <h4>Quản lý hợp đồng</h4>

    <div v-if="isLoading" class="loading-overlay">
        <div class="spinner"></div>
        <p>Đang tải...</p>
    </div>

    <ul v-else>
        <li v-for="item in items" :key="item.id" :class="getItemClass(item.status)">
            <div class="list-box-listing bookings">
                <div class="list-box-listing-img">
                    <img :src="config.public.baseUrl + item.room_image" alt="Room image" />
                </div>
                <div class="list-box-listing-content">
                    <div class="inner">
                        <h3>
                            Hợp đồng #{{ item.id }} [{{ item.room_name }} - {{ item.motel_name }}]
                            <span :class="getStatusClass(item.status)">{{ item.status }}</span>
                            <span
                                v-if="item.latest_extension_status && item.latest_extension_status !== 'Huỷ bỏ'"
                                :class="getExtensionStatusClass(item.latest_extension_status)"
                            >
                                {{ formatExtensionStatus(item.latest_extension_status) }}
                            </span>
                            <span
                                v-if="item.latest_checkout_status && item.latest_checkout_status !== 'Huỷ bỏ'"
                                :class="getCheckoutStatusClass(item.latest_checkout_status)"
                            >
                                {{ formatCheckoutStatus(item.latest_checkout_status) }}
                            </span>
                        </h3>
                        <div class="inner-booking-list">
                            <h5>Ngày bắt đầu:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDate(item.start_date) }}</li>
                            </ul>
                        </div>
                        <div class="inner-booking-list">
                            <h5>Ngày kết thúc:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDate(item.end_date) }}</li>
                            </ul>
                        </div>
                        <div class="inner-booking-list">
                            <h5>Tiền cọc:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatCurrency(item.deposit_amount) }}đ</li>
                            </ul>
                        </div>
                        <div class="inner-booking-list">
                            <h5>Giá thuê mỗi tháng:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatCurrency(item.rental_price) }}đ</li>
                            </ul>
                        </div>
                        <div v-if="item.signed_at" class="inner-booking-list">
                            <h5>Đã ký lúc:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDateTime(item.signed_at) }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="buttons-to-right">
                <a
                    v-if="item.status === 'Chờ xác nhận'"
                    href="#"
                    @click.prevent="openConfirmRejectPopup(item.id)"
                    class="button gray reject"
                >
                    <i class="sl sl-icon-close"></i> Hủy bỏ
                </a>
                <a v-if="item.status === 'Hoạt động'" href="#" class="button gray approve" @click.prevent="downloadPdf(item.id)">
                    <i class="im im-icon-File-Download"></i> Tải hợp đồng
                </a>
                <NuxtLink
                    v-if="item.status === 'Chờ thanh toán tiền cọc'"
                    :to="`/quan-ly/hoa-don/${item.invoice_id}/thanh-toan`"
                    class="button gray approve"
                >
                    <i class="im im-icon-Folder-Bookmark"></i> Thanh toán tiền cọc
                </NuxtLink>
                <NuxtLink :to="`/quan-ly/hop-dong/${item.id}`" class="button gray approve">
                    <i class="im im-icon-Folder-Bookmark"></i> {{ getActText(item.status) }}
                </NuxtLink>
                <a
                    v-if="
                        item.status === 'Hoạt động' &&
                        isNearExpiration(item.end_date) &&
                        item.latest_extension_status !== 'Chờ duyệt' &&
                        item.latest_checkout_status !== 'Chờ kiểm kê' &&
                        item.latest_checkout_status !== 'Đã kiểm kê'
                    "
                    href="#"
                    @click.prevent="openConfirmExtendPopup(item)"
                    class="button"
                >
                    <i class="im im-icon-Clock-Forward"></i> Gia hạn
                </a>
                <a
                    v-if="
                        item.status === 'Hoạt động' &&
                        isNearExpiration(item.end_date) &&
                        item.latest_extension_status !== 'Chờ duyệt' &&
                        item.latest_checkout_status !== 'Chờ kiểm kê' &&
                        item.latest_checkout_status !== 'Đã kiểm kê'
                    "
                    href="#"
                    @click.prevent="openReturnModal(item)"
                    class="button"
                >
                    <i class="sl sl-icon-logout"></i> Trả phòng
                </a>
            </div>
        </li>
        <div v-if="!items.length" class="col-md-12 text-center">
            <p>Chưa có hợp đồng nào.</p>
        </div>
    </ul>

    <div v-if="showReturnModal" class="custom-modal-overlay">
        <div class="custom-modal">
            <div class="custom-modal-header">
                <h3>Xác nhận trả phòng</h3>
                <button class="close-button" @click="closeReturnModal">×</button>
            </div>
            <div class="custom-modal-body">
                <div class="modal-content">
                    <p><strong>Số hợp đồng:</strong> {{ selectedContract.id }}</p>
                    <p><strong>Phòng:</strong> {{ selectedContract.room_name }} - {{ selectedContract.motel_name }}</p>
                    <p><strong>Ngày kết thúc:</strong> {{ formatDate(selectedContract.end_date) }}</p>
                    <p><strong>Tiền cọc:</strong> {{ formatCurrency(selectedContract.deposit_amount) }}đ</p>
                    <hr />
                    <h5>
                        <strong
                            ><em>Thông tin trả phòng <span class="text-danger">(*)</span></em></strong
                        >
                    </h5>
                    <div class="form-group">
                        <label for="check_out_date" class="form-label">Ngày trả phòng:</label>
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
                <button class="button gray" @click="closeReturnModal">Hủy</button>
                <button class="button confirm" @click="requestOTPForReturn" :disabled="otpLoading">
                    <span v-if="otpLoading" class="button-spinner"></span>
                    {{ otpLoading ? 'Đang gửi OTP...' : 'Xác nhận trả phòng' }}
                </button>
            </div>
        </div>
    </div>

    <div v-if="showExtendModal" class="custom-modal-overlay">
        <div class="custom-modal">
            <div class="custom-modal-header">
                <h3>Gia hạn hợp đồng</h3>
                <button class="close-button" @click="closeExtendModal">×</button>
            </div>
            <div class="custom-modal-body">
                <div class="modal-content">
                    <p><strong>Số hợp đồng:</strong> {{ selectedContract.id }}</p>
                    <p><strong>Phòng:</strong> {{ selectedContract.room_name }} - {{ selectedContract.motel_name }}</p>
                    <p><strong>Ngày kết thúc hiện tại:</strong> {{ formatDate(selectedContract.end_date) }}</p>
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
                    <p><strong>Giá thuê phòng mới:</strong> {{ formatCurrency(selectedContract.room_price) }}đ</p>
                    <p>Các điều khoản khác của hợp đồng gốc vẫn giữ nguyên hiệu lực.</p>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button class="button gray" @click="closeExtendModal">Hủy</button>
                <button class="button confirm" @click="confirmExtendContract" :disabled="!isExtendFormValid">Xác nhận gia hạn</button>
            </div>
        </div>
    </div>

    <OTPModal
        :show="showOTPModal"
        :phone-number="otpPhoneNumber"
        :loading="otpLoading"
        v-model:otp-code="otpCode"
        @close="closeOTPModal"
        @confirm="confirmOTP"
    />
</template>

<script setup>
import { ref, watch, nextTick, computed } from 'vue';
import Swal from 'sweetalert2';
import { useToast } from 'vue-toastification';
import TomSelect from 'tom-select';
import 'tom-select/dist/css/tom-select.css';
import { useFirebaseAuth } from '~/composables/useFirebaseAuth';
import { useContractUtils } from '~/composables/useContractUtils';

const { $api } = useNuxtApp();
const toast = useToast();
const { sendOTP, verifyOTP } = useFirebaseAuth();
const {
    formatDate,
    formatDateTime,
    formatCurrency,
    getItemClass,
    getStatusClass,
    getExtensionStatusClass,
    getCheckoutStatusClass,
    formatExtensionStatus,
    formatCheckoutStatus,
    getActText,
    isNearExpiration
} = useContractUtils();

const config = useRuntimeConfig();
const props = defineProps({
    items: {
        type: Array,
        required: true
    },
    isLoading: {
        type: Boolean,
        required: true
    }
});

const emit = defineEmits(['rejectItem', 'extendContract', 'returnContract']);

const showReturnModal = ref(false);
const showExtendModal = ref(false);
const showOTPModal = ref(false);
const selectedContract = ref({});
const otpPhoneNumber = ref('');
const otpCode = ref('');
const otpLoading = ref(false);
const today = computed(() => new Date().toISOString().split('T')[0]);
let tomSelectInstance = null;
const currentAction = ref(null); // Thêm biến để theo dõi hành động hiện tại

const returnForm = ref({
    check_out_date: '',
    bank_name: '',
    account_number: '',
    account_holder: ''
});

const extendForm = ref({
    months: 6
});

const validateReturnForm = () => {
    const form = returnForm.value;
    if (!form.check_out_date) return false;
    if (!form.bank_name) return false;
    if (!form.account_number) return false;
    if (!form.account_holder) return false;
    return true;
};

const isFormValid = computed(() => validateReturnForm());
const isExtendFormValid = computed(() => extendForm.value.months >= 1);

const calculatedNewEndDate = computed(() => {
    if (!selectedContract.value.end_date || !extendForm.value.months) return '';
    const currentEndDate = new Date(selectedContract.value.end_date);
    return new Date(currentEndDate.setMonth(currentEndDate.getMonth() + extendForm.value.months));
});

const openConfirmRejectPopup = async id => {
    const result = await Swal.fire({
        title: 'Xác nhận hủy hợp đồng',
        text: 'Bạn có chắc chắn muốn hủy hợp đồng này?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xác nhận',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#f91942',
        cancelButtonColor: '#e0e0e0',
        customClass: {
            confirmButton: 'button',
            cancelButton: 'button gray'
        }
    });

    if (result.isConfirmed) {
        emit('rejectItem', id);
    }
};

const openConfirmExtendPopup = async contract => {
    selectedContract.value = contract;
    extendForm.value.months = 6;
    showExtendModal.value = true;
};

const confirmExtendContract = async () => {
    if (!isExtendFormValid.value) {
        toast.error('Vui lòng chọn thời gian gia hạn hợp lệ.');
        return;
    }
    showExtendModal.value = false;
    try {
        const response = await $api(`/contracts/${selectedContract.value.id}`, { method: 'GET' });
        otpPhoneNumber.value = response.data.user_phone || '';
        if (!otpPhoneNumber.value) {
            toast.error('Không tìm thấy số điện thoại cho hợp đồng này.');
            return;
        }
        currentAction.value = 'extend'; // Đặt hành động là gia hạn
        showOTPModal.value = true;
        await requestOTP();
    } catch (error) {
        toast.error('Lỗi khi lấy thông tin hợp đồng.');
        console.error(error);
    }
};

const closeExtendModal = () => {
    showExtendModal.value = false;
    selectedContract.value = {};
    extendForm.value.months = 6;
};

const openReturnModal = async contract => {
    try {
        const response = await $api(`/contracts/${contract.id}`, { method: 'GET' });
        otpPhoneNumber.value = response.data.user_phone || '';
        if (!otpPhoneNumber.value) {
            toast.error('Không tìm thấy số điện thoại cho hợp đồng này.');
            return;
        }
        selectedContract.value = contract;
        returnForm.value = {
            check_out_date: '',
            bank_name: '',
            account_number: '',
            account_holder: ''
        };
        showReturnModal.value = true;
    } catch (error) {
        toast.error('Lỗi khi lấy thông tin hợp đồng.');
        console.error(error);
    }
};

const closeReturnModal = () => {
    showReturnModal.value = false;
    showOTPModal.value = false;
    selectedContract.value = {};
    returnForm.value = {
        check_out_date: '',
        bank_name: '',
        account_number: '',
        account_holder: ''
    };
    otpCode.value = '';
    otpPhoneNumber.value = '';
    if (tomSelectInstance) {
        tomSelectInstance.destroy();
        tomSelectInstance = null;
    }
};

const closeOTPModal = () => {
    showOTPModal.value = false;
    selectedContract.value = {};
    otpCode.value = '';
    otpPhoneNumber.value = '';
    currentAction.value = null; // Reset hành động khi đóng OTP modal
};

const requestOTP = async () => {
    if (!otpPhoneNumber.value) {
        toast.error('Số điện thoại không hợp lệ.');
        return;
    }
    try {
        await nextTick();
        const success = await sendOTP(otpPhoneNumber.value);
        if (!success) {
            showOTPModal.value = false;
            toast.error('Lỗi khi gửi OTP. Vui lòng thử lại.');
        }
    } catch (error) {
        console.error('Lỗi khi gửi OTP:', error);
        toast.error('Lỗi khi gửi OTP. Vui lòng thử lại.');
        showOTPModal.value = false;
    }
};

const confirmOTP = async () => {
    otpLoading.value = true;
    try {
        const verified = await verifyOTP(otpCode.value);
        if (!verified) {
            toast.error('OTP không hợp lệ.');
            otpLoading.value = false;
            return;
        }
        // Gửi yêu cầu dựa trên hành động hiện tại
        if (currentAction.value === 'return') {
            if (validateReturnForm()) {
                emit('returnContract', selectedContract.value.id, returnForm.value);
                closeReturnModal();
            } else {
                toast.error('Vui lòng kiểm tra lại thông tin trả phòng.');
                otpLoading.value = false;
                return;
            }
        } else if (currentAction.value === 'extend') {
            if (isExtendFormValid.value) {
                emit('extendContract', selectedContract.value.id, extendForm.value.months);
                closeExtendModal();
            } else {
                toast.error('Vui lòng kiểm tra lại thông tin gia hạn.');
                otpLoading.value = false;
                return;
            }
        }
        showOTPModal.value = false;
        currentAction.value = null; // Reset hành động sau khi hoàn tất
    } catch (error) {
        console.error('Lỗi khi xác minh OTP:', error);
        toast.error('Lỗi khi xác minh OTP. Vui lòng thử lại.');
    } finally {
        otpLoading.value = false;
    }
};

const requestOTPForReturn = async () => {
    if (!isFormValid.value) {
        toast.error('Vui lòng nhập đầy đủ và đúng định dạng thông tin trả phòng và tài khoản ngân hàng.');
        return;
    }
    showReturnModal.value = false;
    currentAction.value = 'return'; // Đặt hành động là trả phòng
    showOTPModal.value = true;
    await requestOTP();
};

const downloadPdf = async id => {
    try {
        const response = await $api(`/contracts/${id}/download-pdf`, { method: 'GET' });
        window.open(response.data.file_url, '_blank');
    } catch (error) {
        toast.error(error.response?._data?.error || 'Đã có lỗi xảy ra khi tải PDF.');
    }
};

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

watch(showReturnModal, async newValue => {
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
                    option: function (data, escape) {
                        return `<span style="display: flex; align-items: center;"><img style="max-width: 40px; margin-right: 12px; border-radius: 4px;" src="${escape(
                            data.logo || ''
                        )}" alt="${escape(
                            data.label
                        )} logo" onerror="this.style.display='none'"/><span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: calc(100% - 52px);">${escape(
                            data.label
                        )}</span></span>`;
                    },
                    item: function (data, escape) {
                        return `<span style="display: flex; align-items: center;"><img style="max-width: 40px; margin-right: 12px; border-radius: 4px;" src="${escape(
                            data.logo || ''
                        )}" alt="${escape(
                            data.label
                        )} logo" onerror="this.style.display='none'"/><span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: calc(100% - 52px);">${escape(
                            data.label
                        )}</span></span>`;
                    },
                    no_results: function (data, escape) {
                        return '<div class="no-results">Không tìm thấy kết quả</div>';
                    }
                }
            });
        }
    }
});
</script>

<style>
.button-spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #fff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
    margin-right: 8px;
    vertical-align: middle;
}

.modal-overlay {
    z-index: 2000 !important;
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
    margin-top: 5%;
}

.custom-modal-header {
    padding: 15px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.custom-modal-header h3 {
    margin: 0;
    font-size: 1.5em;
    color: #2c3e50;
    font-weight: 600;
}

.close-button {
    background: none;
    border: none;
    font-size: 1.5em;
    cursor: pointer;
    color: #7f8c8d;
    transition: color 0.3s ease;
}

.close-button:hover {
    color: #e74c3c;
}

.custom-modal-body {
    padding: 20px;
    max-height: 60vh;
    overflow-y: auto;
    background: #fff;
}

.modal-content p {
    margin: 10px 0;
    color: #34495e;
    font-size: 1em;
}

.modal-content p strong {
    color: #2c3e50;
}

.modal-content hr {
    margin: 20px 0;
    border: 0;
    border-top: 1px solid #e9ecef;
}

.form-group {
    margin-bottom: 15px;
}

.form-label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #2c3e50;
    font-size: 0.95em;
}

.form-control,
.custom-select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 1em;
    line-height: 28px;
    color: #495057;
    background: #fff;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-control:focus,
.custom-select:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
}

.custom-select {
    appearance: none;
    background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23333' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E")
        no-repeat right 10px center;
    padding-right: 30px;
}

.custom-select option {
    padding: 10px;
    background: #fff;
    color: #333;
}

.error-message {
    color: #e74c3c;
    font-size: 0.9em;
    margin-top: 5px;
}

.custom-modal-footer {
    padding: 15px 20px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    background: #f8f9fa;
}

.custom-modal-footer .button {
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    border: none;
    font-weight: 500;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.custom-modal-footer .button.gray {
    background-color: #dcdde1;
    color: #2c3e50;
}

.custom-modal-footer .button.confirm {
    background-color: #2ecc71;
    color: #fff;
}

.custom-modal-footer .button:hover {
    transform: translateY(-1px);
}

.custom-modal-footer .button.gray:hover {
    background-color: #c4c6cc;
}

.custom-modal-footer .button.confirm:hover {
    background-color: #27ae60;
}

.ts-wrapper {
    position: relative;
    width: 100%;
}

.ts-control {
    border: 1px solid #ced4da;
    border-radius: 6px;
    padding: 10px 14px;
    background: #fff;
    display: flex;
    align-items: center;
    min-height: 40px;
    cursor: pointer;
    transition: border-color 0.3s ease;
    font-size: 16px;
    line-height: 28px;
}

.items-placeholder {
    font-size: 16px !important;
    line-height: 28px !important;
    height: auto !important;
}

.ts-control:hover {
    border-color: #3498db;
}

.ts-control .item {
    display: flex;
    align-items: center;
    padding: 2px 6px;
    background: #e9ecef;
    border-radius: 4px;
    margin-right: 5px;
}

.ts-control .item img {
    max-width: 40px;
    margin-right: 8px;
    vertical-align: middle;
}

.ts-dropdown {
    border: 1px solid #ced4da;
    border-radius: 6px;
    background: #fff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    margin-top: 2px;
    z-index: 1001;
}

.ts-dropdown .option {
    padding: 10px 12px;
    display: flex;
    align-items: center;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.ts-dropdown .option:hover,
.ts-dropdown .option.active {
    background-color: #f1f3f5;
}

.ts-dropdown .option img {
    max-width: 40px;
    margin-right: 10px;
    vertical-align: middle;
    object-fit: contain;
}

.ts-dropdown .option span {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: calc(100% - 50px);
}

.item:hover {
    flex: 0;
}

.text-danger {
    color: #e74c3c;
}

.swal2-popup.swal2-modal {
    width: 50em !important;
}

.swal2-actions .swal2-confirm {
    background-color: #64bc36 !important;
}

.swal2-actions .swal2-confirm:hover {
    background-color: #68cf30 !important;
}

.booking-status.pending.extension-status,
.booking-status.pending.checkout-status {
    background-color: #61b2db !important;
}

.booking-status.canceled.extension-status,
.booking-status.canceled.checkout-status {
    background-color: #ee3535 !important;
}

.bookings .list-box-listing-img {
    max-width: 150px;
    max-height: none;
    border-radius: 4px;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #f91942;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

p {
    color: #333;
    margin-top: 10px;
    font-size: 16px;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>
