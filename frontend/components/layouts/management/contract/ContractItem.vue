<template>
    <!-- Mỗi mục hợp đồng -->
    <li :class="getItemClass(item.status)">
        <div class="list-box-listing bookings">
            <!-- Hình ảnh phòng -->
            <div class="list-box-listing-img">
                <NuxtLink :to="`/danh-sach-nha-tro/${item.motel_slug}`" target="_blank" style="height: 150px">
                    <img :src="useRuntimeConfig().public.baseUrl + item.room_image" alt="Room image" />
                    <!-- Hình ảnh phòng -->
                </NuxtLink>
            </div>
            <!-- Nội dung hợp đồng -->
            <div class="list-box-listing-content">
                <div class="inner">
                    <h3>
                        Hợp đồng #{{ item.id }} [{{ item.room_name }} - {{ item.motel_name }}]
                        <!-- Số hợp đồng, tên phòng, tên nhà trọ -->
                        <span :class="getStatusClass(item.status)">{{ item.status }}</span>
                        <!-- Trạng thái hợp đồng -->
                    </h3>
                    <!-- Thời hạn hợp đồng -->
                    <div class="inner-booking-list">
                        <h5>Thời hạn:</h5>
                        <ul class="booking-list">
                            <li class="highlighted">{{ formatDate(item.start_date) }} - {{ formatDate(item.end_date) }}</li>
                        </ul>
                    </div>
                    <!-- Tiền cọc -->
                    <div class="inner-booking-list">
                        <h5>Tiền cọc:</h5>
                        <ul class="booking-list">
                            <li class="highlighted">{{ formatPrice(item.deposit_amount) }}</li>
                        </ul>
                    </div>
                    <!-- Giá thuê -->
                    <div class="inner-booking-list">
                        <h5>Giá thuê:</h5>
                        <ul class="booking-list">
                            <li class="highlighted">{{ formatPrice(item.rental_price) }}</li>
                        </ul>
                    </div>
                    <!-- Ngày ký hợp đồng -->
                    <div v-if="item.signed_at" class="inner-booking-list">
                        <h5>Đã ký vào:</h5>
                        <ul class="booking-list">
                            <li class="highlighted">{{ formatDateTime(item.signed_at) }}</li>
                        </ul>
                    </div>
                    <!-- Ngày kết thúc sớm (nếu có) -->
                    <div v-if="item.early_terminated_at" class="inner-booking-list">
                        <h5>Đã kết thúc hợp đồng sớm vào:</h5>
                        <ul class="booking-list">
                            <li class="highlighted">{{ formatDateTime(item.early_terminated_at) }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- Các nút hành động -->
        <div class="buttons-to-right">
            <!-- Nút hủy hợp đồng (hiển thị khi trạng thái là "Chờ xác nhận") -->
            <a v-if="item.status === 'Chờ xác nhận'" href="#" @click.prevent="openConfirmCancelPopup(item.id)" class="button gray reject">
                <i class="sl sl-icon-close"></i> Hủy bỏ
            </a>
            <!-- Nút tải hợp đồng (hiển thị khi trạng thái là "Hoạt động") -->
            <a v-if="item.status === 'Hoạt động'" href="#" class="button gray approve" @click.prevent="emit('downloadPdf', item.id)">
                <i class="im im-icon-File-Download"></i> Tải hợp đồng
            </a>
            <!-- Nút thanh toán tiền cọc (hiển thị khi trạng thái là "Chờ thanh toán tiền cọc") -->
            <NuxtLink
                v-if="item.status === 'Chờ thanh toán tiền cọc'"
                :to="`/quan-ly/hoa-don/${item.invoice_id}/thanh-toan`"
                class="button gray approve"
            >
                <i class="im im-icon-Folder-Bookmark"></i> Thanh toán tiền cọc
            </NuxtLink>
            <!-- Nút xem chi tiết hợp đồng -->
            <NuxtLink :to="`/quan-ly/hop-dong/${item.id}`" class="button gray approve">
                <i class="im im-icon-Folder-Bookmark"></i> {{ getActText(item.status) }}
            </NuxtLink>
            <!-- Nút quản lý người ở cùng (hiển thị khi trạng thái là "Hoạt động") -->
            <NuxtLink v-if="item.status === 'Hoạt động'" :to="`/quan-ly/hop-dong/${item.id}/nguoi-o-cung`" class="button gray approve">
                <i class="im im-icon-Folder-Bookmark"></i> Quản lý người ở cùng
            </NuxtLink>
            <!-- Nút gia hạn hợp đồng (hiển thị khi hợp đồng gần hết hạn và chưa có yêu cầu gia hạn/trả phòng) -->
            <a
                v-if="
                    item.status === 'Hoạt động' &&
                    isNearExpiration(item.end_date) &&
                    item.latest_extension_status !== 'Chờ duyệt' &&
                    (item.has_checkout === null || (item.has_checkout !== null && item.latest_checkout_status !== null))
                "
                href="#"
                @click.prevent="openConfirmExtendPopup(item)"
                class="button"
            >
                <i class="im im-icon-Clock-Forward"></i> Gia hạn
            </a>
            <!-- Nút trả phòng (hiển thị khi hợp đồng gần hết hạn và chưa có yêu cầu gia hạn/trả phòng) -->
            <a
                v-if="
                    item.status === 'Hoạt động' &&
                    isNearExpiration(item.end_date) &&
                    item.latest_extension_status !== 'Chờ duyệt' &&
                    (item.has_checkout === null || (item.has_checkout !== null && item.latest_checkout_status !== null))
                "
                href="#"
                @click.prevent="openReturnModal(item)"
                class="button"
            >
                <i class="sl sl-icon-logout"></i> Trả phòng
            </a>
        </div>

        <!-- Các modal ẩn (sử dụng Magnific Popup) -->
        <ExtendModal :contract="selectedContract" :otpLoading="otpLoading" @close="closeExtendModal" @confirm="confirmExtendContract" />
        <ReturnModal
            :contract="selectedContract"
            :banks="banks"
            :today="today"
            :otpLoading="otpLoading"
            @close="closeReturnModal"
            @request-otp="requestOTPForReturn"
        />
        <OTPModal :phone-number="otpPhoneNumber" :loading="otpLoading" v-model:otp-code="otpCode" @confirm="confirmOTP" />
    </li>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAppToast } from '~/composables/useToast';
import Swal from 'sweetalert2';
import { useFirebaseAuth } from '~/composables/useFirebaseAuth';
import { useContractUtils } from '~/composables/useContractUtils';
import { useFormatPrice } from '~/composables/useFormatPrice';
import { useFormatDate } from '~/composables/useFormatDate';

// Lấy instance và composables
const { $api } = useNuxtApp();
const toast = useAppToast();
const config = useState('configs');
const { formatPrice } = useFormatPrice();
const { formatDate, formatDateTime } = useFormatDate();
const { sendOTP, verifyOTP } = useFirebaseAuth();
const { getItemClass, getStatusClass, getActText, isNearExpiration } = useContractUtils();

// Định nghĩa props
const props = defineProps({
    item: {
        type: Object,
        required: true // Thông tin hợp đồng
    },
    today: {
        type: String,
        required: true // Ngày hiện tại
    }
});

// Định nghĩa emits
const emit = defineEmits(['cancelContract', 'extendContract', 'returnContract', 'earlyTermination', 'downloadPdf']);

// Khởi tạo các biến reactive
const selectedContract = ref({}); // Hợp đồng được chọn
const otpPhoneNumber = ref(''); // Số điện thoại để gửi OTP
const otpCode = ref(''); // Mã OTP
const otpLoading = ref(false); // Trạng thái loading khi gửi/xác minh OTP
const currentAction = ref(null); // Hành động hiện tại (extend/return)
const banks = ref(config.value.supported_banks); // Danh sách ngân hàng
const returnForm = ref({
    check_out_date: '',
    bank_name: '',
    account_number: '',
    account_holder: '',
    is_cash_refunded: false // Hoàn tiền bằng tiền mặt
});
const extendForm = ref({
    months: 6 // Số tháng gia hạn mặc định
});

// Hàm kiểm tra form trả phòng
const validateReturnForm = () => {
    const form = returnForm.value;
    if (!form.check_out_date) return false; // Kiểm tra ngày trả phòng
    if (!form.is_cash_refunded) {
        return form.bank_name && form.account_number && form.account_holder; // Kiểm tra thông tin ngân hàng nếu không hoàn tiền mặt
    }
    return true;
};

// Kiểm tra tính hợp lệ của form gia hạn
const isExtendFormValid = computed(() => extendForm.value.months >= 1);

// Mở popup xác nhận hủy hợp đồng
const openConfirmCancelPopup = async id => {
    const result = await Swal.fire({
        title: 'Xác nhận hủy hợp đồng',
        text: 'Bạn có chắc chắn muốn hủy hợp đồng này?', // Thông báo xác nhận
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xác nhận',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#f91942',
        cancelButtonColor: '#e0e0e0'
    });

    if (result.isConfirmed) {
        emit('cancelContract', id); // Emit sự kiện hủy hợp đồng
    }
};

// Mở modal gia hạn hợp đồng
const openConfirmExtendPopup = contract => {
    selectedContract.value = contract; // Lưu hợp đồng được chọn
    extendForm.value.months = 6; // Đặt số tháng gia hạn mặc định
    if (!window.jQuery || !window.jQuery.fn.magnificPopup) {
        console.error('Magnific Popup không được tải'); // Báo lỗi nếu Magnific Popup không tải
        toast.error('Lỗi khi mở form gia hạn.');
        return;
    }
    window.jQuery.magnificPopup.open({
        items: { src: '#small-dialog', type: 'inline' },
        fixedContentPos: false,
        fixedBgPos: true,
        overflowY: 'auto',
        closeBtnInside: true,
        preloader: false,
        midClick: true,
        removalDelay: 300,
        mainClass: 'my-mfp-zoom-in',
        closeOnBgClick: false,
        callbacks: {
            open: () => {
                window.jQuery(durationSelect.value).trigger('chosen:updated'); // Cập nhật Chosen select
            }
        }
    });
};

// Xác nhận gia hạn hợp đồng
const confirmExtendContract = async months => {
    if (!isExtendFormValid.value) {
        toast.error('Vui lòng chọn thời gian gia hạn hợp lệ.'); // Thông báo lỗi nếu form không hợp lệ
        return;
    }
    extendForm.value.months = months;
    try {
        const response = await $api(`/contracts/${selectedContract.value.id}`, { method: 'GET' }); // Lấy thông tin hợp đồng
        otpPhoneNumber.value = response.data.user_phone || '';
        if (!otpPhoneNumber.value) {
            toast.error('Không tìm thấy số điện thoại cho hợp đồng này.');
            return;
        }
        currentAction.value = 'extend'; // Đặt hành động hiện tại là gia hạn
        await requestOTP(); // Gửi yêu cầu OTP
    } catch (error) {
        toast.error('Lỗi khi lấy thông tin hợp đồng.');
        console.error(error);
    }
};

// Đóng modal gia hạn
const closeExtendModal = () => {
    selectedContract.value = {};
    extendForm.value.months = 6;
    if (window.jQuery && window.jQuery.fn.magnificPopup) {
        window.jQuery.magnificPopup.close(); // Đóng modal
    }
};

// Mở modal trả phòng
const openReturnModal = async contract => {
    try {
        const response = await $api(`/contracts/${contract.id}`, { method: 'GET' }); // Lấy thông tin hợp đồng
        otpPhoneNumber.value = response.data.user_phone || '';
        if (!otpPhoneNumber.value) {
            toast.error('Không tìm thấy số điện thoại cho hợp đồng này.');
            return;
        }
        selectedContract.value = contract; // Lưu hợp đồng được chọn
        returnForm.value = {
            check_out_date: '',
            bank_name: '',
            account_number: '',
            account_holder: '',
            is_cash_refunded: false
        };
        if (!window.jQuery || !window.jQuery.fn.magnificPopup) {
            console.error('Magnific Popup không được tải');
            toast.error('Lỗi khi mở form trả phòng.');
            return;
        }
        window.jQuery.magnificPopup.open({
            items: { src: '#edit-schedule-dialog', type: 'inline' },
            fixedContentPos: false,
            fixedBgPos: true,
            overflowY: 'auto',
            closeBtnInside: true,
            preloader: false,
            midClick: true,
            removalDelay: 300,
            mainClass: 'my-mfp-zoom-in',
            closeOnBgClick: false,
            callbacks: {
                open: () => {
                    const selectElement = document.getElementById('bank_name');
                    if (selectElement && !selectElement.tomselect) {
                        new TomSelect(selectElement, {
                            plugins: ['dropdown_input'],
                            valueField: 'value',
                            labelField: 'label',
                            searchField: ['label'],
                            options: banks.value,
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
                                    </span>`, // Tùy chỉnh hiển thị option ngân hàng
                                item: (data, escape) => `
                                    <span style="display: flex; align-items: center;">
                                        <img style="max-width: 79px; margin-right: 8px; border-radius: 4px;" 
                                             src="${escape(data.logo || '')}" 
                                             alt="${escape(data.label)} logo" 
                                             onerror="this.style.display='none'"/>
                                        <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: calc(100% - 87px);">
                                            ${escape(data.label)}
                                        </span>
                                    </span>`, // Tùy chỉnh hiển thị item đã chọn
                                no_results: () => '<div class="no-results">Không tìm thấy ngân hàng</div>'
                            },
                            onChange: value => {
                                returnForm.value.bank_name = value; // Cập nhật giá trị ngân hàng
                            }
                        });
                    }
                }
            }
        });
    } catch (error) {
        toast.error('Lỗi khi lấy thông tin hợp đồng.');
        console.error(error);
    }
};

// Đóng modal trả phòng
const closeReturnModal = () => {
    selectedContract.value = {};
    returnForm.value = {
        check_out_date: '',
        bank_name: '',
        account_number: '',
        account_holder: '',
        is_cash_refunded: false
    };
    otpCode.value = '';
    otpPhoneNumber.value = '';
    if (window.jQuery && window.jQuery.fn.magnificPopup) {
        window.jQuery.magnificPopup.close(); // Đóng modal
    }
};

// Đóng modal OTP
const closeOTPModal = () => {
    selectedContract.value = {};
    otpCode.value = '';
    otpPhoneNumber.value = '';
    currentAction.value = null;
    if (window.jQuery && window.jQuery.fn.magnificPopup) {
        window.jQuery.magnificPopup.close(); // Đóng modal
    }
};

// Gửi yêu cầu OTP
const requestOTP = async () => {
    if (!otpPhoneNumber.value) {
        toast.error('Số điện thoại không hợp lệ.');
        return;
    }
    try {
        otpLoading.value = true; // Bật trạng thái loading
        const success = await sendOTP(otpPhoneNumber.value); // Gửi OTP
        if (!success) {
            toast.error('Lỗi khi gửi OTP. Vui lòng thử lại.');
            otpLoading.value = false;
            return;
        }
        window.jQuery.magnificPopup.open({
            items: { src: '#otp-dialog', type: 'inline' },
            fixedContentPos: false,
            fixedBgPos: true,
            overflowY: 'auto',
            closeBtnInside: true,
            preloader: false,
            midClick: true,
            removalDelay: 300,
            mainClass: 'my-mfp-zoom-in',
            closeOnBgClick: false,
            callbacks: {
                open: () => {
                    const otpInput = document.getElementById('otp-input');
                    if (otpInput) {
                        otpInput.focus(); // Focus vào input OTP
                    }
                }
            }
        });
    } catch (error) {
        console.error('Lỗi khi gửi OTP:', error);
        toast.error('Lỗi khi gửi OTP. Vui lòng thử lại.');
    } finally {
        otpLoading.value = false; // Tắt trạng thái loading
    }
};

// Xác minh OTP
const confirmOTP = async () => {
    otpLoading.value = true; // Bật trạng thái loading
    try {
        const verified = await verifyOTP(otpCode.value); // Xác minh OTP
        if (!verified) {
            toast.error('OTP không hợp lệ.');
            otpLoading.value = false;
            return;
        }
        if (currentAction.value === 'return') {
            if (validateReturnForm()) {
                emit('returnContract', selectedContract.value.id, returnForm.value); // Emit sự kiện trả phòng
                closeReturnModal();
            } else {
                toast.error('Vui lòng kiểm tra lại thông tin trả phòng.');
                otpLoading.value = false;
                return;
            }
        } else if (currentAction.value === 'extend') {
            if (isExtendFormValid.value) {
                emit('extendContract', selectedContract.value.id, extendForm.value.months); // Emit sự kiện gia hạn
                closeExtendModal();
            } else {
                toast.error('Vui lòng kiểm tra lại thông tin gia hạn.');
                otpLoading.value = false;
                return;
            }
        }
        closeOTPModal(); // Đóng modal OTP
    } catch (error) {
        console.error('Lỗi khi xác minh OTP:', error);
        toast.error('Lỗi khi xác minh OTP. Vui lòng thử lại.');
    } finally {
        otpLoading.value = false; // Tắt trạng thái loading
    }
};

// Gửi yêu cầu OTP cho trả phòng
const requestOTPForReturn = async formData => {
    returnForm.value = { ...formData }; // Lưu dữ liệu form trả phòng
    if (!validateReturnForm()) {
        toast.error('Vui lòng nhập đầy đủ và đúng định dạng thông tin trả phòng.');
        return;
    }
    currentAction.value = 'return'; // Đặt hành động hiện tại là trả phòng
    await requestOTP(); // Gửi yêu cầu OTP
};
</script>

<style scoped>
/* CSS cho SweetAlert2 popup */
.swal-wide {
    width: 600px !important;
}

/* CSS cho nội dung SweetAlert2 */
.swal-html-container {
    text-align: left;
    font-size: 14px;
}

.swal-html-container ul {
    margin: 10px 0;
    padding-left: 20px;
}

.swal-html-container li {
    margin-bottom: 8px;
}
</style>
