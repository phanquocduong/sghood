<template>
    <!-- Tiêu đề trang hiển thị thông tin hợp đồng và người ở cùng -->
    <Titlebar title="Hợp đồng - Người ở cùng" />

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="dashboard-list-box margin-top-0">
                <!-- Component hiển thị danh sách người ở cùng -->
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

// Cấu hình metadata cho trang, sử dụng layout 'management'
definePageMeta({
    layout: 'management'
});

const { $api } = useNuxtApp(); // Lấy instance API từ Nuxt
const { handleBackendError } = useApi(); // Sử dụng composable useApi để xử lý lỗi backend
const toast = useAppToast(); // Sử dụng composable để hiển thị thông báo
const route = useRoute(); // Lấy thông tin route hiện tại

// Khởi tạo các biến trạng thái
const tenants = ref([]); // Danh sách người ở cùng
const max_occupants = ref(0); // Số lượng người ở tối đa của hợp đồng
const isLoading = ref(false); // Trạng thái loading khi thực hiện các thao tác API

// Hàm lấy danh sách người ở cùng từ server
const fetchTenants = async () => {
    isLoading.value = true; // Bật trạng thái loading
    try {
        // Gửi yêu cầu GET để lấy danh sách người ở cùng và số lượng tối đa
        const response = await $api(`/contracts/${route.params.id}/tenants`, { method: 'GET' });
        tenants.value = response.data.tenants; // Cập nhật danh sách người ở cùng
        max_occupants.value = response.data.max_occupants; // Cập nhật số lượng tối đa
    } catch (error) {
        handleBackendError(error, toast); // Xử lý lỗi backend
    } finally {
        isLoading.value = false; // Tắt trạng thái loading
    }
};

// Hàm hủy đăng ký người ở cùng
const cancelTenant = async tenantId => {
    isLoading.value = true; // Bật trạng thái loading
    try {
        // Gửi yêu cầu POST để hủy người ở cùng
        await $api(`/contracts/${route.params.id}/tenants/${tenantId}/cancel`, {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value // Gửi token XSRF để bảo mật
            },
            body: { _method: 'PATCH' }
        });
        await fetchTenants(); // Tải lại danh sách người ở cùng
        toast.success('Hủy người ở cùng thành công'); // Hiển thị thông báo thành công
    } catch (error) {
        handleBackendError(error, toast); // Xử lý lỗi backend
    } finally {
        isLoading.value = false; // Tắt trạng thái loading
    }
};

// Hàm xác nhận người ở cùng chính thức vào ở
const confirmTenant = async tenantId => {
    isLoading.value = true; // Bật trạng thái loading
    try {
        // Gửi yêu cầu POST để xác nhận người ở cùng
        await $api(`/contracts/${route.params.id}/tenants/${tenantId}/confirm`, {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value // Gửi token XSRF để bảo mật
            },
            body: { _method: 'PATCH' }
        });
        await fetchTenants(); // Tải lại danh sách người ở cùng
        toast.success('Xác nhận người ở cùng vào ở thành công'); // Hiển thị thông báo thành công
    } catch (error) {
        handleBackendError(error, toast); // Xử lý lỗi backend
    } finally {
        isLoading.value = false; // Tắt trạng thái loading
    }
};

// Tải danh sách người ở cùng khi component được mount
onMounted(() => {
    fetchTenants();
});
</script>
