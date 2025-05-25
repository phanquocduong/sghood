export default defineNuxtPlugin(nuxtApp => {
    const config = useRuntimeConfig();

    const api = $fetch.create({
        baseURL: config.public.apiBaseUrl,
        headers: {
            Accept: 'application/json'
        },
        credentials: 'include'
    });

    return {
        provide: {
            api
        }
    };
});
