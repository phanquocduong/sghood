export default defineNuxtPlugin(nuxtApp => {
    const config = useRuntimeConfig();

    const api = $fetch.create({
        baseURL: config.public.apiBaseUrl,
        headers: {
            Accept: 'application/json'
        },
        credentials: 'include'
        // onRequest({ request, options }) {
        //     // Add CSRF token to headers if available
        //     if (process.client) {
        //         const csrfToken = useCookie('XSRF-TOKEN').value;
        //         if (csrfToken && options.method !== 'GET') {
        //             options.headers = {
        //                 ...options.headers,
        //                 'X-XSRF-TOKEN': csrfToken
        //             };
        //         }
        //     }
        // },
        // onResponseError({ request, response, options }) {
        //     // Handle authentication errors globally
        //     if (response.status === 401) {
        //         // Clear any stored user data
        //         if (process.client) {
        //             // Navigate to login or handle unauthenticated state
        //             console.log('Unauthenticated request detected');
        //         }
        //     }

        //     // Handle CSRF token mismatch
        //     if (response.status === 419) {
        //         console.warn('CSRF token mismatch - may need to refresh');
        //     }
        // }
    });

    return {
        provide: {
            api
        }
    };
});
