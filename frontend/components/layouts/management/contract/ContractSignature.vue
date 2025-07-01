<template>
    <div class="signature-section">
        <SignaturePad @signature-saved="signature => $emit('signature-saved', signature)" @signature-cleared="$emit('signature-cleared')" />
        <div class="d-flex justify-content-center">
            <button @click="$emit('sign-contract')" class="button margin-top-15" :disabled="saveLoading || !signatureData">
                <span v-if="saveLoading" class="button-spinner"></span>
                {{ saveLoading ? 'Đang ký...' : 'Ký hợp đồng' }}
            </button>
        </div>
    </div>
</template>

<script setup>
defineProps({
    saveLoading: {
        type: Boolean,
        required: true
    },
    signatureData: {
        type: String,
        default: null
    }
});

defineEmits(['signature-saved', 'signature-cleared', 'sign-contract']);
</script>

<style scoped>
.signature-section {
    margin-top: 20px;
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
</style>
