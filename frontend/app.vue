<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>

<script setup>
import { onMounted } from 'vue';

onMounted(() => {
  if (process.client) {
    const nuxtApp = useNuxtApp();
    if (nuxtApp.$saveFcmToken) {
      nuxtApp.$saveFcmToken();
    } else {
      console.error('❌ saveFcmToken không khả dụng');
    }
  }
});
</script>

<style></style>