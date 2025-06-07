<template>
    <div>
        <Titlebar title="Đặt phòng" />

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="dashboard-list-box margin-top-0">
                    <BookingFilter v-model:filter="filter" @update:filter="fetchBookings" />
                    <BookingList :bookings="bookings" :is-loading="isLoading" @reject-booking="rejectBooking" />
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
const bookings = ref([]);
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

const fetchBookings = async () => {
    isLoading.value = true;
    try {
        const response = await $api('/bookings', { method: 'GET', params: filter.value });
        bookings.value = response.data;
    } catch (error) {
        handleBackendError(error);
    } finally {
        isLoading.value = false;
    }
};

const rejectBooking = async id => {
    isLoading.value = true;
    try {
        await $api(`/bookings/${id}/reject`, {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            }
        });
        await fetchBookings();
        toast.success('Huỷ đặt phòng thành công');
    } catch (error) {
        handleBackendError(error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchBookings();
});
</script>

<style scoped></style>
