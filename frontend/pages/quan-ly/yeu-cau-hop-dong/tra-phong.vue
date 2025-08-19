<template>
    <div>
        <Titlebar title="Trả phòng" />

        <!-- Inventory Modal -->
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

        <!-- Bank Info Modal -->
        <BankInfoModal :checkout="selectedCheckout" @close="closeBankInfoModal" @open-edit-bank-modal="openEditBankModal" />

        <!-- Edit Bank Modal -->
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
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAppToast } from '~/composables/useToast';
import { useApi } from '~/composables/useApi';
import TomSelect from 'tom-select';

definePageMeta({
    layout: 'management'
});

const { $api } = useNuxtApp();
const config = useState('configs');
const checkouts = ref([]);
const isLoading = ref(false);
const toast = useAppToast();
const { handleBackendError } = useApi();

const selectedCheckout = ref({});
const showRejectionForm = ref(false);
const rejectionReason = ref('');
const confirmLoading = ref(false);
const rejectLoading = ref(false);
const leaveLoading = ref(false);
const updateLoading = ref(false);
const banks = ref(config.value.supported_banks);

const fetchCheckouts = async () => {
    isLoading.value = true;
    try {
        const response = await $api('/checkouts', { method: 'GET' });
        checkouts.value = response.data;
    } catch (error) {
        handleBackendError(error, toast);
    } finally {
        isLoading.value = false;
    }
};

const cancelCheckout = async id => {
    isLoading.value = true;
    try {
        await $api(`/checkouts/${id}/cancel`, {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            }
        });
        await fetchCheckouts();
        toast.success('Hủy yêu cầu trả phòng thành công');
    } catch (error) {
        handleBackendError(error, toast);
    } finally {
        isLoading.value = false;
    }
};

const openInventoryPopup = item => {
    selectedCheckout.value = item;
    showRejectionForm.value = false;
    rejectionReason.value = '';

    if (!window.jQuery || !window.jQuery.fn.magnificPopup) {
        console.error('Magnific Popup không được tải');
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
        closeOnBgClick: false
    });
};

const openBankInfoPopup = item => {
    selectedCheckout.value = item;
    if (!window.jQuery || !window.jQuery.fn.magnificPopup) {
        console.error('Magnific Popup không được tải');
        toast.error('Lỗi khi mở modal kiểm tra thông tin ngân hàng.');
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
        closeOnBgClick: false
    });
};

const closeModal = () => {
    if (window.jQuery && window.jQuery.fn.magnificPopup) {
        window.jQuery.magnificPopup.close();
    }
};

const closeBankInfoModal = () => {
    if (window.jQuery && window.jQuery.fn.magnificPopup) {
        window.jQuery.magnificPopup.close();
    }
    selectedCheckout.value = null;
};

const submitApproval = async () => {
    confirmLoading.value = true;
    try {
        await $api(`/checkouts/${selectedCheckout.value.id}/confirm`, {
            method: 'POST',
            headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value },
            body: { status: 'Đồng ý' }
        });
        toast.success('Xác nhận kiểm kê thành công');
        closeModal();
        await fetchCheckouts();
    } catch (error) {
        handleBackendError(error, toast);
    } finally {
        confirmLoading.value = false;
    }
};

const submitRejection = async () => {
    if (!rejectionReason.value.trim()) {
        toast.error('Vui lòng nhập lý do từ chối');
        return;
    }

    rejectLoading.value = true;
    try {
        await $api(`/checkouts/${selectedCheckout.value.id}/confirm`, {
            method: 'POST',
            headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value },
            body: {
                status: 'Từ chối',
                user_rejection_reason: rejectionReason.value
            }
        });
        toast.success('Từ chối kiểm kê thành công');
        closeModal();
        await fetchCheckouts();
    } catch (error) {
        handleBackendError(error, toast);
    } finally {
        rejectLoading.value = false;
    }
};

const confirmLeftRoom = async item => {
    leaveLoading.value = true;
    try {
        await $api(`/checkouts/${item.id}/left-room`, {
            method: 'POST',
            headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value }
        });
        toast.success('Xác nhận đã rời phòng thành công');
        closeModal();
        await fetchCheckouts();
    } catch (error) {
        handleBackendError(error, toast);
    } finally {
        leaveLoading.value = false;
    }
};

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
                }
            }
        }
    });
};

const closeEditBankModal = () => {
    if (window.jQuery && window.jQuery.fn.magnificPopup) {
        window.jQuery.magnificPopup.close();
    }
};

const handleUpdateBankInfo = async ({ id, bankInfo }) => {
    updateLoading.value = true;
    try {
        const response = await $api(`/checkouts/${id}/update-bank`, {
            method: 'POST',
            headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value },
            body: {
                bank_info: bankInfo
            }
        });
        toast.success(response.message);
        closeEditBankModal();
        await fetchCheckouts();
    } catch (error) {
        handleBackendError(error, toast);
    } finally {
        updateLoading.value = false;
    }
};

onMounted(() => {
    fetchCheckouts();
});
</script>

<style></style>
