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
    console.log('[firebase-messaging-sw.js] Received background message ', payload);

    // Customize notification here
    const notificationTitle = payload.notification?.title || 'Background Message';
    const notificationOptions = {
        body: payload.notification?.body || 'Background Message body.',
        icon: '/icon-192x192.png', // Add your app icon
        badge: '/icon-72x72.png',
        tag: 'background-message',
        data: payload.data || {}
    };

    return self.registration.showNotification(notificationTitle, notificationOptions);
});

// Handle notification click
self.addEventListener('notificationclick', function (event) {
    console.log('[firebase-messaging-sw.js] Notification click received.');

    event.notification.close();

    // Handle click action
    event.waitUntil(clients.openWindow('/'));
});
