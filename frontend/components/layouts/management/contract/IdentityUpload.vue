<template>
    <div class="col-lg-3 col-md-12">
        <div class="dashboard-list-box margin-top-0">
            <h4 class="gray">Giấy tờ tùy thân</h4>
            <div class="dashboard-list-box-static">
                <div class="upload-instructions">
                    <h5>Hướng dẫn tải ảnh CCCD</h5>
                    <ul>
                        <li>Tải lên đúng 2 ảnh: mặt trước và mặt sau của căn cước công dân.</li>
                        <li>Đảm bảo ảnh rõ nét, không bị mờ hoặc nhòe.</li>
                        <li>Ảnh không được nghiêng, chụp thẳng để hiển thị đầy đủ thông tin.</li>
                        <li>Cắt background sát với thẻ căn cước, không để các vật thể khác trong ảnh.</li>
                        <li>Định dạng ảnh: JPEG hoặc PNG, kích thước tối đa 2MB mỗi ảnh.</li>
                    </ul>
                </div>
                <p v-if="identityDocument?.has_valid" class="valid-message">Ảnh căn cước đã hợp lệ, không thể tải lên thêm.</p>
                <div class="edit-profile-photo">
                    <form id="dropzone-upload" class="dropzone" :class="{ 'dropzone-disabled': identityDocument?.has_valid }"></form>
                </div>
            </div>
        </div>
        <button @click="$emit('save-contract')" class="button margin-top-20" :disabled="saveLoading || !isFormComplete">
            <span v-if="saveLoading" class="button-spinner"></span>
            {{ saveLoading ? 'Đang lưu...' : 'Lưu hợp đồng' }}
        </button>
    </div>
</template>

<script setup>
defineProps({
    identityDocument: {
        type: Object,
        required: true
    },
    isFormComplete: {
        type: Boolean,
        required: true
    },
    saveLoading: {
        type: Boolean,
        required: true
    }
});

defineEmits(['save-contract', 'identity-upload']);
</script>

<style scoped>
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

.dashboard-list-box ul li {
    padding: 6px 12px;
}

.upload-instructions {
    margin-bottom: 15px;
    text-align: left;
}

.upload-instructions h5 {
    font-size: 1.6rem;
    color: #333;
    margin-bottom: 12px;
}

.upload-instructions ul {
    list-style-type: disc;
    padding-left: 16px;
    font-size: 1.4rem;
    color: #555;
}

.upload-instructions li {
    margin-bottom: 8px;
}

@media (max-width: 992px) {
    .dashboard-list-box {
        margin-top: 20px !important;
    }

    .upload-instructions h5 {
        font-size: 1.5rem;
    }

    .upload-instructions ul {
        font-size: 1.3rem;
    }
}

@media (max-width: 768px) {
    .upload-instructions h5 {
        font-size: 1.4rem;
    }

    .upload-instructions ul {
        font-size: 1.2rem;
        padding-left: 15px;
    }
}

@media (max-width: 576px) {
    .upload-instructions h5 {
        font-size: 1.3rem;
    }

    .upload-instructions ul {
        font-size: 1.1rem;
        padding-left: 10px;
    }
}
</style>
