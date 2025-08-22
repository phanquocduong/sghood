<template>
    <div>
        <Titlebar title="Gia hạn hợp đồng" />

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="dashboard-list-box margin-top-0">
                    <ContractExtensionList :extensions="extensions" :is-loading="isLoading" @cancel-extension="cancelExtension" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAppToast } from '~/composables/useToast';
import { useApi } from '~/composables/useApi';

definePageMeta({
    layout: 'management'
});

const { $api } = useNuxtApp();
const { handleBackendError } = useApi();
const extensions = ref([]);
const isLoading = ref(false);
const toast = useAppToast();

const fetchExtensions = async () => {
    isLoading.value = true;
    try {
        const response = await $api('/contract-extensions', { method: 'GET' });
        extensions.value = response.data;
    } catch (error) {
        handleBackendError(error, toast);
    } finally {
        isLoading.value = false;
    }
};

const cancelExtension = async id => {
    isLoading.value = true;
    try {
        const response = await $api(`/contract-extensions/${id}/cancel`, {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            }
        });
        await fetchExtensions();
        toast.success(response.message);
    } catch (error) {
        handleBackendError(error, toast);
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
