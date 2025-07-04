importScripts("https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js");
importScripts(
    "https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js"
);

const firebaseConfig = {
    apiKey: "AIzaSyAnEYDqg-BwdYKJLoz1bDG1x62JnRsVVB0",
    authDomain: "tro-viet.firebaseapp.com",
    projectId: "tro-viet",
    storageBucket: "tro-viet.firebasestorage.app",
    messagingSenderId: "1000506063285",
    appId: "1:1000506063285:web:47e80b8489d09c8ce8c1fc",
    measurementId: "G-LRB092W6Y5",
};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

// Xử lý thông báo nền (nếu cần)
messaging.onBackgroundMessage(function (payload) {
    console.log(
        "[firebase-messaging-sw.js] Received background message ",
        payload
    );
    const notificationTitle = payload.notification.title;
    const notificationOptions = {
        body: payload.notification.body,
        icon: "/firebase-logo.png",
    };
    self.registration.showNotification(notificationTitle, notificationOptions);
});
