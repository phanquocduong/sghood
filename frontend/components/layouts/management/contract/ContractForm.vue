<template>
    <div>
        <!-- Hiển thị nội dung hợp đồng -->
        <div v-html="contractContent" class="contract-preview"></div>

        <!-- Form chỉnh sửa thông tin Bên B -->
        <form @submit.prevent="submitForm">
            <h5 class="mt-4">Thông tin Bên B (Người thuê)</h5>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Họ và tên:</label>
                    <input v-model="formData.name" type="text" class="form-control" />
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">CMND/CCCD:</label>
                    <input v-model="formData.identity_document" type="text" class="form-control" />
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Ngày sinh:</label>
                    <input v-model="formData.birthdate" type="text" class="form-control" />
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Địa chỉ thường trú:</label>
                    <input v-model="formData.address" type="text" class="form-control" />
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Ngày cấp:</label>
                    <input v-model="formData.date_of_issue" type="text" class="form-control" />
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nơi cấp:</label>
                    <input v-model="formData.address_of_issue" type="text" class="form-control" />
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Gửi lại Admin</button>
        </form>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useFetch } from 'nuxt/app';

// Dữ liệu form
const formData = ref({
    name: '',
    identity_document: '',
    birthdate: '',
    address: '',
    date_of_issue: '',
    address_of_issue: ''
});

// Nội dung hợp đồng gốc
const contractContent = ref('');

// Lấy dữ liệu hợp đồng từ API
const { data: contract } = await useFetch('/api/contract', {
    method: 'GET'
    // Giả sử API trả về đối tượng contract với field content
});

onMounted(() => {
    if (contract.value && contract.value.content) {
        contractContent.value = contract.value.content;
        // Parse HTML để lấy giá trị mặc định cho form (nếu có)
        parseContractContent();
    }
});

// Hàm parse nội dung HTML để lấy giá trị cho form
function parseContractContent() {
    const parser = new DOMParser();
    const doc = parser.parseFromString(contractContent.value, 'text/html');

    formData.value.name = doc.querySelector('input[name="name"]').value || '';
    formData.value.identity_document = doc.querySelector('input[name="identity_document"]').value || '';
    formData.value.birthdate = doc.querySelector('input[name="birthdate"]').value || '';
    formData.value.address = doc.querySelector('input[name="address"]').value || '';
    formData.value.date_of_issue = doc.querySelector('input[name="date_of_issue"]').value || '';
    formData.value.address_of_issue = doc.querySelector('input[name="address_of_issue"]').value || '';
}

// Hàm cập nhật nội dung HTML với dữ liệu từ form
function updateContractContent() {
    let updatedContent = contractContent.value;

    // Thay thế giá trị trong HTML
    updatedContent = updatedContent.replace(
        /<input type="text" class="form-control" value="[^"]*" name="name">/,
        `<input type="text" class="form-control" value="${formData.value.name}" name="name">`
    );
    updatedContent = updatedContent.replace(
        /<input type="text" class="form-control" value="[^"]*" name="identity_document">/,
        `<input type="text" class="form-control" value="${formData.value.identity_document}" name="identity_document">`
    );
    updatedContent = updatedContent.replace(
        /<input type="text" class="form-control" value="[^"]*" name="birthdate">/,
        `<input type="text" class="form-control" value="${formData.value.birthdate}" name="birthdate">`
    );
    updatedContent = updatedContent.replace(
        /<input type="text" class="form-control" value="[^"]*" name="address">/,
        `<input type="text" class="form-control" value="${formData.value.address}" name="address">`
    );
    updatedContent = updatedContent.replace(
        /<input type="text" class="form-control" value="[^"]*" name="date_of_issue">/,
        `<input type="text" class="form-control" value="${formData.value.date_of_issue}" name="date_of_issue">`
    );
    updatedContent = updatedContent.replace(
        /<input type="text" class="form-control" value="[^"]*" name="address_of_issue">/,
        `<input type="text" class="form-control" value="${formData.value.address_of_issue}" name="address_of_issue">`
    );

    return updatedContent;
}

// Hàm gửi dữ liệu về backend
async function submitForm() {
    const updatedContent = updateContractContent();

    try {
        const response = await $fetch('/api/contract', {
            method: 'PUT',
            body: {
                content: updatedContent
                // Các field khác nếu cần, ví dụ: contract_id
            }
        });
        alert('Hợp đồng đã được gửi lại cho admin!');
    } catch (error) {
        console.error('Lỗi khi gửi hợp đồng:', error);
        alert('Có lỗi xảy ra, vui lòng thử lại.');
    }
}
</script>

<style scoped>
.contract-preview {
    max-height: 500px;
    overflow-y: auto;
    border: 1px solid #ddd;
    padding: 15px;
    margin-bottom: 20px;
}
</style>
