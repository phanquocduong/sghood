<template>
    <div class="col-lg-3 col-md-3">
        <div class="dashboard-list-box margin-top-0">
            <h4 class="gray">Giấy tờ tùy thân</h4>
            <div class="dashboard-list-box-static">
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
</style>
