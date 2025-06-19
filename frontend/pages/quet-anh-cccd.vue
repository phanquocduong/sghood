<template>
    <div class="container">
        <div class="card">
            <h2 class="card-header">Quét Căn Cước Công Dân</h2>

            <div class="form-group">
                <label for="cccd">Chọn ảnh CCCD:</label>
                <input type="file" id="cccd" accept="image/*" @change="handleFileChange" />
            </div>

            <button :disabled="!file || isLoading" @click="submitImage" class="btn">
                {{ isLoading ? 'Đang quét...' : 'Quét CCCD' }}
            </button>

            <div v-if="result" class="result">
                <h3>Kết quả:</h3>
                <pre>{{ result }}</pre>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const { $api } = useNuxtApp();
const file = ref(null);
const isLoading = ref(false);
const result = ref(null);

const handleFileChange = event => {
    file.value = event.target.files[0];
};

const submitImage = async () => {
    if (!file.value) return;

    isLoading.value = true;
    result.value = null;

    try {
        const formData = new FormData();
        formData.append('cccd_image', file.value);

        const response = await $api('/ocr/extract-cccd-front', {
            method: 'POST',
            body: formData,
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            }
        });

        if (response.status === 'success') {
            result.value = response.data;
        } else {
            throw new Error(response.message);
        }
    } catch (error) {
        console.log(error);
        alert('Lỗi: ' + error.message);
    } finally {
        isLoading.value = false;
    }
};
</script>
<style scoped>
.container {
    max-width: 600px;
    margin: 40px auto;
    padding: 24px;
}

.card {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.card-header {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 16px;
}

.form-group {
    margin-bottom: 16px;
    display: flex;
    flex-direction: column;
}

input[type='file'] {
    margin-top: 8px;
}

.btn {
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
}

.btn:disabled {
    background-color: #aaa;
    cursor: not-allowed;
}

.result {
    margin-top: 24px;
    background: #f1f1f1;
    padding: 16px;
    border-radius: 6px;
}
</style>
