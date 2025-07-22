<template>
    <div>
        <Titlebar title="Yêu cầu hoàn tiền" />

        <!-- Modal Dialog for QR Code -->
        <div id="small-dialog" class="zoom-anim-dialog mfp-hide">
            <div class="small-dialog-header">
                <h3>Kiểm tra thông tin chuyển khoản</h3>
                <p class="booking-subtitle">Vui lòng quét mã QR để kiểm tra thông tin chuyển khoản</p>
            </div>
            <div class="message-reply margin-top-0">
                <div class="modal-content">
                    <p>Vui lòng quét mã QR để kiểm tra thông tin chuyển khoản:</p>
                    <img :src="selectedItem?.qr_code_path" alt="Mã QR" style="max-width: 400px; margin: 0 auto; display: block" />
                    <p v-if="selectedItem?.bank_info" class="mt-3">
                        <strong>Thông tin ngân hàng:</strong>
                        <br />
                        <span v-html="formatBankInfo(selectedItem?.bank_info)"></span>
                    </p>
                    <p class="mt-3"><em>Nếu thông tin không đúng, vui lòng chỉnh sửa:</em></p>
                    <button class="button gray" @click="openEditBankModal">Chỉnh sửa thông tin chuyển khoản</button>
                </div>
                <div class="booking-actions">
                    <button @click="closeQRModal" class="button gray" type="button"><i class="fa fa-times"></i> Đóng</button>
                </div>
            </div>
        </div>

        <!-- Include EditBankModal -->
        <EditBankModal
            :refund-request="selectedItem"
            :banks="banks"
            :update-loading="updateLoading"
            @close="closeEditBankModal"
            @update-bank-info="handleUpdateBankInfo"
        />

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="dashboard-list-box margin-top-0">
                    <RefundRequestFilter v-model:filter="filter" @update:filter="fetchRefundRequests" />
                    <RefundRequestList
                        :items="refundRequests"
                        :is-loading="isLoading"
                        :update-loading="updateLoading"
                        :selected-item="selectedItem"
                        :banks="banks"
                        @open-qr-modal="openQRModal"
                        @update-bank-info="handleUpdateBankInfo"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useToast } from 'vue-toastification';
import { useFormatPrice } from '~/composables/useFormatPrice';

definePageMeta({
    layout: 'management'
});

const { $api } = useNuxtApp();
const { formatPrice } = useFormatPrice();
const config = useState('configs');
const refundRequests = ref([]);
const filter = ref({ sort: 'default', status: '' });
const isLoading = ref(false);
const updateLoading = ref(false);
const selectedItem = ref(null);
const banks = ref(config.value.supported_banks);
const toast = useToast();

const handleBackendError = error => {
    const data = error.response?._data;
    if (data?.error) {
        toast.error(data.error);
        return;
    }
    if (data?.errors) {
        Object.values(data.errors).forEach(err => toast.error(err[0]));
        return;
    }
    toast.error('Đã có lỗi xảy ra. Vui lòng thử lại.');
};

const fetchRefundRequests = async () => {
    isLoading.value = true;
    try {
        const response = await $api('/refund-requests', { method: 'GET', params: filter.value });
        refundRequests.value = response.data;
    } catch (error) {
        handleBackendError(error);
    } finally {
        isLoading.value = false;
    }
};

const formatBankInfo = bankInfo => {
    if (!bankInfo || typeof bankInfo !== 'object') return 'Không có thông tin';
    const fields = [
        bankInfo.bank_name ? `Ngân hàng: ${bankInfo.bank_name}` : '',
        bankInfo.account_number ? `Số tài khoản: ${bankInfo.account_number}` : '',
        bankInfo.account_holder ? `Chủ tài khoản: ${bankInfo.account_holder}` : ''
    ].filter(Boolean);
    return fields.join('<br>');
};

const openQRModal = item => {
    selectedItem.value = item;
    if (!window.jQuery || !window.jQuery.fn.magnificPopup) {
        console.error('Magnific Popup không được tải');
        toast.error('Lỗi khi mở modal QR.');
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

const closeQRModal = () => {
    if (window.jQuery && window.jQuery.fn.magnificPopup) {
        window.jQuery.magnificPopup.close();
    }
    selectedItem.value = null;
};

const openEditBankModal = () => {
    if (!selectedItem.value) {
        toast.error('Vui lòng chọn một yêu cầu hoàn tiền trước.');
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
    if (window.jQuery && window.jQuery.magnificPopup) {
        window.jQuery.magnificPopup.close();
    }
    selectedItem.value = null;
};

const handleUpdateBankInfo = async ({ id, bankInfo }) => {
    updateLoading.value = true;
    try {
        const response = await $api(`/refund-requests/${id}`, {
            method: 'POST',
            body: {
                ...bankInfo,
                _method: 'PATCH'
            }
        });
        toast.success(response.message);
        closeEditBankModal();
        await fetchRefundRequests();
    } catch (error) {
        handleBackendError(error);
    } finally {
        updateLoading.value = false;
    }
};

onMounted(() => {
    fetchRefundRequests();
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
</style>
