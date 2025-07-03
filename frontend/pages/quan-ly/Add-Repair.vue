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
          <input
            v-model="form.title"
            type="text"
            id="title"
            required
            placeholder="Nhập tiêu đề..."
          />
        </div>

        <!-- Mô tả -->
        <div class="form-group">
          <label for="description">Mô tả chi tiết:</label>
          <textarea
            v-model="form.description"
            id="description"
            rows="4"
            required
            placeholder="Nhập mô tả chi tiết về sự cố..."
          />
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
        <button type="submit" class="submit-btn">Gửi yêu cầu</button>
      </form>
    </div>
  </div>
</template>

<script setup>
definePageMeta({ layout: 'management' })

import { ref, watch, nextTick, onMounted } from 'vue'
import Dropzone from 'dropzone'
import 'dropzone/dist/dropzone.css'

Dropzone.autoDiscover = false

const loading = ref(true)

const form = ref({
  title: '',
  description: '',
  status: 'Chờ xác nhận',
  images: []
})

let dropzoneInstance = null

const initDropzone = () => {
  const dropzoneEl = document.querySelector('#dropzone-upload')
  if (!dropzoneEl) return

  if (dropzoneInstance) {
    dropzoneInstance.destroy()
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
      this.on('queuecomplete', (file) => {
        if (form.value.images.length >= 4) {
          this.removeFile(file)
          alert('Chỉ được tải tối đa 4 ảnh.')
          return
        }
        form.value.images.push(file)
      })

      this.on('removedfile', (file) => {
        form.value.images = form.value.images.filter(f => f.name !== file.name)
      })
    }
  })
}

onMounted(() => {
  setTimeout(() => {
    loading.value = false
  }, 300)
})

watch(loading, async (val) => {
  if (!val) {
    await nextTick()
    initDropzone()
  }
})

const submitForm = () => {
  console.log('Dữ liệu gửi:', form.value)
  alert('Yêu cầu đã được gửi!')
  // TODO: gửi API khi backend sẵn
}
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
.submit-btn:hover {
  background: white;
  color: #d32f2f;
  border: 2px solid #d32f2f;
}
</style>
