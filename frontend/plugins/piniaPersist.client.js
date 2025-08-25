import piniaPluginPersistedstate from 'pinia-plugin-persistedstate';

// Định nghĩa plugin để tích hợp pinia-plugin-persistedstate vào Nuxt
export default defineNuxtPlugin(nuxtApp => {
    // Sử dụng plugin persistedstate để lưu trữ trạng thái của Pinia vào localStorage hoặc sessionStorage
    nuxtApp.$pinia.use(piniaPluginPersistedstate);
});
