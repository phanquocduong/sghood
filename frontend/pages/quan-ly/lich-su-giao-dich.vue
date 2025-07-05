<template>
    <div>
        <Titlebar title="Giao dịch" />

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="dashboard-list-box margin-top-0">
                    <TransactionFilter v-model:filter="filter" @update:filter="fetchItems" />
                    <h4>Lịch sử giao dịch</h4>
                    <TransactionList :items="items" :is-loading="isLoading" />
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
const filter = ref({ sort: 'default', type: '' });
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
        const response = await $api('/transactions', { method: 'GET', params: filter.value });
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
