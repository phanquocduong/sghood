<template>
    <NuxtLayout>
        <NuxtPage></NuxtPage>
    </NuxtLayout>
</template>

<script setup></script>

<style>
/* Tùy chỉnh thanh loading của nprogress */
#nprogress .bar {
    background: #3b82f6; /* Màu xanh */
    height: 4px; /* Độ dày */
}

#nprogress .spinner {
    display: none; /* Ẩn spinner mặc định nếu không cần */
}
</style>
