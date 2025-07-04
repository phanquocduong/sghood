<template>
    <div>
        <Titlebar title="Thêm Yêu Cầu Sửa Chữa" />

        <Loading :is-loading="loading" />

        <div v-if="loading" class="text-center p-5">
            <p>Đang tải...</p>
        </div>

        <div v-else class="page-container">
            <form @submit.prevent="submitForm" class="repair-form">
                <!-- Tiêu đề -->
                <div class="form-group">
                    <label for="title">Tiêu đề yêu cầu:</label>
                    <input v-model="form.title" type="text" id="title" required placeholder="Nhập tiêu đề..." />
                </div>

                <!-- Mô tả -->
                <div class="form-group">
                    <label for="description">Mô tả chi tiết:</label>
                    <textarea v-model="form.description" id="description" rows="4" required placeholder="Nhập mô tả chi tiết về sự cố..." />
                </div>

                <!-- Dropzone hình ảnh -->
                <div class="form-group">
                    <label>Hình ảnh (tối đa 4 ảnh):</label>
                    <div id="dropzone-upload" class="dropzone">
                        <div class="dz-default dz-message">
                            <span>Kéo ảnh vào đây hoặc nhấn để chọn</span>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                 <button
                            type="submit"
                            class="submit button"
                            id="submit"
                            value="Gửi tin nhắn"
                            :disabled="isLoading"
                            style="margin-bottom: 10px; margin-top: -10px"
                        >
                            <span v-if="isLoading" class="spinner"></span>
                            {{ isLoading ? ' Đang gửi...' : 'Gửi đi' }}
                        </button>
            </form>
        </div>
    </div>
</template>

<script setup>
definePageMeta({ layout: 'management' });

import { ref, watch, nextTick, onMounted } from 'vue';
import Dropzone from 'dropzone';
import 'dropzone/dist/dropzone.css';
import { useRouter  } from 'vue-router';
const router = useRouter()
Dropzone.autoDiscover = false;
const {$api} = useNuxtApp()
const loading = ref(true);
const isLoading = ref (false)

const form = ref({
    title: '',
    description: '',
    status: 'Chờ xác nhận',
    images: []
});

let dropzoneInstance = null;

const initDropzone = () => {
    const dropzoneEl = document.querySelector('#dropzone-upload');
    if (!dropzoneEl) return;

    if (dropzoneInstance) {
        dropzoneInstance.destroy();
    }

    dropzoneInstance = new Dropzone(dropzoneEl, {
        url: '/', // Không upload thực tế
        maxFiles: 4,
        acceptedFiles: 'image/*',
        addRemoveLinks: true,
        dictDefaultMessage: 'Kéo ảnh vào đây hoặc nhấn để chọn',
        dictRemoveFile: 'Xoá',
        autoProcessQueue: true,
        init() {
            this.on('addedfile', file => {
                if (form.value.images.length >= 4) {
                    this.removeFile(file);
                    alert('Chỉ được tải tối đa 4 ảnh.');
                    return;
                }
                form.value.images.push(file);
            });

            this.on('removedfile', file => {
                form.value.images = form.value.images.filter(f => f.name !== file.name);
            });
        }
    });
};
const submitForm = async ()=>{
    if(form.value.images.length === 0 ){
      alert('Chon hinh di thang con cac')
      return
    }
    const formData = new FormData();
    formData.append('title',form.value.title);
    formData.append('description',form.value.description);
    formData.append('status',form.value.status);
     

    form.value.images.forEach((file )=>{
        formData.append(`images[]`,file)
    });
    isLoading.value = true;
        try{
        const res = await $api(`/repair-requests`,{
        method:'POST',
        body:formData,
        headers:{
            'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value,
            'Accept': 'application/json'
        }
    })
       dropzoneInstance.removeAllFiles();
       router.push('/quan-ly/quan-ly-sua-chua')
    } catch (e) {
    console.log('Lỗi gửi form:', e?.response?._data || e);
  } finally {
    isLoading.value = false;
  }
    
}
onMounted(() => {
    setTimeout(() => {
        loading.value = false;
    }, 300);
});

watch(loading, async val => {
    if (!val) {
        await nextTick();
        initDropzone();
    }
});


</script>

<style scoped>
.page-container {
    max-width: 800px;
    margin: 30px auto;
    padding: 30px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 0 12px rgba(0, 0, 0, 0.1);
}

.repair-form .form-group {
    margin-bottom: 20px;
}

.repair-form label {
    display: block;
    font-weight: bold;
    margin-bottom: 6px;
}

.repair-form input,
.repair-form textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 15px;
}

/* Dropzone style */
.dropzone {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 20px;
    border: 2px dashed #4caf50;
    border-radius: 10px;
    background-color: #f8f8f8;
    justify-content: start;
    min-height: 150px;
}
.dropzone:hover {
    border-color: #59b02c;
}
.dz-message {
    font-size: 20px;
    color: #777;
    margin: auto;
}
.dz-preview {
    width: 20%;
    position: relative;
    margin-bottom: 10px;
}
.dz-image {
    border-radius: 6px;
    overflow: hidden;
}
.dz-remove {
    display: block;
    margin-top: 6px;
    color: #d32f2f;
    text-decoration: underline;
    cursor: pointer;
    font-weight: bold;
    background: none !important;
    border: none !important;
}

/* Button */
.submit-btn {
    background: #d32f2f;
    color: white;
    padding: 10px 20px;
    border: none;
    font-weight: bold;
    border-radius: 999px;
    cursor: pointer;
    transition: 0.3s;
}
.spinner {
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

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
