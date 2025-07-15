<template>
    <li :class="getItemClass(item.status)">
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
            <a v-if="item.status === 'Chờ xác nhận'" href="#" @click.prevent="openConfirmRejectPopup(item.id)" class="button gray reject">
                <i class="sl sl-icon-close"></i> Hủy bỏ
            </a>
            <a v-if="item.status === 'Hoạt động'" href="#" class="button gray approve" @click.prevent="emit('downloadPdf', item.id)">
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

        <ReturnModal
            v-if="showReturnModal"
            :contract="selectedContract"
            :banks="banks"
            :today="today"
            @close="closeReturnModal"
            @request-otp="requestOTPForReturn"
        />

        <ExtendModal v-if="showExtendModal" :contract="selectedContract" @close="closeExtendModal" @confirm="confirmExtendContract" />

        <OTPModal
            :show="showOTPModal"
            :phone-number="otpPhoneNumber"
            :loading="otpLoading"
            v-model:otp-code="otpCode"
            @close="closeOTPModal"
            @confirm="confirmOTP"
        />
    </li>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { useToast } from 'vue-toastification';
import Swal from 'sweetalert2';
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

const props = defineProps({
    item: {
        type: Object,
        required: true
    },
    config: {
        type: Object,
        required: true
    },
    today: {
        type: String,
        required: true
    }
});

const emit = defineEmits(['rejectItem', 'extendContract', 'returnContract', 'downloadPdf']);

const showReturnModal = ref(false);
const showExtendModal = ref(false);
const showOTPModal = ref(false);
const selectedContract = ref({});
const otpPhoneNumber = ref('');
const otpCode = ref('');
const otpLoading = ref(false);
const currentAction = ref(null);
const returnForm = ref({
    check_out_date: '',
    bank_name: '',
    account_number: '',
    account_holder: ''
});
const extendForm = ref({
    months: 6
});
let tomSelectInstance = null;

const validateReturnForm = () => {
    const form = returnForm.value;
    return form.check_out_date && form.bank_name && form.account_number && form.account_holder;
};

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
        cancelButtonColor: '#e0e0e0'
    });

    if (result.isConfirmed) {
        emit('rejectItem', id);
    }
};

const openConfirmExtendPopup = contract => {
    selectedContract.value = contract;
    extendForm.value.months = 6;
    showExtendModal.value = true;
};

const confirmExtendContract = async months => {
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
        currentAction.value = 'extend';
        extendForm.value.months = months; // Đồng bộ months từ modal
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
    currentAction.value = null;
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
        if (currentAction.value === 'return') {
            console.log(selectedContract.value.id);
            console.log(returnForm.value);
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
        currentAction.value = null;
    } catch (error) {
        console.error('Lỗi khi xác minh OTP:', error);
        toast.error('Lỗi khi xác minh OTP. Vui lòng thử lại.');
    } finally {
        otpLoading.value = false;
    }
};

const requestOTPForReturn = async formData => {
    returnForm.value = { ...formData }; // Đồng bộ formData từ modal
    if (!validateReturnForm()) {
        toast.error('Vui lòng nhập đầy đủ và đúng định dạng thông tin trả phòng và tài khoản ngân hàng.');
        return;
    }
    showReturnModal.value = false;
    currentAction.value = 'return';
    showOTPModal.value = true;
    await requestOTP();
};

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
});

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

<style scoped></style>
