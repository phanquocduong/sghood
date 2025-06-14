export default defineNuxtConfig({
    compatibilityDate: '2024-11-01',
    devtools: { enabled: true },
    css: ['public/css/style.css', 'public/css/main-color.css',],
    plugins: [
        '~/plugins/firebase.js',
        '~/plugins/api.js',
        '~/plugins/toast.js',
        '~/plugins/template-scripts.client.js',
        '~/plugins/auth.js',
        '~/plugins/dropzone.client.js'
    ],
    devServer: {
        host: '0.0.0.0',
        port: 3000
    },
    components: [
        {
            path: '~/components',
            pathPrefix: false
        }
    ],
    modules: ['@pinia/nuxt'],
    runtimeConfig: {
        public: {
            baseUrl: 'http://127.0.0.1:8000',
            apiBaseUrl: 'http://127.0.0.1:8000/api'
        }
    }
});
