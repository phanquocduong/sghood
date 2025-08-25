<template>
    <div>
        <!-- Tiêu đề của trang thêm yêu cầu sửa chữa -->
        <Titlebar title="Thêm Yêu Cầu " />

        <!-- Hiển thị spinner khi đang tải trang -->
        <Loading :is-loading="loading" />

        <!-- Hiển thị thông báo đang tải khi loading = true -->
        <div v-if="loading" class="text-center p-5">
            <p>Đang tải...</p>
        </div>

        <!-- Form nhập thông tin yêu cầu sửa chữa -->
        <div v-else class="page-container">
            <form @submit.prevent="submitForm" class="repair-form">
                <!-- Trường nhập tiêu đề yêu cầu -->
                <div class="form-group">
                    <label for="title">Tiêu đề yêu cầu:</label>
                    <input v-model="form.title" type="text" id="title" required placeholder="Nhập tiêu đề..." />
                </div>

                <!-- Trường nhập mô tả chi tiết -->
                <div class="form-group">
                    <label for="description">Mô tả chi tiết:</label>
                    <textarea v-model="form.description" id="description" rows="4" required placeholder="Nhập mô tả chi tiết về sự cố..." />
                </div>

                <!-- Khu vực tải lên hình ảnh sử dụng Dropzone -->
                <div class="form-group">
                    <label>Hình ảnh (tối đa 4 ảnh):</label>
                    <div id="dropzone-upload" class="dropzone">
                        <div class="dz-default dz-message">
                            <span>Kéo ảnh vào đây hoặc nhấn để chọn</span>
                        </div>
                    </div>
                </div>

                <!-- Nút gửi form -->
                <button
                    type="submit"
                    class="submit button margin-top-10"
                    id="submit"
                    value="Gửi tin nhắn"
                    :disabled="isLoading"
                    style="margin-bottom: 10px; margin-top: -10px"
                >
                    <span v-if="isLoading" class="spinner"></span>
                    <!-- Hiển thị spinner khi đang gửi -->
                    {{ isLoading ? ' Đang gửi...' : 'Gửi đi' }}
                    <!-- Văn bản nút -->
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, nextTick, onMounted } from 'vue';
import Dropzone from 'dropzone'; // Thư viện Dropzone để tải lên hình ảnh
import 'dropzone/dist/dropzone.css'; // Style mặc định của Dropzone
import { useRouter } from 'vue-router';
import { useAppToast } from '~/composables/useToast';

// Tắt tính năng tự động tìm kiếm của Dropzone
Dropzone.autoDiscover = false;

// Cấu hình metadata cho trang, sử dụng layout 'management'
definePageMeta({ layout: 'management' });

// Khởi tạo các biến và đối tượng
const router = useRouter(); // Router để điều hướng
const { $api } = useNuxtApp(); // Lấy instance API từ Nuxt
const loading = ref(true); // Trạng thái loading khi khởi tạo trang
const isLoading = ref(false); // Trạng thái loading khi gửi form
const toast = useAppToast(); // Hàm hiển thị thông báo
const form = ref({
    title: '', // Tiêu đề yêu cầu
    description: '', // Mô tả chi tiết
    status: 'Chờ xác nhận', // Trạng thái mặc định
    images: [] // Danh sách hình ảnh
});

let dropzoneInstance = null; // Biến lưu trữ instance của Dropzone

// Hàm khởi tạo Dropzone để tải lên hình ảnh
const initDropzone = () => {
    const dropzoneEl = document.querySelector('#dropzone-upload'); // Lấy phần tử Dropzone
    if (!dropzoneEl) return; // Thoát nếu không tìm thấy phần tử

    if (dropzoneInstance) {
        dropzoneInstance.destroy(); // Hủy instance Dropzone cũ nếu tồn tại
    }

    // Tạo instance Dropzone mới
    dropzoneInstance = new Dropzone(dropzoneEl, {
        url: '/', // URL giả, không upload thực tế
        maxFiles: 4, // Giới hạn tối đa 4 ảnh
        acceptedFiles: 'image/*', // Chỉ chấp nhận file ảnh
        addRemoveLinks: true, // Thêm liên kết xóa file
        dictDefaultMessage: 'Kéo ảnh vào đây hoặc nhấn để chọn', // Thông báo mặc định
        dictRemoveFile: 'Xoá', // Văn bản nút xóa
        autoProcessQueue: true, // Tự động xử lý hàng đợi
        init() {
            // Sự kiện khi thêm file
            this.on('addedfile', file => {
                if (form.value.images.length >= 4) {
                    this.removeFile(file); // Xóa file nếu vượt quá giới hạn
                    alert('Chỉ được tải tối đa 4 ảnh.'); // Thông báo lỗi
                    return;
                }
                form.value.images.push(file); // Thêm file vào danh sách
            });

            // Sự kiện khi xóa file
            this.on('removedfile', file => {
                form.value.images = form.value.images.filter(f => f.name !== file.name); // Xóa file khỏi danh sách
            });
        }
    });
};

// Hàm xử lý gửi form
const submitForm = async () => {
    if (form.value.images.length === 0) {
        toast.error('Vui lòng chọn ít nhất 1 hình'); // Thông báo lỗi nếu không có ảnh
        return;
    }
    const formData = new FormData(); // Tạo FormData để gửi dữ liệu
    formData.append('title', form.value.title); // Thêm tiêu đề
    formData.append('description', form.value.description); // Thêm mô tả
    formData.append('status', form.value.status); // Thêm trạng thái

    // Thêm từng ảnh vào FormData
    form.value.images.forEach(file => {
        formData.append(`images[]`, file);
    });
    isLoading.value = true; // Bật trạng thái loading
    try {
        // Gửi yêu cầu POST để tạo yêu cầu sửa chữa
        await $api('/repair-requests', {
            method: 'POST',
            body: formData,
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value // Thêm token xác thực
            }
        });
        dropzoneInstance.removeAllFiles(); // Xóa tất cả file trong Dropzone
        router.push('/quan-ly/yeu-cau-sua-chua'); // Chuyển hướng về trang danh sách
    } catch (e) {
        console.error('Lỗi gửi form:', e?.response?._data || e); // Ghi log lỗi
    } finally {
        isLoading.value = false; // Tắt trạng thái loading
    }
};

// Khởi tạo trang
onMounted(() => {
    setTimeout(() => {
        loading.value = false; // Tắt trạng thái loading sau 300ms
    }, 300);
});

// Theo dõi trạng thái loading để khởi tạo Dropzone
watch(loading, async val => {
    if (!val) {
        await nextTick();
        initDropzone(); // Khởi tạo Dropzone sau khi DOM sẵn sàng
    }
});
</script>

<style scoped>
/* Style cho container của trang */
.page-container {
    max-width: 800px;
    margin: 30px auto;
    padding: 30px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 0 12px rgba(0, 0, 0, 0.1); /* Hiệu ứng bóng */
}

/* Style cho form */
.repair-form .form-group {
    margin-bottom: 20px;
}

/* Style cho nhãn của form */
.repair-form label {
    display: block;
    font-weight: bold;
    margin-bottom: 6px;
}

/* Style cho input và textarea */
.repair-form input,
.repair-form textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 15px;
}

/* Style cho khu vực Dropzone */
.dropzone {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 20px;
    border: 2px dashed #4caf50; /* Đường viền nét đứt màu xanh */
    border-radius: 10px;
    background-color: #f8f8f8;
    justify-content: start;
    min-height: 150px;
}

/* Hiệu ứng hover cho Dropzone */
.dropzone:hover {
    border-color: #59b02c; /* Đổi màu viền khi hover */
}

/* Style cho thông báo mặc định của Dropzone */
.dz-message {
    font-size: 20px;
    color: #777;
    margin: auto;
}

/* Style cho bản xem trước ảnh trong Dropzone */
.dz-preview {
    width: 20%;
    position: relative;
    margin-bottom: 10px;
}

/* Style cho ảnh trong Dropzone */
.dz-image {
    border-radius: 6px; /* Bo góc */
    overflow: hidden;
}

/* Style cho nút xóa file trong Dropzone */
.dz-remove {
    display: block;
    margin-top: 6px;
    color: #d32f2f; /* Màu đỏ */
    text-decoration: underline;
    cursor: pointer;
    font-weight: bold;
    background: none !important;
    border: none !important;
}

/* Style cho nút gửi */
.submit-btn {
    background: #d32f2f; /* Màu đỏ */
    color: white;
    padding: 10px 20px;
    border: none;
    font-weight: bold;
    border-radius: 999px; /* Bo tròn hoàn toàn */
    cursor: pointer;
    transition: 0.3s; /* Hiệu ứng chuyển đổi */
}

/* Style cho spinner khi gửi form */
.spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite; /* Hiệu ứng xoay */
    margin-right: 8px;
    vertical-align: middle;
}

/* Hiệu ứng xoay cho spinner */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Style cho nút khi bị vô hiệu hóa */
.button:disabled {
    opacity: 0.6; /* Giảm độ mờ */
    cursor: not-allowed; /* Con trỏ không cho phép click */
}
</style>
