<template>
    <Loading :is-loading="loading" />

    <div class="contract-page">
        <Titlebar :title="`Hợp đồng #${contract?.id} (${contract?.status})`" />

        <div class="row">
            <div :class="contract?.status === 'Chờ xác nhận' ? 'col-lg-9 col-md-12' : 'col-lg-12 col-md-12'" class="contract-column">
                <div v-if="extractLoading" class="extract-loading-overlay">
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

        <OTPModal :phone-number="phoneNumber" :loading="saveLoading" v-model:otp-code="otpCode" @confirm="confirmOTPAndSign" />
    </div>

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="dashboard-list-box margin-top-15">
                <ExtensionList
                    v-if="contract?.active_extensions.length !== 0"
                    :contract="contract"
                    @reject-extension="rejectExtension"
                    @open-popup="openPopup"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { useHead } from '@unhead/vue';
import { shallowRef, ref, computed, onMounted } from 'vue';
import { useAppToast } from '~/composables/useToast';
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
const toast = useAppToast();
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
const { fetchContract, signContract, confirmOTPAndSign, saveContract, handleIdentityUpload, phoneNumber, otpCode } = useContract({
    contract,
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
    dropzoneInstance
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

/* Responsive styles cho các thành phần trong contract-page */
@media (max-width: 992px) {
    .contract-page {
        padding: 15px;
    }

    .contract-column {
        padding: 0 10px;
    }

    .signature-section {
        margin-top: 15px;
    }
}

@media (max-width: 768px) {
    .contract-page {
        padding: 10px;
    }

    .extract-loading-overlay p {
        font-size: 14px;
    }
}

@media (max-width: 576px) {
    .contract-page {
        padding: 5px;
    }

    .extract-loading-overlay p {
        font-size: 12px;
    }
}
</style>

<style>
/* Responsive styles cho contract-document */
@media (max-width: 992px) {
    .contract-document {
        padding: 10mm 15mm !important;
    }

    .contract-document * {
        font-size: 14px !important;
        line-height: 28px !important;
    }

    .contract-document h3 {
        font-size: 16px !important;
    }

    .contract-document .form-control.flat-line {
        width: 100% !important;
        margin-bottom: 10px !important;
    }

    .contract-document .row.mb-3 .col-10,
    .contract-document .row.mb-3 .col-2 {
        flex: 0 0 100% !important;
        max-width: 100% !important;
    }

    .contract-document .row.mb-3 .col-2 {
        text-align: center !important;
        margin-top: 10px !important;
    }

    .contract-document .row.mt-5.pt-4 .col-6 {
        flex: 0 0 50% !important;
        max-width: 100% !important;
        margin-bottom: 20px !important;
    }

    .contract-document .row.mt-5.pt-4 .col-6 img {
        width: 150px !important;
        height: 75px !important;
    }

    .col-2 {
        display: none !important;
    }

    #breadcrumbs {
        display: none !important;
    }
}

@media (max-width: 768px) {
    .contract-document {
        padding: 8mm 10mm !important;
        font-size: 12px !important;
        line-height: 1.4 !important;
    }

    .contract-document h3 {
        font-size: 14px !important;
    }

    .contract-document .mb-4,
    .contract-document .mb-3,
    .contract-document .mb-2 {
        margin-bottom: 10px !important;
    }

    .contract-document .form-control.flat-line {
        height: 22px !important;
        font-size: 12px !important;
    }
}

@media (max-width: 576px) {
    .contract-document {
        padding: 6mm 8mm !important;
        font-size: 11px !important;
        line-height: 1.3 !important;
    }

    .contract-document h3 {
        font-size: 13px !important;
    }

    .contract-document .text-center.mb-4 > div {
        font-size: 12px !important;
    }

    .contract-document .form-control.flat-line {
        font-size: 11px !important;
        margin-bottom: 8px !important;
    }

    .contract-document .row.mt-5.pt-4 .col-6 img {
        width: 120px !important;
        height: 60px !important;
    }
}

@media (max-width: 480px) {
    .contract-document {
        padding: 6mm 8mm !important;
    }

    .contract-document * {
        font-size: 12px !important;
        line-height: 22px !important;
    }

    .contract-document h3 {
        font-size: 14px !important;
    }

    .contract-document .form-control.flat-line {
        font-size: 12px !important;
        height: 24px !important;
    }

    .contract-document .row.mt-5.pt-4 .col-6 img {
        width: 100px !important;
        height: 50px !important;
    }

    span.ms-3 {
        margin-left: 0 !important;
    }
}
</style>
