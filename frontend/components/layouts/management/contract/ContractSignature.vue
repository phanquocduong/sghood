<template>
    <div class="signature-section">
        <!-- Component vẽ chữ ký -->
        <SignaturePad @signature-saved="signature => $emit('signature-saved', signature)" @signature-cleared="$emit('signature-cleared')" />
        <div class="d-flex justify-content-center">
            <!-- Nút ký hợp đồng -->
            <button @click="$emit('sign-contract')" class="button margin-top-15" :disabled="saveLoading || !signatureData">
                <span v-if="saveLoading" class="button-spinner"></span>
                {{ saveLoading ? 'Đang ký...' : 'Ký hợp đồng' }}
            </button>
        </div>
    </div>
</template>

<script setup>
// Định nghĩa props
defineProps({
    saveLoading: {
        type: Boolean,
        required: true // Trạng thái loading khi ký hợp đồng
    },
    signatureData: {
        type: String,
        default: null // Dữ liệu chữ ký
    }
});

// Định nghĩa emits
defineEmits(['signature-saved', 'signature-cleared', 'sign-contract']);
</script>

<style scoped>
/* CSS cho khu vực chữ ký */
.signature-section {
    margin-top: 20px;
}

/* CSS cho spinner trong nút */
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

/* Hiệu ứng xoay cho spinner */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* CSS cho nút khi bị vô hiệu hóa */
.button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
