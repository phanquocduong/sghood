<template>
    <!-- Tiêu đề của trang lịch sử giao dịch -->
    <Titlebar title="Giao dịch" />

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="dashboard-list-box margin-top-0">
                <!-- Bộ lọc giao dịch, nhận và cập nhật giá trị filter -->
                <TransactionFilter v-model:filter="filter" @update:filter="fetchItems" />
                <!-- Tiêu đề danh sách lịch sử giao dịch -->
                <h4>Lịch sử giao dịch</h4>
                <!-- Danh sách các giao dịch, hiển thị dữ liệu và trạng thái loading -->
                <TransactionList :items="items" :is-loading="isLoading" />
            </div>
            <!-- Phân trang cho danh sách giao dịch -->
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

// Khởi tạo các biến reactive
const { $api } = useNuxtApp(); // Lấy instance API từ Nuxt
const { handleBackendError } = useApi(); // Hàm xử lý lỗi từ backend
const items = ref([]); // Danh sách giao dịch
const filter = ref({ sort: 'default', type: '' }); // Bộ lọc giao dịch (sắp xếp, loại giao dịch)
const isLoading = ref(false); // Trạng thái loading khi lấy dữ liệu
const toast = useAppToast(); // Hàm hiển thị thông báo
const currentPage = ref(1); // Trang hiện tại
const totalPages = ref(0); // Tổng số trang
const perPage = ref(10); // Số lượng giao dịch mỗi trang

// Hàm lấy danh sách giao dịch từ API
const fetchItems = async () => {
    isLoading.value = true; // Bật trạng thái loading
    try {
        // Gửi yêu cầu GET đến endpoint '/transactions' với các tham số lọc và phân trang
        const response = await $api('/transactions', {
            method: 'GET',
            params: {
                ...filter.value, // Các tham số lọc (sort, type)
                page: currentPage.value, // Trang hiện tại
                per_page: perPage.value // Số lượng giao dịch mỗi trang
            }
        });
        items.value = response.data; // Cập nhật danh sách giao dịch
        currentPage.value = response.current_page; // Cập nhật trang hiện tại
        totalPages.value = response.total_pages; // Cập nhật tổng số trang
    } catch (error) {
        handleBackendError(error, toast); // Xử lý lỗi nếu có
    } finally {
        isLoading.value = false; // Tắt trạng thái loading
    }
};

// Xử lý sự kiện thay đổi trang
const handlePageChange = page => {
    if (page >= 1 && page <= totalPages.value) {
        // Kiểm tra trang hợp lệ
        currentPage.value = page; // Cập nhật trang hiện tại
        fetchItems(); // Gọi lại hàm lấy dữ liệu
    }
};

// Theo dõi thay đổi của bộ lọc
watch(
    filter,
    () => {
        currentPage.value = 1; // Reset về trang 1 khi bộ lọc thay đổi
        fetchItems(); // Gọi lại hàm lấy dữ liệu
    },
    { deep: true } // Theo dõi sâu để phát hiện thay đổi trong object filter
);

// Gọi hàm fetchItems khi component được mounted
onMounted(() => {
    fetchItems();
});
</script>
