<template>
    <div>
        <Titlebar title="Yêu cầu hoàn tiền" />

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="dashboard-list-box margin-top-0">
                    <RefundRequestFilter v-model:filter="filter" @update:filter="fetchRefundRequests" />
                    <RefundRequestList
                        :items="refundRequests"
                        :is-loading="isLoading"
                        :update-loading="updateLoading"
                        v-model:showEditBankModal="showEditBankModal"
                        @updateBankInfo="handleUpdateBankInfo"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useToast } from 'vue-toastification';

definePageMeta({
    layout: 'management'
});

const { $api } = useNuxtApp();
const refundRequests = ref([]);
const filter = ref({ sort: 'default', status: '' });
const isLoading = ref(false);
const updateLoading = ref(false);
const showEditBankModal = ref(false);
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
        showEditBankModal.value = false;
        toast.success(response.message);
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
