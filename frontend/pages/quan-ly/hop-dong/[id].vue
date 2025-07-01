<template>
    <Loading :is-loading="loading" />
    <div v-if="!loading" class="contract-page">
        <Titlebar :title="`Hợp đồng #${contract?.id} (${contract?.status})`" />

        <div class="row">
            <div :class="contract?.status === 'Chờ xác nhận' ? 'col-lg-9 col-md-9' : 'col-lg-12 col-md-12'" class="contract-column">
                <ContractPayment
                    v-if="contract?.status === 'Chờ thanh toán tiền cọc'"
                    :contract="contract"
                    :invoice="invoice"
                    :qr-code-url="qrCodeUrl"
                />
                <div v-else-if="extractLoading" class="extract-loading-overlay">
                    <p>Đang quét ảnh căn cước...</p>
                </div>
                <div v-else ref="contractContainer" v-html="contract?.content"></div>

                <ContractSignature
                    v-if="contract?.status === 'Chờ ký'"
                    @signature-saved="signature => (signatureData = signature)"
                    @signature-cleared="() => (signatureData = null)"
                    @sign-contract="signContract"
                    :save-loading="saveLoading"
                    :signature-data="signatureData"
                />

                <ContractSaveButton v-if="contract?.status === 'Chờ chỉnh sửa'" @save-contract="saveContract" :save-loading="saveLoading" />
            </div>

            <IdentityUpload
                v-if="contract?.status === 'Chờ xác nhận'"
                :identity-document="identityDocument"
                :is-form-complete="isFormComplete"
                :save-loading="saveLoading"
                @save-contract="saveContract"
                @identity-upload="handleIdentityUpload"
            />
        </div>
    </div>
</template>

<script setup>
import { useHead } from '@unhead/vue';
import { shallowRef, ref, computed, onMounted, onUnmounted } from 'vue';
import { useToast } from 'vue-toastification';
import { useRoute, useRouter } from 'vue-router';
import { useContract } from '~/composables/useContract';

definePageMeta({
    layout: 'management'
});

useHead({
    link: [
        {
            rel: 'stylesheet',
            href: 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
            integrity: 'sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM',
            crossorigin: 'anonymous'
        },
        {
            rel: 'stylesheet',
            href: 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css',
            integrity: 'sha512-Fo3rlrZj/k7ujTnHg4CGR2D7kSs0v4LLanw2qksYuRlEzO+tcaEPQogQ0KaoGN26/zrn20ImR1DfuLWnOo7aBA==',
            crossorigin: 'anonymous'
        }
    ]
});

const route = useRoute();
const router = useRouter();
const toast = useToast();
const { $dropzone } = useNuxtApp();

// State
const loading = ref(false);
const extractLoading = ref(false);
const saveLoading = ref(false);
const contract = shallowRef(null);
const contractContainer = ref(null);
const dropzoneInstance = ref(null);
const identityImages = ref([]);
const signatureData = ref(null);
const invoice = ref(null);
const qrCodeUrl = ref('');
const paymentInterval = ref(null);
const identityDocument = ref({
    full_name: '',
    year_of_birth: '',
    identity_number: '',
    date_of_issue: '',
    place_of_issue: '',
    permanent_address: '',
    has_valid: false
});

// Computed
const isFormComplete = computed(() =>
    Object.values(identityDocument.value)
        .slice(0, -1)
        .every(value => value)
);

// Composable
const { fetchContract, signContract, saveContract, handleIdentityUpload } = useContract({
    contract,
    invoice,
    qrCodeUrl,
    signatureData,
    identityDocument,
    identityImages,
    contractContainer,
    loading,
    extractLoading,
    saveLoading,
    toast,
    router,
    route,
    dropzoneInstance,
    paymentInterval
});

onMounted(async () => {
    await fetchContract();
    dropzoneInstance.value = new $dropzone('#dropzone-upload', {
        url: '/',
        autoProcessQueue: true,
        maxFilesize: 5,
        acceptedFiles: 'image/*',
        clickable: !identityDocument.value.has_valid,
        dictDefaultMessage: '<i class="sl sl-icon-plus"></i>Tải lên 2 ảnh căn cước công dân mặt trước và mặt sau',
        init() {
            this.on('queuecomplete', () => {
                const files = [...this.getQueuedFiles(), ...this.getAcceptedFiles()];
                if (files.length) handleIdentityUpload(files);
            });
            this.on('error', (file, message) => console.error('Error uploading file:', message));
            if (identityDocument.value.has_valid) this.disable();
        }
    });
});

onUnmounted(() => {
    if (paymentInterval.value) clearInterval(paymentInterval.value);
});
</script>

<style scoped>
.contract-page {
    padding: 20px;
}

.contract-column {
    position: relative;
}

.extract-loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #f7f7f7;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    transition: opacity 0.3s ease;
}

.extract-loading-overlay p {
    font-size: 16px;
    color: #333;
}
</style>
