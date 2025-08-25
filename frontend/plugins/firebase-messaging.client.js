import { defineNuxtPlugin } from '#app';
import { initializeApp, getApps, getApp } from 'firebase/app';
import { getMessaging, getToken, onMessage } from 'firebase/messaging';
import { useAppToast } from '~/composables/useToast';
import { useRouter } from 'vue-router';

// Plugin tích hợp Firebase Messaging
export default defineNuxtPlugin(nuxtApp => {
    // Chỉ chạy ở client-side
    if (process.client) {
        // Cấu hình Firebase
        const firebaseConfig = {
            apiKey: 'AIzaSyAnEYDqg-BwdYKJLoz1bDG1x62JnRsVVB0',
            authDomain: 'tro-viet.firebaseapp.com',
            projectId: 'tro-viet',
            storageBucket: 'tro-viet.firebasestorage.app',
            messagingSenderId: '1000506063285',
            appId: '1:1000506063285:web:47e80b8489d09c8ce8c1fc',
            measurementId: 'G-LRB092W6Y5'
        };

        // Khởi tạo Firebase app
        const app = getApps().length ? getApp() : initializeApp(firebaseConfig);
        const messaging = getMessaging(app); // Lấy Firebase Messaging
        const toast = useAppToast(); // Lấy composable hiển thị thông báo
        const router = useRouter(); // Lấy router để điều hướng

        // Hàm lấy FCM token
        const getFcmToken = async () => {
            try {
                // Kiểm tra hỗ trợ Service Worker và Push API
                if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                    console.error('Push messaging is not supported');
                    return null;
                }

                // Yêu cầu quyền thông báo
                const permission = await Notification.requestPermission();
                if (permission !== 'granted') {
                    console.error('Notification permission denied');
                    return null;
                }

                await navigator.serviceWorker.ready; // Chờ Service Worker sẵn sàng

                // Lấy FCM token
                const token = await getToken(messaging, {
                    vapidKey: 'BIwo8BokWVVEkQusRhenQkeVXDESe5Hfev8clWdC4BAcN1Onj6Ic2W6WOyFBrQKMMHIHQI2lloDVsn2F6lxOyxo'
                });

                if (token) {
                    return token;
                } else {
                    console.error('No registration token available.');
                    return null;
                }
            } catch (error) {
                console.error('Error getting FCM token:', error);
                if (error.code === 'messaging/token-subscribe-failed') {
                    console.error('Token subscribe failed. Check VAPID key and Firebase config.');
                }
                return null;
            }
        };

        // Xử lý thông báo khi ứng dụng đang chạy
        onMessage(messaging, payload => {
            if (payload.notification) {
                toast.success(`${payload.notification.title}: ${payload.notification.body}`); // Hiển thị thông báo
            }

            // Điều hướng nếu có link trong thông báo
            if (payload.data?.link) {
                router.push(payload.data.link).catch(err => {
                    console.error('Navigation failed:', err);
                    window.open(payload.data.link, '_blank'); // Mở trong tab mới nếu điều hướng thất bại
                });
            }
        });

        // Cung cấp Firebase Messaging và hàm lấy FCM token
        return {
            provide: {
                firebaseMessaging: messaging,
                getFcmToken
            }
        };
    }

    // Trả về giá trị mặc định cho server-side
    return {
        provide: {
            firebaseMessaging: null,
            getFcmToken: () => null
        }
    };
});
