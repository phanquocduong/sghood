<template>
    <Titlebar title="Đặt phòng" />

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="dashboard-list-box margin-top-0">
                <BookingFilter v-model:filter="filter" @update:filter="fetchBookings" />
                <BookingList :bookings="bookings" :is-loading="isLoading" @cancel-booking="cancelBooking" />
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
const toast = useAppToast();

const bookings = ref([]);
const filter = ref({ sort: 'default', status: '' });
const isLoading = ref(false);

const fetchBookings = async () => {
    isLoading.value = true;
    try {
        const response = await $api('/bookings', { method: 'GET', params: filter.value });
        bookings.value = response.data;
    } catch (error) {
        handleBackendError(error, toast);
    } finally {
        isLoading.value = false;
    }
};

const cancelBooking = async id => {
    isLoading.value = true;
    try {
        await $api(`/bookings/${id}/cancel`, {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            }
        });
        await fetchBookings();
        toast.success('Hủy đặt phòng thành công');
    } catch (error) {
        handleBackendError(error, toast);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchBookings();
});
</script>

<style scoped>
input#date-picker {
    border: 1px solid #dbdbdb;
    box-shadow: 0 1px 3px 0px rgba(0, 0, 0, 0.08);
}

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
