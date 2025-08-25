<template>
    <!-- Tiêu đề danh sách hợp đồng -->
    <h4>Quản lý hợp đồng</h4>

    <!-- Hiển thị loading khi đang tải dữ liệu -->
    <Loading :is-loading="isLoading" />

    <ul>
        <!-- Hiển thị từng hợp đồng -->
        <ContractItem
            v-for="item in items"
            :key="item.id"
            :item="item"
            :today="today"
            @cancel-contract="handleCancelContract"
            @extend-contract="handleExtendContract"
            @return-contract="handleReturnContract"
            @early-termination="handleEarlyTermination"
            @download-pdf="downloadPdf"
        />
        <!-- Thông báo khi không có hợp đồng -->
        <div v-if="!items.length" class="col-md-12 text-center">
            <p>Chưa có hợp đồng nào.</p>
        </div>
    </ul>
</template>

<script setup>
import { computed } from 'vue';
import { useAppToast } from '~/composables/useToast';

// Lấy instance của API và toast
const { $api } = useNuxtApp();
const toast = useAppToast();

// Định nghĩa props
const props = defineProps({
    items: {
        type: Array,
        required: true // Danh sách hợp đồng
    },
    isLoading: {
        type: Boolean,
        required: true // Trạng thái loading
    }
});

// Định nghĩa emits
const emit = defineEmits(['cancelContract', 'extendContract', 'returnContract', 'earlyTermination']);

// Ngày hiện tại
const today = computed(() => new Date().toISOString().split('T')[0]);

// Xử lý hủy hợp đồng
const handleCancelContract = id => {
    emit('cancelContract', id); // Emit sự kiện hủy hợp đồng
};

// Xử lý gia hạn hợp đồng
const handleExtendContract = (id, months) => {
    emit('extendContract', id, months); // Emit sự kiện gia hạn hợp đồng
};

// Xử lý trả phòng
const handleReturnContract = (id, data) => {
    emit('returnContract', id, data); // Emit sự kiện trả phòng
};

// Xử lý kết thúc sớm hợp đồng
const handleEarlyTermination = id => {
    emit('earlyTermination', id); // Emit sự kiện kết thúc sớm
};

// Tải file PDF hợp đồng
const downloadPdf = async id => {
    try {
        const response = await $api(`/contracts/${id}/download-pdf`, { method: 'GET' }); // Gọi API tải PDF
        window.open(response.data.file_url, '_blank'); // Mở file PDF trong tab mới
    } catch (error) {
        toast.error(error.response?._data?.error || 'Đã có lỗi xảy ra khi tải PDF.'); // Hiển thị lỗi nếu có
    }
};
</script>

<style>
@import '~/public/css/contract-list.css';
</style>
