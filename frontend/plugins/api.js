// Plugin cung cấp API client cho ứng dụng
export default defineNuxtPlugin(nuxtApp => {
    // Lấy cấu hình runtime
    const config = useRuntimeConfig();

    // Tạo instance $fetch với cấu hình mặc định
    const api = $fetch.create({
        baseURL: config.public.apiBaseUrl, // URL API cơ bản
        headers: {
            Accept: 'application/json' // Yêu cầu trả về định dạng JSON
        },
        credentials: 'include' // Bao gồm cookie trong các yêu cầu
    });

    // Cung cấp API client cho ứng dụng
    return {
        provide: {
            api
        }
    };
});
