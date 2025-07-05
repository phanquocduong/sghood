export default defineNuxtConfig({

    compatibilityDate: '2024-11-01',
    devtools: { enabled: true },
    // ssr:false, // khi up lên server thì bỏ comment dòng này
    css: ['public/css/fonts.css', 'public/css/style.css', 'public/css/main-color.css'],
    plugins: [
        '~/plugins/firebase.js',
        '~/plugins/api.js',
        '~/plugins/toast.js',
        '~/plugins/template-scripts.client.js',
        { src: '~/plugins/auth.js', mode: 'client' },
        '~/plugins/dropzone.client.js',
        '~/plugins/service-worker.client.js',
        '~/plugins/firebase-messaging.client.js'
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
    modules: ['@pinia/nuxt', '@nuxtjs/google-fonts'],

    googleFonts: {
        display: 'swap',
        families: {
            Inter: [400, 600, 700]
        },
        preconnect: true
    },

    runtimeConfig: {
        public: {
            baseUrl: 'http://127.0.0.1:8000',
            apiBaseUrl: 'http://127.0.0.1:8000/api',
            sepayBank: 'ACB',
            sepayAccountNumber: '31214717',
            sepayTemplate: 'compact'
        }
    }
});
