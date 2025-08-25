// Plugin lấy cấu hình từ API
export default defineNuxtPlugin(async nuxtApp => {
    try {
        // Gọi API để lấy danh sách cấu hình
        const res = await nuxtApp.$api('/configs', {
            baseURL: useRuntimeConfig().public.apiBaseUrl
        });

        // Khởi tạo object để lưu trữ cấu hình
        const configData = {};
        res.forEach(item => {
            // Parse chuỗi JSON cho supported_banks nếu có
            if (item.config_key === 'supported_banks' && typeof item.config_value === 'string') {
                try {
                    configData[item.config_key] = JSON.parse(item.config_value);
                } catch (error) {
                    console.error(`Lỗi khi parse ${item.config_key}:`, error);
                    configData[item.config_key] = [];
                }
            } else {
                configData[item.config_key] = item.config_value;
            }
        });

        // Lưu cấu hình vào state toàn cục
        useState('configs', () => configData);
    } catch (err) {
        console.error('Fetch config error:', err); // Ghi log lỗi nếu có
    }
});
