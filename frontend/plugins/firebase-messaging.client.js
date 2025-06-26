//firebase-messaging.client.js
import { initializeApp } from 'firebase/app';
import { getMessaging, getToken, onMessage } from 'firebase/messaging';
import { useToast } from 'vue-toastification';

export default defineNuxtPlugin(nuxtApp => {
    if (process.client) {
        console.log('Firebase Messaging plugin running on client');

        const firebaseConfig = {
            apiKey: 'AIzaSyAnEYDqg-BwdYKJLoz1bDG1x62JnRsVVB0',
            authDomain: 'tro-viet.firebaseapp.com',
            projectId: 'tro-viet',
            storageBucket: 'tro-viet.firebasestorage.app',
            messagingSenderId: '1000506063285',
            appId: '1:1000506063285:web:47e80b8489d09c8ce8c1fc',
            measurementId: 'G-LRB092W6Y5'
        };

        const app = initializeApp(firebaseConfig);
        const messaging = getMessaging(app);

        const getFcmToken = async () => {
            try {
                // Kiểm tra support
                if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                    console.log('Push messaging is not supported');
                    return null;
                }

                // Request notification permission first
                const permission = await Notification.requestPermission();
                if (permission !== 'granted') {
                    console.log('Notification permission denied');
                    return null;
                }

                // Đợi service worker ready
                await navigator.serviceWorker.ready;

                const token = await getToken(messaging, {
                    // Copy chính xác VAPID key từ Firebase Console
                    vapidKey: 'BIwo8BokWVVEkQusRhenQkeVXDESe5Hfev8clWdC4BAcN1Onj6Ic2W6WOyFBrQKMMHIHQI2lloDVsn2F6lxOyxo'
                });

                if (token) {
                    console.log('FCM Token retrieved:', token);
                    return token;
                } else {
                    console.log('No registration token available.');
                    return null;
                }
            } catch (error) {
                console.error('Error getting FCM token:', error);

                // Xử lý lỗi cụ thể
                if (error.code === 'messaging/token-subscribe-failed') {
                    console.error('Token subscribe failed. Check VAPID key and Firebase config.');
                }

                return null;
            }
        };

        const toast = useToast();

        // Handle foreground messages
        onMessage(messaging, payload => {
            console.log('Message received in foreground:', payload);

            if (payload.notification) {
                toast.success(`${payload.notification.title}: ${payload.notification.body}`);
            }
        });

        // Auto get token khi có user (không cần get ngay lập tức)
        // Token sẽ được lấy khi user đăng nhập/đăng ký thông qua saveFcmToken()

        return {
            provide: {
                firebaseMessaging: messaging,
                getFcmToken
            }
        };
    }

    return {
        provide: {
            firebaseMessaging: null,
            getFcmToken: () => null
        }
    };
});
