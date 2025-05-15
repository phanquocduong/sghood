export default defineNuxtConfig({
    compatibilityDate: '2024-11-01',

    future: {
        compatibilityVersion: 4
    },

    experimental: {
        sharedPrerenderData: false,
        relativeWatchPaths: true
    },

    features: {
        inlineStyles: true
    },

    unhead: {
        renderSSRHeadOptions: {
            omitLineBreaks: false
        }
    },

    devtools: { enabled: true },

    modules: ['@nuxtjs/tailwindcss', '@nuxt/icon'],

    plugins: ['~/plugins/firebase.js', '~/plugins/api.js', '~/plugins/toast.js']
});
