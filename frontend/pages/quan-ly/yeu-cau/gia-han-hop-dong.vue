<template>
    <div>
        <Titlebar title="Yêu cầu gia hạn hợp đồng" />

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="dashboard-list-box margin-top-0">
                    <ContractExtensionFilter v-model:filter="filter" @update:filter="fetchExtensions" />
                    <ContractExtensionList :extensions="extensions" :is-loading="isLoading" @reject-extension="rejectExtension" />
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
const extensions = ref([]);
const filter = ref({ sort: 'default', status: '' });
const isLoading = ref(false);
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

const fetchExtensions = async () => {
    isLoading.value = true;
    try {
        const response = await $api('/contract-extensions', { method: 'GET', params: filter.value });
        extensions.value = response.data;
        console.log(extensions.value);
    } catch (error) {
        handleBackendError(error);
    } finally {
        isLoading.value = false;
    }
};

const rejectExtension = async id => {
    isLoading.value = true;
    try {
        const response = await $api(`/contract-extensions/${id}/reject`, {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            }
        });
        await fetchExtensions();
        toast.success(response.message);
    } catch (error) {
        handleBackendError(error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchExtensions();
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
