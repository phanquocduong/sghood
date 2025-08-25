<template>
    <!-- Tiêu đề trang -->
    <Titlebar title="Hợp đồng" />

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="dashboard-list-box margin-top-0">
                <!-- Danh sách hợp đồng -->
                <ContractList
                    :items="contracts"
                    :is-loading="isLoading"
                    @cancel-contract="cancelContract"
                    @extend-contract="extendContract"
                    @return-contract="returnContract"
                    @early-termination="earlyTermination"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useContractActions } from '~/composables/useContractActions';

// Định nghĩa layout cho trang
definePageMeta({
    layout: 'management' // Sử dụng layout 'management'
});

// Khởi tạo các biến reactive
const contracts = ref([]); // Danh sách hợp đồng
const isLoading = ref(false); // Trạng thái loading khi tải dữ liệu

// Sử dụng composable để xử lý các hành động liên quan đến hợp đồng
const { fetchContracts, cancelContract, extendContract, returnContract } = useContractActions({ isLoading, contracts });

// Lấy danh sách hợp đồng khi component được mount
onMounted(async () => {
    await fetchContracts(); // Gọi hàm lấy danh sách hợp đồng
});
</script>
