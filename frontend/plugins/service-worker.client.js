export default defineNuxtPlugin(nuxtApp => {
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker
            .register('/firebase-messaging-sw.js')
            .then(registration => {
                console.log('Service Worker registered:', registration.scope);
            })
            .catch(error => {
                console.error('Service Worker registration failed:', error);
            });
    }
});
