import { initializeApp } from "firebase/app";
import { getMessaging, getToken, onMessage } from "firebase/messaging";
import { useToast } from "vue-toastification";

export default defineNuxtPlugin((nuxtApp) => {
  if (process.client) {
    console.log("🔥 Firebase Messaging plugin running on client");

    const firebaseConfig = {
      apiKey: "AIzaSyAnEYDqg-BwdYKJLoz1bDG1x62JnRsVVB0",
      authDomain: "tro-viet.firebaseapp.com",
      projectId: "tro-viet",
      storageBucket: "tro-viet.firebasestorage.app",
      messagingSenderId: "1000506063285",
      appId: "1:1000506063285:web:47e80b8489d09c8ce8c1fc",
      measurementId: "G-LRB092W6Y5",
    };

    const app = initializeApp(firebaseConfig);
    const messaging = getMessaging(app);
    const toast = useToast();

    const getFcmToken = async () => {
      try {
        if (!("serviceWorker" in navigator) || !("PushManager" in window)) {
          console.warn("❌ Trình duyệt không hỗ trợ push notification");
          return null;
        }

        const permission = await Notification.requestPermission();
        if (permission !== "granted") {
          console.warn("❌ Người dùng từ chối cấp quyền thông báo");
          return null;
        }

        await navigator.serviceWorker.ready;

        const token = await getToken(messaging, {
          vapidKey:
            "BIwo8BokWVVEkQusRhenQkeVXDESe5Hfev8clWdC4BAcN1Onj6Ic2W6WOyFBrQKMMHIHQI2lloDVsn2F6lxOyxo",
        });

        if (token) {
          console.log("✅ FCM token:", token);
          return token;
        } else {
          console.warn("⚠️ Không lấy được FCM token");
          return null;
        }
      } catch (err) {
        console.error("❌ Lỗi khi lấy FCM token:", err);
        return null;
      }
    };

    if ("serviceWorker" in navigator) {
      navigator.serviceWorker
        .register("/firebase-messaging-sw.js")
        .then((registration) => {
          console.log("✅ Service Worker đã được đăng ký:", registration);
        })
        .catch((err) => {
          console.error("❌ Lỗi khi đăng ký Service Worker:", err);
        });
    }

    const saveFcmToken = async () => {
      const token = await getFcmToken();
      if (token) {
        const oldToken = localStorage.getItem("fcm_token");
        if (token !== oldToken) {
          try {
            await fetch("http://127.0.0.1:8000/save-fcm-token", {
              method: "POST",
              headers: {
                "Content-Type": "application/json",
                // Authorization: 'Bearer ...' nếu cần auth
              },
              body: JSON.stringify({ fcm_token: token }),
            });

            localStorage.setItem("fcm_token", token);
            console.log(
              "📦 Token mới đã lưu vào localStorage và gửi về backend"
            );
          } catch (error) {
            console.error("❌ Lỗi khi gửi token về backend:", error);
          }
        } else {
          console.log("🔁 Token giống token cũ, không cần gửi lại");
        }
      }
    };

    // Lắng nghe thông báo khi tab đang mở
    onMessage(messaging, (payload) => {
      console.log("📩 Nhận được thông báo:", payload);
      if (payload.notification) {
        toast.success(
          `${payload.notification.title}: ${payload.notification.body}`
        );
      }
    });

    return {
      provide: {
        firebaseMessaging: messaging,
        getFcmToken,
        saveFcmToken,
      },
    };
  }

  // Nếu đang SSR thì return rỗng
  return {
    provide: {
      firebaseMessaging: null,
      getFcmToken: () => null,
      saveFcmToken: () => {},
    },
  };
});
