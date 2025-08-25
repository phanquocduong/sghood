// Cấu hình ứng dụng Nuxt
export default defineNuxtConfig({
    // Cấu hình thẻ head của ứng dụng
    app: {
        head: {
            link: [
                // Favicon cơ bản
                { rel: 'icon', type: 'image/x-icon', href: '/images/sghood_icon.png' },
                // Favicon cho Apple Touch Icon (iOS)
                { rel: 'apple-touch-icon', sizes: '180x180', href: '/images/sghood_icon.png' },
                // Favicon cho Android
                { rel: 'icon', type: 'image/png', sizes: '192x192', href: '/images/sghood_icon.png' }
            ]
        }
    },
    // Ngày tương thích
    compatibilityDate: '2024-11-01',
    // Bật devtools trong môi trường phát triển
    devtools: { enabled: true },
    // Các file CSS toàn cục
    css: ['public/css/fonts.css', 'public/css/style.css', 'public/css/main-color.css'],
    // Danh sách plugin
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
    // Cấu hình dev server
    devServer: {
        host: '0.0.0.0',
        port: 3000
    },
    // Cấu hình components
    components: [
        {
            path: '~/components',
            pathPrefix: false
        }
    ],
    // Cấu hình modules
    modules: ['@pinia/nuxt', '@nuxtjs/google-fonts'],

    // Cấu hình Google Fonts
    googleFonts: {
        display: 'swap',
        families: {
            Inter: [400, 600, 700]
        },
        preconnect: true
    },

    // Cấu hình runtime
    runtimeConfig: {
        public: {
            baseUrl: 'http://127.0.0.1:8000', // URL cơ bản
            apiBaseUrl: 'http://127.0.0.1:8000/api', // URL API
            sepayBank: 'ACB', // Ngân hàng Sepay
            sepayAccountNumber: '31214717', // Số tài khoản Sepay
            sepayTemplate: 'compact' // Template Sepay
        }
    },

    // Cấu hình build
    build: {
        transpile: ['vue-toastification'] // Transpile thư viện vue-toastification
    }
});
