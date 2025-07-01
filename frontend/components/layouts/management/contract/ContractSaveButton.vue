<template>
    <div class="d-flex justify-content-center">
        <button @click="$emit('save-contract')" class="button margin-top-15" :disabled="saveLoading">
            <span v-if="saveLoading" class="button-spinner"></span>
            {{ saveLoading ? 'Đang lưu...' : 'Lưu hợp đồng' }}
        </button>
    </div>
</template>

<script setup>
defineProps({
    saveLoading: {
        type: Boolean,
        required: true
    }
});

defineEmits(['save-contract']);
</script>

<style scoped>
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
