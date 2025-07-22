// plugins/fetchConfig.client.ts
export default defineNuxtPlugin(async nuxtApp => {
    try {
        const res = await nuxtApp.$api('/configs', {
            baseURL: useRuntimeConfig().public.apiBaseUrl
        });

        const configData = {};
        res.forEach(item => {
            // Parse chuỗi JSON cho supported_banks
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

        useState('configs', () => configData);
    } catch (err) {
        console.error('Fetch config error:', err);
    }
});
