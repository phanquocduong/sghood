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
                <Pagination :current-page="currentPage" :total-pages="totalPages" @change:page="handlePageChange" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useAppToast } from '~/composables/useToast';

definePageMeta({
    layout: 'management'
});

const { $api } = useNuxtApp();
const items = ref([]);
const filter = ref({ sort: 'default', type: '', month: '', year: '' });
const isLoading = ref(false);
const toast = useAppToast();
const currentPage = ref(1);
const totalPages = ref(0);
const perPage = ref(10);

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
        const response = await $api('/invoices', {
            method: 'GET',
            params: {
                ...filter.value,
                page: currentPage.value,
                per_page: perPage.value
            }
        });
        items.value = response.data;
        currentPage.value = response.current_page;
        totalPages.value = response.total_pages;
    } catch (error) {
        handleBackendError(error);
    } finally {
        isLoading.value = false;
    }
};

const handlePageChange = page => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
        fetchItems();
    }
};

watch(
    filter,
    () => {
        currentPage.value = 1; // Reset về trang 1 khi bộ lọc thay đổi
        fetchItems();
    },
    { deep: true }
);

onMounted(() => {
    fetchItems();
});
</script>

<style scoped></style>
