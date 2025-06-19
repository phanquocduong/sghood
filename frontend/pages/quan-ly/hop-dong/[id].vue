<template>
    <Titlebar :title="`Hợp đồng #${contract.id} (${contract.status})`" />

    <div class="row">
        <div :class="getClassByStatus(contract.status)" class="contract-column">
            <div v-if="loading" class="loading-overlay">
                <div class="spinner"></div>
                <p>Đang quét ảnh căn cước...</p>
            </div>
            <div v-else ref="contractContainer" v-html="contract.content" />
        </div>
        <div v-if="contract.status == 'Chờ xác nhận' || contract.status == 'Chờ chỉnh sửa'" class="col-lg-3 col-md-3">
            <div v-if="!identityDocument.has_valid" class="dashboard-list-box margin-top-0">
                <h4 class="gray">Giấy tờ tuỳ thân</h4>
                <div class="dashboard-list-box-static">
                    <div class="edit-profile-photo">
                        <form id="dropzone-upload" class="dropzone"></form>
                    </div>
                </div>
            </div>

            <button @click="saveContract" class="button margin-top-15" :disabled="buttonLoading || !isComplete">
                <span v-if="buttonLoading" class="button-spinner"></span> {{ buttonLoading ? 'Đang lưu...' : 'Lưu hợp đồng' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { useHead } from '@unhead/vue';
import { ref, onMounted, computed, nextTick, watch } from 'vue';
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
const { $api } = useNuxtApp();
const toast = useToast();
const loading = ref(false);
const buttonLoading = ref(false);
const router = useRouter();

// Lưu trữ ảnh CCCD tạm thời để gửi khi lưu hợp đồng
const identityImages = ref([]);

// Dữ liệu CCCD để hiển thị trên hợp đồng
const identityDocument = ref({
    full_name: '',
    year_of_birth: '',
    identity_number: '',
    date_of_issue: '',
    place_of_issue: '',
    permanent_address: '',
    has_valid: false
});

const contract = ref('');
const contractContainer = ref(null);

const isComplete = computed(() => {
    return (
        identityDocument.value.full_name &&
        identityDocument.value.year_of_birth &&
        identityDocument.value.identity_number &&
        identityDocument.value.date_of_issue &&
        identityDocument.value.place_of_issue &&
        identityDocument.value.permanent_address
    );
});

const getClassByStatus = status => {
    switch (status) {
        case 'Chờ xác nhận':
        case 'Chờ chỉnh sửa':
            return 'col-lg-9 col-md-9';
        case 'Chờ duyệt':
        case 'Chờ ký':
        case 'Hoạt động':
        case 'Kết thúc':
        case 'Huỷ bỏ':
            return 'col-lg-12 col-md-12';
        default:
            return '';
    }
};

const fetchContract = async () => {
    try {
        loading.value = true;
        const response = await $api(`/contracts/${route.params.id}`, { method: 'GET' });
        contract.value = response.data;
        await nextTick();
        syncContractDataFromInputs();
        syncInputsWithContractData();
    } catch (error) {
        console.error('Lỗi khi lấy hợp đồng:', error);
        toast.error('Lỗi khi tải hợp đồng, vui lòng thử lại.');
        router.push('/quan-ly/hop-dong');
    } finally {
        loading.value = false;
    }
};

const syncContractDataFromInputs = () => {
    if (!contractContainer.value) return;

    const inputs = contractContainer.value.querySelectorAll('input[type="text"]');
    inputs.forEach(input => {
        const name = input.name;
        if (identityDocument.value.hasOwnProperty(name)) {
            identityDocument.value[name] = input.value || '';
        }
    });
};

const syncInputsWithContractData = () => {
    if (!contractContainer.value) return;

    const inputs = contractContainer.value.querySelectorAll('input[type="text"]');
    inputs.forEach(input => {
        const name = input.name;
        if (identityDocument.value.hasOwnProperty(name)) {
            input.value = identityDocument.value[name] || '';
        }
    });
};

watch(
    identityDocument,
    newData => {
        nextTick(() => {
            syncInputsWithContractData();
        });
    },
    { deep: true }
);

const handleCccdUpload = async files => {
    if (!files || files.length === 0) return;

    const formData = new FormData();
    files.forEach(file => formData.append('identity_images[]', file));

    try {
        loading.value = true;
        const response = await $api('/extract-identity-images', {
            method: 'POST',
            body: formData,
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            }
        });
        identityDocument.value = response.data;
        identityImages.value = files;
        toast.success(response.message);
    } catch (error) {
        console.error('Lỗi khi quét CCCD:', error);
        toast.error(error.response?._data.message || 'Lỗi khi quét ảnh CCCD, vui lòng thử lại.');
    } finally {
        loading.value = false;
    }
};

const updateContractHtmlWithValues = () => {
    let updatedHtml = contract.value.content;
    const inputs = contractContainer.value.querySelectorAll('input[type="text"]');
    inputs.forEach(input => {
        const name = input.name;
        const value = input.value || '';
        if (identityDocument.value.hasOwnProperty(name)) {
            let inputWidth;
            switch (name) {
                case 'full_name':
                    inputWidth = '250px';
                    break;
                case 'year_of_birth':
                    inputWidth = '100px';
                    break;
                case 'identity_number':
                    inputWidth = '150px';
                    break;
                case 'date_of_issue':
                    inputWidth = '150px';
                    break;
                case 'place_of_issue':
                    inputWidth = '500px';
                    break;
                case 'permanent_address':
                    inputWidth = '500px';
                    break;
                default:
                    inputWidth = '200px';
            }
            const regex = new RegExp(`<input[^>]*name="${name}"[^>]*>`, 'g');
            updatedHtml = updatedHtml.replace(
                regex,
                `<input type="text" class="form-control flat-line d-inline-block" style="width: ${inputWidth};" name="${name}" value="${value}" readonly>`
            );
        }
    });
    return updatedHtml;
};

const saveContract = async () => {
    try {
        buttonLoading.value = true;
        syncContractDataFromInputs();
        const updatedContractHtml = updateContractHtmlWithValues();

        const formData = new FormData();
        formData.append('contract_content', updatedContractHtml);
        identityImages.value.forEach(file => formData.append('identity_images[]', file));

        const response = await $api(`/contracts/${route.params.id}/save`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            }
        });
        toast.success(response.message);
        router.push('/quan-ly/hop-dong');
    } catch (error) {
        console.error('Lỗi khi lưu hợp đồng:', error);
        toast.error('Lỗi khi lưu thông tin, vui lòng thử lại.');
    } finally {
        buttonLoading.value = false;
    }
};

onMounted(async () => {
    await fetchContract();
    const { $dropzone } = useNuxtApp();
    new $dropzone('#dropzone-upload', {
        url: '/extract-identity-images',
        autoProcessQueue: true,
        maxFilesize: 5,
        acceptedFiles: 'image/*',
        dictDefaultMessage: '<i class="sl sl-icon-plus"></i>Tải lên 2 ảnh căn cước công dân mặt trước và mặt sau',
        init: function () {
            this.on('success', (file, response) => {
                console.log('File uploaded successfully:', response);
                handleCccdUpload([file]);
            });
            this.on('queuecomplete', () => {
                const files = this.getQueuedFiles().concat(this.getAcceptedFiles());
                if (files.length > 0) {
                    handleCccdUpload(files);
                }
            });
            this.on('error', (file, message) => {
                console.error('Error uploading file:', message);
            });
        }
    });
});
</script>

<style scoped>
.contract-column {
    position: relative; /* Để .loading-overlay định vị tương đối */
}

.loading-overlay {
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

.loading-overlay .spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #ddd;
    border-top: 5px solid #f91942;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 10px;
}

.loading-overlay p {
    font-size: 16px;
    color: #333;
}

.button-spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
    margin-right: 8px;
    vertical-align: middle;
}

.dropzone {
    border: 2px dashed #ccc;
}

.dropzone:hover {
    border: 2px dashed #59b02c;
}

@keyframes spin {
    0% {
        transform: rotate(0);
    }
    100% {
        transform: rotate(360deg);
    }
}

.button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
