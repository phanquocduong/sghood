// Import Firebase scripts for v8 (Service Worker sử dụng v8 syntax)
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');

// Firebase configuration
const firebaseConfig = {
    apiKey: 'AIzaSyAnEYDqg-BwdYKJLoz1bDG1x62JnRsVVB0',
    authDomain: 'tro-viet.firebaseapp.com',
    projectId: 'tro-viet',
    storageBucket: 'tro-viet.firebasestorage.app',
    messagingSenderId: '1000506063285',
    appId: '1:1000506063285:web:47e80b8489d09c8ce8c1fc',
    measurementId: 'G-LRB092W6Y5'
};

// Initialize Firebase
firebase.initializeApp(firebaseConfig);

// Initialize Firebase Cloud Messaging and get a reference to the service
const messaging = firebase.messaging();

// Handle background messages
messaging.onBackgroundMessage(function (payload) {
    const notificationTitle = payload.notification?.title;
    const notificationOptions = {
        body: payload.notification?.body,
        icon: '/images/sghood_logo1.png',
        badge: '/images/sghood_logo2.png',
        tag: 'background-message',
        data: payload.data || {}
    };

    return self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle notification click with link
self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    const link = event.notification.data?.link;
    console.log('Notification click data:', event.notification.data);

    if (link) {
        event.waitUntil(
            clients.matchAll({ type: 'window' }).then(windowClients => {
                for (let client of windowClients) {
                    if (client.url === link && 'focus' in client) {
                        return client.focus();
                    }
                }
                if (clients.openWindow) {
                    return clients.openWindow(link);
                }
            })
        );
    } else {
        event.waitUntil(clients.openWindow('/'));
    }
});
