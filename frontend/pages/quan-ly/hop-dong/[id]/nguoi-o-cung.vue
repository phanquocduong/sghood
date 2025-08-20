<template>
    <Titlebar title="Hợp đồng - Người ở cùng" />

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="dashboard-list-box margin-top-0">
                <ContractTenantList
                    :tenants="tenants"
                    :is-loading="isLoading"
                    :contract-id="route.params.id"
                    :max-occupants="max_occupants"
                    @cancel-tenant="cancelTenant"
                    @add-tenant="fetchTenants()"
                    @confirm-tenant="confirmTenant"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAppToast } from '~/composables/useToast';
import { useApi } from '~/composables/useApi';
import { useCookie } from '#app';

definePageMeta({
    layout: 'management'
});

const { $api } = useNuxtApp();
const { handleBackendError } = useApi();
const toast = useAppToast();
const route = useRoute();

const tenants = ref([]);
const max_occupants = ref(0);
const isLoading = ref(false);

const fetchTenants = async () => {
    isLoading.value = true;
    try {
        const response = await $api(`/contracts/${route.params.id}/tenants`, { method: 'GET' });
        tenants.value = response.data.tenants;
        max_occupants.value = response.data.max_occupants;
    } catch (error) {
        handleBackendError(error, toast);
    } finally {
        isLoading.value = false;
    }
};

const cancelTenant = async tenantId => {
    isLoading.value = true;
    try {
        await $api(`/contracts/${route.params.id}/tenants/${tenantId}/cancel`, {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            }
        });
        await fetchTenants();
        toast.success('Hủy người ở cùng thành công');
    } catch (error) {
        handleBackendError(error, toast);
    } finally {
        isLoading.value = false;
    }
};

const confirmTenant = async tenantId => {
    isLoading.value = true;
    try {
        await $api(`/contracts/${route.params.id}/tenants/${tenantId}/confirm`, {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            }
        });
        await fetchTenants();
        toast.success('Xác nhận người ở cùng vào ở thành công');
    } catch (error) {
        handleBackendError(error, toast);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchTenants();
});
</script>

<style scoped></style>
