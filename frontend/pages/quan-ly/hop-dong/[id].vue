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

                <!-- Form chữ ký -->
                <div v-if="contract.status === 'Chờ ký'" class="signature-section mt-4">
                    <SignaturePad @signature-saved="handleSignatureSaved" @signature-cleared="handleSignatureCleared" />
                </div>

                <div v-if="contract.status === 'Chờ chỉnh sửa'" class="d-flex justify-content-center">
                    <button @click="saveContract" class="button margin-top-15" :disabled="saveLoading">
                        <span v-if="saveLoading" class="button-spinner"></span>
                        {{ saveLoading ? 'Đang lưu...' : 'Lưu hợp đồng' }}
                    </button>
                </div>

                <div v-if="contract.status === 'Chờ ký'" class="d-flex justify-content-center">
                    <button
                        v-if="contract.status === 'Chờ ký'"
                        @click="signContract"
                        class="button margin-top-15"
                        :disabled="saveLoading || !signatureData"
                    >
                        <span v-if="saveLoading" class="button-spinner"></span>
                        {{ saveLoading ? 'Đang ký...' : 'Ký hợp đồng' }}
                    </button>
                </div>
            </div>

            <!-- Cột giấy tờ tùy thân và nút hành động -->
            <div v-if="['Chờ xác nhận', 'Chờ chỉnh sửa', 'Chờ ký'].includes(contract.status)" class="col-lg-3 col-md-3">
                <!-- Form giấy tờ tùy thân -->
                <div v-if="contract.status === 'Chờ xác nhận'" class="dashboard-list-box margin-top-0">
                    <h4 class="gray">Giấy tờ tùy thân</h4>
                    <div class="dashboard-list-box-static">
                        <p v-if="identityDocument.has_valid" class="valid-message">Ảnh căn cước đã hợp lệ, không thể tải lên thêm.</p>
                        <div class="edit-profile-photo">
                            <form
                                id="dropzone-upload"
                                class="dropzone"
                                :class="{ 'dropzone-disabled': identityDocument.has_valid ?? false }"
                            ></form>
                        </div>
                    </div>
                </div>
                <!-- Nút hành động -->

                <button
                    v-if="contract.status === 'Chờ xác nhận'"
                    @click="saveContract"
                    class="button margin-top-15"
                    :disabled="saveLoading || !isFormComplete"
                >
                    <span v-if="saveLoading" class="button-spinner"></span>
                    {{ saveLoading ? 'Đang lưu...' : 'Lưu hợp đồng' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useHead } from '@unhead/vue';
import { ref, computed, onMounted, nextTick } from 'vue';
import { useToast } from 'vue-toastification';
import { useRoute, useRouter } from 'vue-router';
import SignaturePad from '~/components/SignaturePad.vue';

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
const signatureData = ref(null);
const identityDocument = ref({
    full_name: '',
    year_of_birth: '',
    identity_number: '',
    date_of_issue: '',
    place_of_issue: '',
    permanent_address: ''
});

// Computed
const isFormComplete = computed(() =>
    Object.values(identityDocument.value)
        .slice(0, -1)
        .every(value => value)
);

const getColumnClass = status => {
    return ['Chờ xác nhận'].includes(status) ? 'col-lg-9 col-md-9' : 'col-lg-12 col-md-12';
};

// Methods
const fetchContract = async () => {
    loading.value = true;
    try {
        const response = await $api(`/contracts/${route.params.id}`, { method: 'GET' });
        contract.value = response.data;
        await nextTick();

        processContractContent();
    } catch (error) {
        console.error('Lỗi khi lấy hợp đồng:', error);
        toast.error('Lỗi khi tải hợp đồng, vui lòng thử lại.');
        router.push('/quan-ly/hop-dong');
    } finally {
        loading.value = false;
        await nextTick();
        syncIdentityData();
        console.log(identityDocument.value);
    }
};

const processContractContent = () => {
    if (!contract.value.content) return;

    let processedContent = contract.value.content;

    if (contract.value.status === 'Chờ chỉnh sửa') {
        processedContent = processedContent.replace(/\s*readonly\s*(?=\s|>|\/)/gi, '');
        processedContent = processedContent.replace(/<input([^>]*type="text"[^>]*)>/gi, (match, attributes) => {
            const cleanAttributes = attributes.replace(/\s*readonly\s*/gi, '');
            return `<input${cleanAttributes}>`;
        });
    }

    contract.value.content = processedContent;
};

const syncIdentityData = () => {
    if (!contractContainer.value) return;

    const inputs = contractContainer.value.querySelectorAll('input[type="text"]');
    inputs.forEach(input => {
        const { name } = input;
        if (name in identityDocument.value) {
            if (identityDocument.value[name]) {
                input.value = identityDocument.value[name];
            } else {
                identityDocument.value[name] = input.value;
            }
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
        identityDocument.value = {
            full_name: '',
            year_of_birth: '',
            identity_number: '',
            date_of_issue: '',
            place_of_issue: '',
            permanent_address: ''
        };
        identityImages.value = [];
        dropzoneInstance.value?.removeAllFiles(true);
    } finally {
        extractLoading.value = false;
        await nextTick();
        syncIdentityData();
    }
};

const handleSignatureSaved = signature => {
    signatureData.value = signature;
};

const handleSignatureCleared = () => {
    signatureData.value = null;
};

const updateContractWithSignature = async () => {
    if (!signatureData.value) return contract.value.content;

    // Thu nhỏ ảnh chữ ký với chất lượng cao
    const processedSignature = await processSignature(signatureData.value);
    if (!processedSignature) return contract.value.content;

    const parser = new DOMParser();
    const doc = parser.parseFromString(contract.value.content, 'text/html');
    const sideBSections = doc.getElementsByClassName('col-6 text-center');
    if (sideBSections.length >= 2) {
        const sideB = sideBSections[1]; // Lấy cột thứ hai (BÊN B)
        const signatureImg = doc.createElement('img');
        signatureImg.src = processedSignature;
        signatureImg.className = 'signature-image';
        signatureImg.alt = 'Chữ ký Bên B';

        // Tạo thẻ <p> chứa <strong> cho họ tên
        const nameParagraph = doc.createElement('p');
        const nameStrong = doc.createElement('strong');
        nameStrong.textContent = identityDocument.value.full_name;
        nameParagraph.appendChild(nameStrong);
        nameParagraph.className = 'signature-name';

        // Tìm placeholder
        const signaturePlaceholder = sideB.querySelector('p.mb-5');
        if (signaturePlaceholder) {
            // Chèn chữ ký và họ tên ngay sau placeholder
            signaturePlaceholder.after(nameParagraph);
            signaturePlaceholder.after(signatureImg);
        } else {
            // Nếu không tìm thấy placeholder, thêm vào cuối sideB
            sideB.appendChild(signatureImg);
            sideB.appendChild(nameParagraph);
        }
        return doc.documentElement.innerHTML;
    }
    return contract.value.content;
};

// Hàm thu nhỏ ảnh chữ ký với chất lượng cao
const processSignature = signature => {
    return new Promise(resolve => {
        const img = new Image();
        img.crossOrigin = 'Anonymous'; // Để tránh lỗi CORS nếu có
        img.onload = () => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');

            // Sử dụng super-sampling để cải thiện chất lượng
            const scaleFactor = 2; // Tăng gấp đôi kích thước tạm thời
            const targetWidth = 200 * scaleFactor;
            const targetHeight = 100 * scaleFactor;
            canvas.width = targetWidth;
            canvas.height = targetHeight;

            // Vẽ ảnh với kích thước lớn hơn trước, sau đó thu nhỏ
            ctx.drawImage(img, 0, 0, targetWidth, targetHeight);
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';

            // Tạo canvas mới để thu nhỏ xuống kích thước cuối
            const outputCanvas = document.createElement('canvas');
            const outputCtx = outputCanvas.getContext('2d');
            outputCanvas.width = 200;
            outputCanvas.height = 100;
            outputCtx.drawImage(canvas, 0, 0, targetWidth, targetHeight, 0, 0, 200, 100);

            // Chuyển thành base64 với chất lượng cao
            resolve(outputCanvas.toDataURL('image/png', 0.9));
        };
        img.onerror = () => resolve(null);
        img.src = signature;
    });
};

const signContract = async () => {
    if (!signatureData.value) {
        toast.error('Vui lòng ký hợp đồng trước khi gửi.');
        return;
    }

    saveLoading.value = true;
    try {
        const updatedContent = await updateContractWithSignature(); // Chờ xử lý ảnh
        const response = await $api(`/contracts/${route.params.id}/sign`, {
            method: 'POST',
            body: {
                signature: signatureData.value, // Gửi bản gốc để lưu
                content: updatedContent
            },
            headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value }
        });
        toast.success(response.message);
        router.push('/quan-ly/hop-dong');
    } catch (error) {
        console.error('Lỗi khi ký hợp đồng:', error);
        handleBackendError(error);
    } finally {
        saveLoading.value = false;
    }
};

const updateContractHtml = () => {
    let html = contract.value.content;
    const inputs = contractContainer.value.querySelectorAll('input[type="text"]');
    const inputWidths = {
        full_name: '200px',
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
        if (contract.value.status === 'Chờ xác nhận') {
            syncIdentityData();
        }
        const updatedHtml = updateContractHtml();
        const formData = new FormData();
        formData.append('contract_content', updatedHtml);

        if (contract.value.status === 'Chờ xác nhận') {
            identityImages.value.forEach(file => formData.append('identity_images[]', file));
        }

        formData.append('_method', 'PATCH');

        const response = await $api(`/contracts/${route.params.id}`, {
            method: 'POST',
            body: formData,
            headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value }
        });
        toast.success(response.message);
        router.push('/quan-ly/hop-dong');
    } catch (error) {
        console.error('Lỗi khi lưu hợp đồng:', error);
        handleBackendError(error);
    } finally {
        saveLoading.value = false;
    }
};

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

.signature-section {
    margin-top: 20px;
}

.signature-image {
    max-width: 80px;
    max-height: 40px;
    margin: 10px auto;
    border: 1px solid #000;
    border-radius: 4px;
}

.signature-name {
    display: block;
    text-align: center;
    font-size: 14px;
    margin-top: 5px;
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
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    transition: border-color 0.2s ease;
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
    font-size: 1.4rem;
    line-height: 2.2rem;
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

input[type='text']:not([readonly]) {
    background-color: #fff;
    border-color: #ddd;
    border-radius: 5px;
}

input[type='text']:not([readonly]):focus {
    border-color: #59b02c;
    box-shadow: 0 0 0 0.2rem rgba(89, 176, 44, 0.25);
}
</style>
