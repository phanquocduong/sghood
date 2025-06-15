<template>
    <Titlebar title="Hợp đồng" />

    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div v-if="loading">Đang tải hợp đồng...</div>
            <div v-else-if="!contractContent">Không tìm thấy hợp đồng.</div>
            <div v-else v-html="contractContent" class="contract-preview"></div>
            <form @submit.prevent="submitForm">
                <h5 class="mt-4">Thông tin Bên B (Người thuê)</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Họ và tên:</label>
                        <input v-model="formData.name" type="text" class="form-control" required />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">CMND/CCCD:</label>
                        <input v-model="formData.identity_document" type="text" class="form-control" required />
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ngày sinh:</label>
                        <input v-model="formData.birthdate" type="text" class="form-control" placeholder="DD/MM/YYYY" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Địa chỉ thường trú:</label>
                        <input v-model="formData.address" type="text" class="form-control" />
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Ngày cấp:</label>
                        <input v-model="formData.date_of_issue" type="text" class="form-control" placeholder="DD/MM/YYYY" />
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nơi cấp:</label>
                        <input v-model="formData.address_of_issue" type="text" class="form-control" />
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Gửi lại Admin</button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useToast } from 'vue-toastification';
import { useAuthStore } from '~/stores/auth';
import { useNuxtApp, useRoute } from '#app';
import { storeToRefs } from 'pinia';

definePageMeta({
    layout: 'management'
});

const { $api } = useNuxtApp();
const toast = useToast();
const authStore = useAuthStore();
const { user } = storeToRefs(authStore);
const loading = ref(false);
const contractContent = ref('');

// Dữ liệu form
const formData = ref({
    name: '',
    identity_document: '',
    birthdate: '',
    address: '',
    date_of_issue: '',
    address_of_issue: ''
});

const fetchContract = async () => {
    try {
        loading.value = true;
        const userId = user.value?.id;
        if (!userId) {
            toast.error('Không tìm thấy id người dùng.');
            return;
        }
        const res = await $api(`users/${userId}/contract`);
        if (res && res[0]?.content) {
            contractContent.value = res[0].content;
            console.log('contractContent:', contractContent.value); // Debug nội dung HTML
            parseContractContent();
        } else {
            toast.error('Không tìm thấy hợp đồng.');
        }
    } catch (error) {
        console.error('Lỗi khi lấy hợp đồng:', error);
        toast.error('Lỗi khi tải hợp đồng, vui lòng thử lại.');
    } finally {
        loading.value = false;
    }
};

onMounted(async () => {
    await fetchContract();
});

// Hàm parse nội dung HTML để lấy giá trị cho form
function parseContractContent() {
    if (!contractContent.value) {
        console.error('contractContent is empty');
        toast.error('Không thể parse hợp đồng vì nội dung rỗng.');
        return;
    }

    try {
        const parser = new DOMParser();
        const doc = parser.parseFromString(contractContent.value, 'text/html');

        // Kiểm tra và lấy giá trị từ các input
        const nameInput = doc.querySelector('input[name="name"]');
        const identityInput = doc.querySelector('input[name="identity_document"]');
        const birthdateInput = doc.querySelector('input[name="birthdate"]');
        const addressInput = doc.querySelector('input[name="address"]');
        const dateOfIssueInput = doc.querySelector('input[name="date_of_issue"]');
        const addressOfIssueInput = doc.querySelector('input[name="address_of_issue"]');

        // Gán giá trị vào formData, nếu input không tồn tại thì để rỗng
        formData.value.name = nameInput ? nameInput.value : '';
        formData.value.identity_document = identityInput ? identityInput.value : '';
        formData.value.birthdate = birthdateInput ? birthdateInput.value : '';
        formData.value.address = addressInput ? addressInput.value : '';
        formData.value.date_of_issue = dateOfIssueInput ? dateOfIssueInput.value : '';
        formData.value.address_of_issue = addressOfIssueInput ? addressOfIssueInput.value : '';

        // Debug giá trị lấy được
        console.log('Parsed formData:', formData.value);
    } catch (error) {
        console.error('Lỗi khi parse HTML:', error);
        toast.error('Lỗi khi xử lý nội dung hợp đồng.');
    }
}

// Hàm cập nhật nội dung HTML với dữ liệu từ form
function updateContractContent() {
    if (!contractContent.value) {
        toast.error('Nội dung hợp đồng rỗng, không thể cập nhật.');
        return contractContent.value;
    }

    let updatedContent = contractContent.value;

    // Hàm để thay thế giá trị input an toàn
    const replaceInputValue = (content, name, value) => {
        const regex = new RegExp(`<input type="text" class="form-control" value="[^"]*" name="${name}">`);
        return content.replace(regex, `<input type="text" class="form-control" value="${value || ''}" name="${name}">`);
    };

    // Thay thế giá trị trong HTML
    updatedContent = replaceInputValue(updatedContent, 'name', formData.value.name);
    updatedContent = replaceInputValue(updatedContent, 'identity_document', formData.value.identity_document);
    updatedContent = replaceInputValue(updatedContent, 'birthdate', formData.value.birthdate);
    updatedContent = replaceInputValue(updatedContent, 'address', formData.value.address);
    updatedContent = replaceInputValue(updatedContent, 'date_of_issue', formData.value.date_of_issue);
    updatedContent = replaceInputValue(updatedContent, 'address_of_issue', formData.value.address_of_issue);

    return updatedContent;
}

// Hàm gửi dữ liệu về backend
async function submitForm() {
    const updatedContent = updateContractContent();
    if (!updatedContent) return;

    try {
        const response = await $api(`/users/${user.value.id}/contract`, {
            method: 'PUT',
            body: {
                content: updatedContent
                // Thêm contract_id nếu cần
            }
        });
        toast.success('Hợp đồng đã được gửi lại cho admin!');
    } catch (error) {
        console.error('Lỗi khi gửi hợp đồng:', error);
        toast.error('Có lỗi xảy ra, vui lòng thử lại.');
    }
}
</script>
