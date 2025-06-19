<template>
    <div>
        <Titlebar title="Hợp đồng" />

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="dashboard-list-box margin-top-0">
                    <ContractList :items="items" :is-loading="isLoading" @reject-item="rejectItem" @open-popup="openPopup" />
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
const items = ref([]);
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

const fetchItems = async () => {
    isLoading.value = true;
    try {
        const response = await $api('/contracts', { method: 'GET' });
        items.value = response.data;
    } catch (error) {
        handleBackendError(error);
    } finally {
        isLoading.value = false;
    }
};

const rejectItem = async ({ id }) => {
    isLoading.value = true;
    try {
        await $api(`/contracts/${id}/reject`, {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            }
        });
        await fetchItems();
        toast.success(`Hủy hợp đồng thành công`);
    } catch (error) {
        handleBackendError(error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchItems();
});
</script>

<style scoped></style>
