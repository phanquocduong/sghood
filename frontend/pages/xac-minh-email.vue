<template>
    <div class="container">
        <h2>Xác minh Email</h2>
        <div v-if="loading" class="text-center">
            <span class="spinner"></span>
            Đang xác minh...
        </div>
        <div v-else-if="message" class="notification" :class="{ success: !error, error: error }">
            {{ message }}
        </div>
        <div v-else class="notification error">Liên kết xác minh không hợp lệ.</div>
        <NuxtLink to="/" class="button border margin-top-10" v-if="!redirecting">Quay lại trang chủ</NuxtLink>
    </div>
</template>

<script setup>
definePageMeta({
    layout: 'blank'
});

import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useToast } from 'vue-toastification';

const router = useRouter();
const route = useRoute();
const toast = useToast();
const loading = ref(true);
const message = ref('');
const error = ref(false);
const redirecting = ref(false);

onMounted(() => {
    const { message: msg, error: err } = route.query;

    if (msg) {
        message.value = decodeURIComponent(msg);
        error.value = false;
    } else if (err) {
        message.value = decodeURIComponent(err);
        error.value = true;
        toast.error(message.value);
    } else {
        message.value = 'Liên kết xác minh không hợp lệ';
        error.value = true;
        toast.error(message.value);
    }

    loading.value = false;
    setTimeout(() => {
        redirecting.value = true;
        router.push('/');
    }, 3000);
});
</script>

<style scoped>
.container {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    text-align: center;
}

.notification {
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.success {
    background-color: #d4edda;
    color: #155724;
}

.error {
    background-color: #f8d7da;
    color: #721c24;
}
</style>
