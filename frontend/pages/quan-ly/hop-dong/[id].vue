<template>
    <Loading :is-loading="loading" />
    <div v-if="!loading">
        <Titlebar :title="`Hợp đồng #${contract.id} (${contract.status})`" />

        <div class="row">
            <!-- Cột hợp đồng -->
            <div :class="getColumnClass(contract.status)" class="contract-column">
                <div v-if="extractLoading" class="extract-loading-overlay">
                    <p>Đang quét ảnh căn cước...</p>
                </div>
                <div v-else ref="contractContainer" v-html="contract.content"></div>
            </div>

            <!-- Cột giấy tờ tùy thân (chỉ hiển thị khi cần) -->
            <div v-if="['Chờ xác nhận', 'Chờ chỉnh sửa'].includes(contract.status)" class="col-lg-3 col-md-3">
                <div class="dashboard-list-box margin-top-0">
                    <h4 class="gray">Giấy tờ tùy thân</h4>
                    <div class="dashboard-list-box-static">
                        <div class="edit-profile-photo">
                            <form id="dropzone-upload" class="dropzone" :class="{ 'dropzone-disabled': identityDocument.has_valid }"></form>
                            <p v-if="identityDocument.has_valid" class="valid-message">Ảnh căn cước đã hợp lệ, không thể tải lên thêm.</p>
                        </div>
                    </div>
                </div>

                <button @click="saveContract" class="button margin-top-15" :disabled="saveLoading || !isFormComplete">
                    <span v-if="saveLoading" class="button-spinner"></span>
                    {{ saveLoading ? 'Đang lưu...' : 'Lưu hợp đồng' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useHead } from '@unhead/vue';
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useToast } from 'vue-toastification';
import { useRoute, useRouter } from 'vue-router';

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
        }
    ]
});

const route = useRoute();
const router = useRouter();
const { $api, $dropzone } = useNuxtApp();
const toast = useToast();

// State
const loading = ref(false);
const extractLoading = ref(false);
const saveLoading = ref(false);
const contract = ref({ id: '', status: '', content: '' });
const contractContainer = ref(null);
const dropzoneInstance = ref(null);
const identityImages = ref([]);
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

const getColumnClass = status => {
    return ['Chờ xác nhận', 'Chờ chỉnh sửa'].includes(status) ? 'col-lg-9 col-md-9' : 'col-lg-12 col-md-12';
};

// Methods
const fetchContract = async () => {
    loading.value = true;
    try {
        const response = await $api(`/contracts/${route.params.id}`, { method: 'GET' });
        contract.value = response.data;
        await nextTick();
        syncIdentityData();
    } catch (error) {
        console.error('Lỗi khi lấy hợp đồng:', error);
        toast.error('Lỗi khi tải hợp đồng, vui lòng thử lại.');
        router.push('/quan-ly/hop-dong');
    } finally {
        loading.value = false;
    }
};

const syncIdentityData = () => {
    if (!contractContainer.value) return;

    const inputs = contractContainer.value.querySelectorAll('input[type="text"]');
    inputs.forEach(input => {
        const { name } = input;
        if (name in identityDocument.value) {
            // Đồng bộ từ identityDocument sang input
            input.value = identityDocument.value[name] || '';

            // Xóa listener cũ để tránh chồng chéo
            input.removeEventListener('input', input.handlers?.[name]);

            // Thêm listener mới để đồng bộ từ input sang identityDocument
            const handler = () => {
                identityDocument.value[name] = input.value || '';
            };
            input.handlers = { ...input.handlers, [name]: handler };
            input.addEventListener('input', handler);
        }
    });
};

const handleBackendError = error => {
    const data = error.response?._data;
    if (data?.error) {
        toast.error(data.error);
        return;
    }
    if (data?.errors) {
        Object.values(data.errors).forEach(err => toast.error(err[0]));
        return;
    }
    toast.error('Đã có lỗi xảy ra. Vui lòng thử lại.');
};

const handleIdentityUpload = async files => {
    if (!files?.length) return;

    const formData = new FormData();
    files.forEach(file => formData.append('identity_images[]', file));

    extractLoading.value = true;
    try {
        const response = await $api('/extract-identity-images', {
            method: 'POST',
            body: formData,
            headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value }
        });
        identityDocument.value = response.data;
        identityImages.value = files;
        toast.success(response.message);

        if (dropzoneInstance.value && identityDocument.value.has_valid) {
            dropzoneInstance.value.disable();
        }
    } catch (error) {
        handleBackendError(error);
        dropzoneInstance.value?.removeAllFiles(true);
        identityImages.value = [];
    } finally {
        extractLoading.value = false;
    }
};

const updateContractHtml = () => {
    let html = contract.value.content;
    const inputs = contractContainer.value.querySelectorAll('input[type="text"]');
    const inputWidths = {
        full_name: '250px',
        year_of_birth: '100px',
        identity_number: '150px',
        date_of_issue: '150px',
        place_of_issue: '500px',
        permanent_address: '500px'
    };

    inputs.forEach(input => {
        const { name, value = '' } = input;
        if (name in identityDocument.value) {
            const width = inputWidths[name] || '200px';
            const regex = new RegExp(`<input[^>]*name="${name}"[^>]*>`, 'g');
            html = html.replace(
                regex,
                `<input type="text" class="form-control flat-line d-inline-block" style="width: ${width};" name="${name}" value="${value}" readonly>`
            );
        }
    });
    return html;
};

const saveContract = async () => {
    saveLoading.value = true;
    try {
        syncIdentityData();
        const updatedHtml = updateContractHtml();
        const formData = new FormData();
        formData.append('contract_content', updatedHtml);
        identityImages.value.forEach(file => formData.append('identity_images[]', file));

        const response = await $api(`/contracts/${route.params.id}/save`, {
            method: 'POST',
            body: formData,
            headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value }
        });
        toast.success(response.message);
        router.push('/quan-ly/hop-dong');
    } catch (error) {
        console.error('Lỗi khi lưu hợp đồng:', error);
        toast.error('Lỗi khi lưu thông tin, vui lòng thử lại.');
    } finally {
        saveLoading.value = false;
    }
};

// Watch
watch(
    identityDocument,
    () => {
        nextTick(syncIdentityData);
    },
    { deep: true }
);

// Lifecycle
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
.contract-column {
    position: relative;
}

.extract-loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #f7f7f7; /* Nền trắng mờ */
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 1000; /* Đủ cao để che nội dung hợp đồng nhưng không che sidebar */
    transition: opacity 0.3s ease;
}

.extract-loading-overlay p {
    font-size: 16px;
    color: #333;
}

.button-spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #fff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
    margin-right: 8px;
    vertical-align: middle;
}

.dropzone {
    border: 2px dashed #ccc;
    transition: border-color 0.2s;
}

.dropzone:hover {
    border-color: #59b02c;
}

.dropzone-disabled {
    opacity: 0.6;
    cursor: not-allowed;
    pointer-events: none;
    border-color: #ccc !important;
}

.valid-message {
    color: #59b02c;
    font-size: 14px;
    margin-top: 10px;
    text-align: center;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.edit-profile-photo {
    margin-bottom: 0;
}
</style>
