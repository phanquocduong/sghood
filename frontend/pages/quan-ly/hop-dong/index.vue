<template>
    <div>
        <Titlebar title="Hợp đồng" />

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="dashboard-list-box margin-top-0">
                    <ContractList
                        :items="items"
                        :is-loading="isLoading"
                        @open-popup="openPopup"
                        @reject-item="rejectItem"
                        @extend-contract="extendContract"
                        @return-contract="returnContract"
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

const rejectItem = async id => {
    isLoading.value = true;
    try {
        await $api(`/contracts/${id}/reject`, {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            }
        });
        toast.success(`Hủy hợp đồng thành công`);
        await fetchItems();
    } catch (error) {
        handleBackendError(error);
    } finally {
        isLoading.value = false;
    }
};

const extendContract = async id => {
    isLoading.value = true;
    try {
        await $api(`/contracts/${id}/extend`, {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            }
        });
        toast.success('Yêu cầu gia hạn hợp đồng đã được gửi.');
        await fetchItems();
    } catch (error) {
        handleBackendError(error);
    } finally {
        isLoading.value = false;
    }
};

const returnContract = async (contractId, data) => {
    isLoading.value = true;
    try {
        await $api(`/contracts/${contractId}/return`, {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            },
            body: data
        });
        toast.success('Yêu cầu trả phòng và hoàn tiền cọc đã được gửi.');
        await fetchItems();
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
