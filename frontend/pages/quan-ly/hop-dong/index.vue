<template>
    <Titlebar title="Hợp đồng" />

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="dashboard-list-box margin-top-0">
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
import { ref } from 'vue';
import { useContractActions } from '~/composables/useContractActions';

definePageMeta({
    layout: 'management'
});

const contracts = ref([]);
const isLoading = ref(false);

const { fetchContracts, cancelContract, extendContract, returnContract, earlyTermination } = useContractActions({ isLoading, contracts });

onMounted(async () => {
    await fetchContracts();
});
</script>

<style scoped></style>
