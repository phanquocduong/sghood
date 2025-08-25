<template>
    <!-- Tiêu đề trang hiển thị thông tin về hóa đơn -->
    <Titlebar title="Hóa đơn" />

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="dashboard-list-box margin-top-0">
                <!-- Component bộ lọc hóa đơn -->
                <InvoiceFilter v-model:filter="filter" @update:filter="fetchItems" />
                <!-- Tiêu đề danh sách hóa đơn -->
                <h4>Quản lý hoá đơn</h4>
                <!-- Component hiển thị danh sách hóa đơn -->
                <InvoiceList :items="items" :is-loading="isLoading" />
            </div>
            <!-- Component phân trang -->
            <Pagination :current-page="currentPage" :total-pages="totalPages" @change:page="handlePageChange" />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useAppToast } from '~/composables/useToast';
import { useApi } from '~/composables/useApi';

// Cấu hình metadata cho trang, sử dụng layout 'management'
definePageMeta({
    layout: 'management'
});

const { $api } = useNuxtApp(); // Lấy instance API từ Nuxt
const { handleBackendError } = useApi(); // Sử dụng composable useApi để xử lý lỗi backend
const items = ref([]); // Danh sách các hóa đơn
const filter = ref({ sort: 'default', type: '', month: '', year: '' }); // Bộ lọc hóa đơn
const isLoading = ref(false); // Trạng thái loading khi thực hiện các thao tác API
const toast = useAppToast(); // Sử dụng composable để hiển thị thông báo
const currentPage = ref(1); // Trang hiện tại
const totalPages = ref(0); // Tổng số trang
const perPage = ref(10); // Số lượng mục trên mỗi trang

// Hàm lấy danh sách hóa đơn từ server
const fetchItems = async () => {
    isLoading.value = true; // Bật trạng thái loading
    try {
        // Gửi yêu cầu GET để lấy danh sách hóa đơn với các tham số lọc
        const response = await $api('/invoices', {
            method: 'GET',
            params: {
                ...filter.value, // Áp dụng bộ lọc
                page: currentPage.value, // Trang hiện tại
                per_page: perPage.value // Số mục trên mỗi trang
            }
        });
        items.value = response.data; // Cập nhật danh sách hóa đơn
        currentPage.value = response.current_page; // Cập nhật trang hiện tại
        totalPages.value = response.total_pages; // Cập nhật tổng số trang
    } catch (error) {
        handleBackendError(error, toast); // Xử lý lỗi backend
    } finally {
        isLoading.value = false; // Tắt trạng thái loading
    }
};

// Hàm xử lý thay đổi trang
const handlePageChange = page => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page; // Cập nhật trang hiện tại
        fetchItems(); // Tải lại danh sách hóa đơn
    }
};

// Theo dõi thay đổi của bộ lọc
watch(
    filter,
    () => {
        currentPage.value = 1; // Reset về trang 1 khi bộ lọc thay đổi
        fetchItems(); // Tải lại danh sách hóa đơn
    },
    { deep: true } // Theo dõi sâu để phát hiện thay đổi trong object filter
);

// Tải danh sách hóa đơn khi component được mount
onMounted(() => {
    fetchItems();
});
</script>
