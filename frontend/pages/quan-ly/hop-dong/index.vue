<template>
    <Titlebar title="Hợp đồng" />
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="dashboard-list-box margin-top-0">
                <ContractList
                    :items="contracts"
                    :is-loading="isLoading"
                    @reject-item="rejectItem"
                    @extend-contract="extendContract"
                    @return-contract="returnContract"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useContractActions } from '~/composables/useContractActions';

definePageMeta({
    layout: 'management'
});

const contracts = ref([]);
const isLoading = ref(false);

const { fetchContracts, rejectItem, extendContract, returnContract } = useContractActions({ isLoading, contracts });

onMounted(async () => {
    await fetchContracts();
});
</script>

<style scoped>
/* Không cần style vì đã được xử lý trong ContractList.vue */
</style>
