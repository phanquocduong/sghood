<template>
    <div v-if="isLoading" class="loading-overlay">
        <div class="spinner"></div>
        <p>{{ message }}</p>
    </div>
</template>

<script setup>
defineProps({
    message: {
        type: String,
        default: 'Đang tải...'
    },
    isLoading: {
        type: Boolean,
        required: true
    }
});
</script>

<style scoped>
.loading-overlay {
    position: absolute;
    inset: 0;
    background: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #f91942;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

p {
    color: #333;
    margin-top: 10px;
    font-size: 16px;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>
