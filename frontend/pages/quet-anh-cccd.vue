<template>
    <div class="p-4 max-w-md mx-auto">
        <h1 class="text-2xl font-bold mb-4">Quét Căn Cước Công Dân</h1>
        <form @submit.prevent="uploadImage" enctype="multipart/form-data">
            <input type="file" accept="image/*" @change="onFileChange" />
            <button type="submit" class="mt-4" :disabled="!file">Tải lên</button>
        </form>

        <div v-if="loading" class="mt-4">Đang xử lý...</div>
        <div v-if="result" class="mt-4 p-4 border rounded">
            <h2 class="text-lg font-semibold">Kết quả:</h2>
            <p><strong>Số CCCD:</strong> {{ result.id_number }}</p>
            <p><strong>Họ tên:</strong> {{ result.name }}</p>
            <p><strong>Ngày sinh:</strong> {{ result.dob }}</p>
            <p><strong>Địa chỉ:</strong> {{ result.address }}</p>
        </div>
        <div v-if="error" class="mt-4 text-red-500">{{ error }}</div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const { $api } = useNuxtApp();
const file = ref(null);
const result = ref(null);
const error = ref(null);
const loading = ref(false);

const onFileChange = event => {
    file.value = event.target.files[0];
};

const uploadImage = async () => {
    if (!file.value) return;

    loading.value = true;
    error.value = null;
    result.value = null;

    const formData = new FormData();
    formData.append('image', file.value);

    try {
        const response = await $api('/ocr/citizen-id', {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            },
            body: formData
        });

        if (response.success) {
            result.value = response.data;
        } else {
            error.value = response.error;
        }
    } catch (err) {
        console.log(err);
        error.value = 'Có lỗi xảy ra khi xử lý ảnh';
    } finally {
        loading.value = false;
    }
};
</script>

<style lang="scss" scoped></style>
