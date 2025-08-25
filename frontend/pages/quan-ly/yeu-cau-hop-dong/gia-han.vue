<template>
    <div>
        <!-- Tiêu đề trang hiển thị thông tin về gia hạn hợp đồng -->
        <Titlebar title="Gia hạn hợp đồng" />

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="dashboard-list-box margin-top-0">
                    <!-- Component hiển thị danh sách các yêu cầu gia hạn -->
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
import { useCookie } from '#app';

// Cấu hình metadata cho trang, sử dụng layout 'management'
definePageMeta({
    layout: 'management'
});

const { $api } = useNuxtApp(); // Lấy instance API từ Nuxt
const { handleBackendError } = useApi(); // Sử dụng composable useApi để xử lý lỗi backend
const extensions = ref([]); // Danh sách các yêu cầu gia hạn hợp đồng
const isLoading = ref(false); // Trạng thái loading khi thực hiện các thao tác API
const toast = useAppToast(); // Sử dụng composable để hiển thị thông báo

// Hàm lấy danh sách yêu cầu gia hạn từ server
const fetchExtensions = async () => {
    isLoading.value = true; // Bật trạng thái loading
    try {
        // Gửi yêu cầu GET để lấy danh sách yêu cầu gia hạn
        const response = await $api('/contract-extensions', { method: 'GET' });
        extensions.value = response.data; // Cập nhật danh sách yêu cầu gia hạn
    } catch (error) {
        handleBackendError(error, toast); // Xử lý lỗi backend
    } finally {
        isLoading.value = false; // Tắt trạng thái loading
    }
};

// Hàm hủy yêu cầu gia hạn
const cancelExtension = async id => {
    isLoading.value = true; // Bật trạng thái loading
    try {
        const response = await $api(`/contract-extensions/${id}/cancel`, {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value // Gửi token XSRF để bảo mật
            },
            body: { _method: 'PATCH' }
        });
        await fetchExtensions(); // Tải lại danh sách yêu cầu gia hạn
        toast.success(response.message); // Hiển thị thông báo thành công
    } catch (error) {
        handleBackendError(error, toast); // Xử lý lỗi backend
    } finally {
        isLoading.value = false; // Tắt trạng thái loading
    }
};

// Tải danh sách yêu cầu gia hạn khi component được mount
onMounted(() => {
    fetchExtensions();
});
</script>

<style scoped>
/* Style cho spinner loading */
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

/* Hiệu ứng quay cho spinner */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Style cho nút bị vô hiệu hóa */
.button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
