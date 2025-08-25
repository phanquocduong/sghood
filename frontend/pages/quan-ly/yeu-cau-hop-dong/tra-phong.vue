<template>
    <!-- Tiêu đề trang hiển thị thông tin về trả phòng -->
    <Titlebar title="Trả phòng" />

    <!-- Modal kiểm kê tài sản -->
    <InventoryModal
        :checkout="selectedCheckout"
        :show-rejection-form="showRejectionForm"
        :rejection-reason="rejectionReason"
        :confirm-loading="confirmLoading"
        :reject-loading="rejectLoading"
        @update:show-rejection-form="showRejectionForm = $event"
        @update:rejection-reason="rejectionReason = $event"
        @submit-approval="submitApproval"
        @submit-rejection="submitRejection"
    />

    <!-- Modal hiển thị thông tin ngân hàng -->
    <BankInfoModal :checkout="selectedCheckout" @close="closeBankInfoModal" @open-edit-bank-modal="openEditBankModal" />

    <!-- Modal chỉnh sửa thông tin ngân hàng -->
    <EditBankModal
        :checkout="selectedCheckout"
        :banks="banks"
        :update-loading="updateLoading"
        @close="closeEditBankModal"
        @update-bank-info="handleUpdateBankInfo"
    />

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="dashboard-list-box margin-top-0">
                <!-- Component hiển thị danh sách yêu cầu trả phòng -->
                <CheckoutList
                    :items="checkouts"
                    :is-loading="isLoading"
                    @cancel-checkout="cancelCheckout"
                    @open-inventory-popup="openInventoryPopup"
                    @open-bank-info-popup="openBankInfoPopup"
                    @confirm-left-room="confirmLeftRoom"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAppToast } from '~/composables/useToast';
import { useApi } from '~/composables/useApi';
import { useCookie } from '#app';
import TomSelect from 'tom-select';

// Cấu hình metadata cho trang, sử dụng layout 'management'
definePageMeta({
    layout: 'management'
});

const { $api } = useNuxtApp(); // Lấy instance API từ Nuxt
const config = useState('configs'); // Lấy cấu hình từ state
const checkouts = ref([]); // Danh sách yêu cầu trả phòng
const isLoading = ref(false); // Trạng thái loading khi thực hiện các thao tác API
const toast = useAppToast(); // Sử dụng composable để hiển thị thông báo
const { handleBackendError } = useApi(); // Sử dụng composable useApi để xử lý lỗi backend

// Khởi tạo các biến trạng thái
const selectedCheckout = ref({}); // Yêu cầu trả phòng được chọn
const showRejectionForm = ref(false); // Hiển thị form từ chối kiểm kê
const rejectionReason = ref(''); // Lý do từ chối kiểm kê
const confirmLoading = ref(false); // Trạng thái loading khi xác nhận kiểm kê
const rejectLoading = ref(false); // Trạng thái loading khi từ chối kiểm kê
const leaveLoading = ref(false); // Trạng thái loading khi xác nhận rời phòng
const updateLoading = ref(false); // Trạng thái loading khi cập nhật thông tin ngân hàng
const banks = ref(config.value.supported_banks); // Danh sách ngân hàng được hỗ trợ

// Hàm lấy danh sách yêu cầu trả phòng từ server
const fetchCheckouts = async () => {
    isLoading.value = true; // Bật trạng thái loading
    try {
        // Gửi yêu cầu GET để lấy danh sách yêu cầu trả phòng
        const response = await $api('/checkouts', { method: 'GET' });
        checkouts.value = response.data; // Cập nhật danh sách yêu cầu trả phòng
    } catch (error) {
        handleBackendError(error, toast); // Xử lý lỗi backend
    } finally {
        isLoading.value = false; // Tắt trạng thái loading
    }
};

// Hàm hủy yêu cầu trả phòng
const cancelCheckout = async id => {
    isLoading.value = true; // Bật trạng thái loading
    try {
        await $api(`/checkouts/${id}/cancel`, {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value // Gửi token XSRF để bảo mật
            },
            body: { _method: 'PATCH' }
        });
        await fetchCheckouts(); // Tải lại danh sách yêu cầu trả phòng
        toast.success('Hủy yêu cầu trả phòng thành công'); // Hiển thị thông báo thành công
    } catch (error) {
        handleBackendError(error, toast); // Xử lý lỗi backend
    } finally {
        isLoading.value = false; // Tắt trạng thái loading
    }
};

// Mở popup kiểm kê tài sản
const openInventoryPopup = item => {
    selectedCheckout.value = item; // Lưu yêu cầu trả phòng được chọn
    showRejectionForm.value = false; // Ẩn form từ chối
    rejectionReason.value = ''; // Xóa lý do từ chối

    if (!window.jQuery || !window.jQuery.fn.magnificPopup) {
        console.error('Magnific Popup không được tải');
        return;
    }

    // Mở Magnific Popup để hiển thị modal kiểm kê
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
        closeOnBgClick: false
    });
};

// Mở popup thông tin ngân hàng
const openBankInfoPopup = item => {
    selectedCheckout.value = item; // Lưu yêu cầu trả phòng được chọn
    if (!window.jQuery || !window.jQuery.fn.magnificPopup) {
        console.error('Magnific Popup không được tải');
        toast.error('Lỗi khi mở modal kiểm tra thông tin ngân hàng.');
        return;
    }
    // Mở Magnific Popup để hiển thị modal thông tin ngân hàng
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
        closeOnBgClick: false
    });
};

// Đóng modal
const closeModal = () => {
    if (window.jQuery && window.jQuery.fn.magnificPopup) {
        window.jQuery.magnificPopup.close(); // Đóng Magnific Popup
    }
};

// Đóng modal thông tin ngân hàng
const closeBankInfoModal = () => {
    if (window.jQuery && window.jQuery.fn.magnificPopup) {
        window.jQuery.magnificPopup.close(); // Đóng Magnific Popup
    }
    selectedCheckout.value = null; // Xóa yêu cầu trả phòng được chọn
};

// Xác nhận kiểm kê tài sản
const submitApproval = async () => {
    confirmLoading.value = true; // Bật trạng thái loading
    try {
        await $api(`/checkouts/${selectedCheckout.value.id}/confirm`, {
            method: 'POST',
            headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value },
            body: {
                status: 'Đồng ý',
                _method: 'PATCH'
            }
        });
        toast.success('Xác nhận kiểm kê thành công'); // Hiển thị thông báo thành công
        closeModal(); // Đóng modal
        await fetchCheckouts(); // Tải lại danh sách yêu cầu trả phòng
    } catch (error) {
        handleBackendError(error, toast); // Xử lý lỗi backend
    } finally {
        confirmLoading.value = false; // Tắt trạng thái loading
    }
};

// Từ chối kiểm kê tài sản
const submitRejection = async () => {
    if (!rejectionReason.value.trim()) {
        toast.error('Vui lòng nhập lý do từ chối'); // Hiển thị lỗi nếu không có lý do
        return;
    }

    rejectLoading.value = true; // Bật trạng thái loading
    try {
        // Gửi yêu cầu POST để từ chối kiểm kê
        await $api(`/checkouts/${selectedCheckout.value.id}/confirm`, {
            method: 'POST',
            headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value },
            body: {
                status: 'Từ chối',
                user_rejection_reason: rejectionReason.value,
                _method: 'PATCH'
            }
        });
        toast.success('Từ chối kiểm kê thành công'); // Hiển thị thông báo thành công
        closeModal(); // Đóng modal
        await fetchCheckouts(); // Tải lại danh sách yêu cầu trả phòng
    } catch (error) {
        handleBackendError(error, toast); // Xử lý lỗi backend
    } finally {
        rejectLoading.value = false; // Tắt trạng thái loading
    }
};

// Xác nhận người thuê đã rời phòng
const confirmLeftRoom = async item => {
    leaveLoading.value = true; // Bật trạng thái loading
    try {
        await $api(`/checkouts/${item.id}/left-room`, {
            method: 'POST',
            headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value },
            body: { _method: 'PATCH' }
        });
        toast.success('Xác nhận đã rời phòng thành công'); // Hiển thị thông báo thành công
        closeModal(); // Đóng modal
        await fetchCheckouts(); // Tải lại danh sách yêu cầu trả phòng
    } catch (error) {
        handleBackendError(error, toast); // Xử lý lỗi backend
    } finally {
        leaveLoading.value = false; // Tắt trạng thái loading
    }
};

// Mở modal chỉnh sửa thông tin ngân hàng
const openEditBankModal = () => {
    if (!selectedCheckout.value) {
        toast.error('Vui lòng chọn một yêu cầu trả phòng trước.');
        return;
    }
    if (!window.jQuery || !window.jQuery.fn.magnificPopup) {
        console.error('Magnific Popup không được tải');
        toast.error('Lỗi khi mở modal chỉnh sửa ngân hàng.');
        return;
    }
    // Mở Magnific Popup để hiển thị modal chỉnh sửa ngân hàng
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
                    // Khởi tạo TomSelect cho dropdown chọn ngân hàng
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
                            editBankForm.value.bank_name = value; // Cập nhật giá trị ngân hàng
                        }
                    });
                }
            }
        }
    });
};

// Đóng modal chỉnh sửa ngân hàng
const closeEditBankModal = () => {
    if (window.jQuery && window.jQuery.fn.magnificPopup) {
        window.jQuery.magnificPopup.close(); // Đóng Magnific Popup
    }
};

// Xử lý cập nhật thông tin ngân hàng
const handleUpdateBankInfo = async ({ id, bankInfo }) => {
    updateLoading.value = true; // Bật trạng thái loading
    try {
        const response = await $api(`/checkouts/${id}/update-bank`, {
            method: 'POST',
            headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value },
            body: {
                bank_info: bankInfo,
                _method: 'PATCH'
            }
        });
        toast.success(response.message); // Hiển thị thông báo thành công
        closeEditBankModal(); // Đóng modal chỉnh sửa ngân hàng
        await fetchCheckouts(); // Tải lại danh sách yêu cầu trả phòng
    } catch (error) {
        handleBackendError(error, toast); // Xử lý lỗi backend
    } finally {
        updateLoading.value = false; // Tắt trạng thái loading
    }
};

// Tải danh sách yêu cầu trả phòng khi component được mount
onMounted(() => {
    fetchCheckouts();
});
</script>
