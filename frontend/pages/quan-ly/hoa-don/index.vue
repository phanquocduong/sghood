<template>
    <div>
        <Titlebar title="Hóa đơn" />

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="dashboard-list-box margin-top-0">
                    <InvoiceFilter v-model:filter="filter" @update:filter="fetchItems" />
                    <h4>Quản lý hoá đơn</h4>
                    <InvoiceList :items="items" :is-loading="isLoading" />
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
const filter = ref({ sort: 'default', type: '', month: '', year: '' });
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
    console.log(filter.value);
    isLoading.value = true;
    try {
        const response = await $api('/invoices', { method: 'GET', params: filter.value });
        items.value = response.data;
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
