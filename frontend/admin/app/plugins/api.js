export default defineNuxtPlugin(nuxtApp => {
    const api = $fetch.create({
        baseURL: 'http://127.0.0.1:8000/api',
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
