import { defineNuxtPlugin } from '#app';
import { initializeApp, getApps, getApp } from 'firebase/app';
import { getMessaging, getToken, onMessage } from 'firebase/messaging';
import { useToast } from 'vue-toastification';
import { useRouter } from 'vue-router';

export default defineNuxtPlugin(nuxtApp => {
    if (process.client) {
        const firebaseConfig = {
            apiKey: 'AIzaSyAnEYDqg-BwdYKJLoz1bDG1x62JnRsVVB0',
            authDomain: 'tro-viet.firebaseapp.com',
            projectId: 'tro-viet',
            storageBucket: 'tro-viet.firebasestorage.app',
            messagingSenderId: '1000506063285',
            appId: '1:1000506063285:web:47e80b8489d09c8ce8c1fc',
            measurementId: 'G-LRB092W6Y5'
        };

        const app = getApps().length ? getApp() : initializeApp(firebaseConfig)
        const messaging = getMessaging(app);
        const toast = useToast();
        const router = useRouter();

        const getFcmToken = async () => {
            try {
                if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                    console.error('Push messaging is not supported');
                    return null;
                }

                const permission = await Notification.requestPermission();
                if (permission !== 'granted') {
                    console.error('Notification permission denied');
                    return null;
                }

                await navigator.serviceWorker.ready;

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

        onMessage(messaging, payload => {
            console.log('Message received in foreground:', payload);

            if (payload.notification) {
                toast.success(`${payload.notification.title}: ${payload.notification.body}`);
            }

            if (payload.data?.link) {
                router.push(payload.data.link).catch(err => {
                    console.error('Navigation failed:', err);
                    window.open(payload.data.link, '_blank'); // Fallback to open in new tab
                });
            }
        });

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
