<template>
    <Loading :is-loading="isLoading" />
    <div v-if="!isLoading">
        <NuxtPage />
    </div>
</template>

<script setup>
import { useRoute } from 'vue-router';

const route = useRoute();
const isLoading = ref(false);
watch(
    () => route.fullPath,
    async () => {
        isLoading.value = true;
        await nextTick();
        setTimeout(() => {
            isLoading.value = false;
        }, 500);
    },
    { immediate: true }
);
</script>

<style>
body {
    background: #f4f4f4;
}
</style>
