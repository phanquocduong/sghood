<template>
    <!-- Hiển thị loading khi đang tải dữ liệu -->
    <Loading :is-loading="loading" />

    <div class="contract-page">
        <!-- Tiêu đề trang hiển thị số hợp đồng và trạng thái -->
        <Titlebar :title="`Hợp đồng #${contract?.id} (${contract?.status})`" />

        <div class="row">
            <!-- Cột chứa nội dung hợp đồng -->
            <div :class="contract?.status === 'Chờ xác nhận' ? 'col-lg-9 col-md-12' : 'col-lg-12 col-md-12'" class="contract-column">
                <!-- Hiển thị loading khi đang quét ảnh CCCD -->
                <div v-if="extractLoading" class="extract-loading-overlay">
                    <p>Đang quét ảnh căn cước...</p>
                </div>
                <!-- Nội dung hợp đồng được render dưới dạng HTML -->
                <div v-else ref="contractContainer" v-html="contract?.content" @input="handleInput"></div>

                <!-- Component chữ ký hiển thị khi trạng thái là "Chờ ký" -->
                <ContractSignature
                    v-if="contract?.status === 'Chờ ký'"
                    @signature-saved="signature => (signatureData = signature)"
                    @signature-cleared="() => (signatureData = null)"
                    @sign-contract="signContract"
                    :save-loading="saveLoading"
                    :signature-data="signatureData"
                />

                <!-- Nút kết thúc sớm hợp đồng (hiển thị khi hợp đồng đang hoạt động và không gần hết hạn) -->
                <div
                    v-if="
                        contract?.status === 'Hoạt động' &&
                        !isNearExpiration(contract?.end_date) &&
                        contract?.latest_extension_status !== 'Chờ duyệt' &&
                        (contract?.has_checkout === null || (contract?.has_checkout !== null && contract?.latest_checkout_status !== null))
                    "
                    class="d-flex justify-content-center margin-top-20"
                >
                    <button @click="openConfirmEarlyTerminationPopup(contract.id)" class="button">
                        <i class="sl sl-icon-close"></i> Kết thúc sớm
                    </button>
                </div>
            </div>

            <!-- Component tải lên giấy tờ tùy thân (hiển thị khi trạng thái là "Chờ xác nhận") -->
            <IdentityUpload
                v-if="contract?.status === 'Chờ xác nhận'"
                :identity-document="identityDocument"
                :is-form-complete="isFormComplete"
                :save-loading="saveLoading"
                :bypass-extract="bypassExtract"
                @save-contract="saveContract"
                @identity-upload="handleIdentityUpload"
            />
        </div>

        <!-- Modal xác minh OTP -->
        <OTPModal :phone-number="phoneNumber" :loading="saveLoading" v-model:otp-code="otpCode" @confirm="verifyOTPAndExecute" />
    </div>

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="dashboard-list-box margin-top-15">
                <!-- Danh sách phụ lục hợp đồng -->
                <ExtensionList v-if="contract?.active_extensions.length !== 0" :contract="contract" @open-popup="openPopup" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { useHead } from '@unhead/vue';
import { shallowRef, ref, computed, onMounted } from 'vue';
import { useAppToast } from '~/composables/useToast';
import { useRoute, useRouter } from 'vue-router';
import { useContract } from '~/composables/useContract';
import Swal from 'sweetalert2';
import { useFormatPrice } from '~/composables/useFormatPrice';
import { useContractUtils } from '~/composables/useContractUtils';

// Định nghĩa layout cho trang
definePageMeta({
    layout: 'management' // Sử dụng layout 'management'
});

// Cấu hình thẻ <head> để thêm các liên kết CSS
useHead({
    link: [
        {
            rel: 'stylesheet',
            href: 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
            integrity: 'sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM',
            crossorigin: 'anonymous'
        },
        {
            rel: 'stylesheet',
            href: 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css',
            integrity: 'sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA==',
            crossorigin: 'anonymous'
        }
    ]
});

// Khởi tạo các biến và composables
const route = useRoute(); // Lấy thông tin route hiện tại
const router = useRouter(); // Lấy instance router
const toast = useAppToast(); // Hàm hiển thị thông báo
const { $dropzone, $api } = useNuxtApp(); // Lấy instance Dropzone và API
const { formatPrice } = useFormatPrice(); // Hàm định dạng giá tiền
const { isNearExpiration } = useContractUtils(); // Hàm kiểm tra hợp đồng gần hết hạn

// Khởi tạo các biến reactive
const loading = ref(false); // Trạng thái loading khi tải dữ liệu
const extractLoading = ref(false); // Trạng thái loading khi quét ảnh CCCD
const saveLoading = ref(false); // Trạng thái loading khi lưu hợp đồng
const contract = shallowRef(null); // Thông tin hợp đồng
const contractContainer = ref(null); // Ref cho container nội dung hợp đồng
const dropzoneInstance = ref(null); // Instance của Dropzone
const identityImages = ref([]); // Danh sách ảnh CCCD
const signatureData = ref(null); // Dữ liệu chữ ký
const identityDocument = ref({
    full_name: '',
    year_of_birth: '',
    identity_number: '',
    date_of_issue: '',
    place_of_issue: '',
    permanent_address: '',
    has_valid: false // Trạng thái hợp lệ của CCCD
});

// Kiểm tra xem form giấy tờ tùy thân đã hoàn thiện chưa
const isFormComplete = computed(() =>
    Object.values(identityDocument.value)
        .slice(0, -1)
        .every(value => value)
);

// Sử dụng composable useContract để xử lý logic hợp đồng
const {
    fetchContract,
    signContract,
    verifyOTPAndExecute,
    saveContract,
    handleIdentityUpload,
    phoneNumber,
    otpCode,
    bypassExtract,
    earlyTermination
} = useContract({
    contract,
    signatureData,
    identityDocument,
    identityImages,
    contractContainer,
    loading,
    extractLoading,
    saveLoading,
    toast,
    router,
    route,
    dropzoneInstance
});

// Xử lý sự kiện input trong nội dung hợp đồng
const handleInput = event => {
    if (bypassExtract.value) {
        const input = event.target;
        const { name, value } = input;
        if (name in identityDocument.value) {
            identityDocument.value[name] = value; // Cập nhật thông tin giấy tờ tùy thân
        }
    }
};

// Mở popup xác nhận kết thúc sớm hợp đồng
const openConfirmEarlyTerminationPopup = async id => {
    const result = await Swal.fire({
        title: 'Xác nhận kết thúc hợp đồng sớm',
        html: `
            <p><strong>Lưu ý quan trọng:</strong> Việc kết thúc hợp đồng sớm sẽ có các hậu quả sau:</p>
            <ul style="text-align: left;">
                <li><strong>Tiền cọc không được hoàn lại:</strong> Toàn bộ số tiền cọc (${formatPrice(
                    contract.value.deposit_amount
                )}) sẽ không được hoàn trả dưới bất kỳ hình thức nào.</li>
                <li><strong>Rời khỏi phòng sớm:</strong> Bạn cần rời khỏi phòng trong vòng <strong>3 ngày</strong> kể từ khi yêu cầu được xác nhận để hỗ trợ việc kiểm kê và sửa chữa phòng cho khách thuê mới.</li>
                <li><strong>Nghĩa vụ tài chính:</strong> Bạn cần thanh toán toàn bộ các hóa đơn chưa thanh toán trước khi kết thúc hợp đồng. Nếu thời gian hiện tại từ ngày 27 cuối tháng đến ngày 5 đầu tháng, vui lòng đợi SGHood tạo hóa đơn tháng cuối để thanh toán trước khi yêu cầu kết thúc sớm.</li>
                <li><strong>Lịch sử thuê:</strong> Việc kết thúc hợp đồng sớm có thể ảnh hưởng đến hồ sơ thuê phòng của bạn, có thể tác động đến các giao dịch thuê trong tương lai.</li>
            </ul>
            <p>Bạn có chắc chắn muốn tiếp tục yêu cầu kết thúc hợp đồng sớm?</p>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xác nhận',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#f91942',
        cancelButtonColor: '#e0e0e0',
        customClass: {
            popup: 'swal-wide',
            htmlContainer: 'swal-html-container'
        }
    });

    if (result.isConfirmed) {
        await earlyTermination(id); // Gọi hàm kết thúc sớm hợp đồng
    }
};

// Khởi tạo khi component được mount
onMounted(async () => {
    await fetchContract(); // Lấy thông tin hợp đồng
    dropzoneInstance.value = new $dropzone('#dropzone-upload', {
        url: '/',
        autoProcessQueue: true,
        maxFilesize: 5,
        acceptedFiles: 'image/*',
        clickable: !identityDocument.value.has_valid, // Chỉ cho phép click nếu CCCD chưa hợp lệ
        dictDefaultMessage: '<i class="sl sl-icon-plus"></i>Tải lên 2 ảnh căn cước công dân mặt trước và mặt sau',
        init() {
            this.on('queuecomplete', () => {
                const files = [...this.getQueuedFiles(), ...this.getAcceptedFiles()];
                if (files.length) handleIdentityUpload(files); // Xử lý ảnh CCCD khi tải lên
            });
            this.on('error', (file, message) => console.error('Error uploading file:', message)); // Xử lý lỗi tải lên
            if (identityDocument.value.has_valid) this.disable(); // Vô hiệu hóa Dropzone nếu CCCD đã hợp lệ
        }
    });
});
</script>

<style scoped>
/* CSS cho trang chi tiết hợp đồng */
.contract-page {
    padding: 20px;
}

.contract-column {
    position: relative;
}

/* CSS cho overlay khi quét CCCD */
.extract-loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #f7f7f7;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    transition: opacity 0.3s ease;
}

.extract-loading-overlay p {
    font-size: 16px;
    color: #333;
}

/* Responsive styles */
@media (max-width: 992px) {
    .contract-page {
        padding: 15px;
    }

    .contract-column {
        padding: 0 10px;
    }

    .signature-section {
        margin-top: 15px;
    }
}

@media (max-width: 768px) {
    .contract-page {
        padding: 10px;
    }

    .extract-loading-overlay p {
        font-size: 14px;
    }
}

@media (max-width: 576px) {
    .contract-page {
        padding: 5px;
    }

    .extract-loading-overlay p {
        font-size: 12px;
    }
}
</style>

<style>
@import '~/public/css/contract-details.css';
</style>
