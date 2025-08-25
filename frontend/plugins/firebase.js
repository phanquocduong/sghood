import { initializeApp, getApps, getApp } from 'firebase/app';
import { getAuth } from 'firebase/auth';
import { getFirestore } from 'firebase/firestore';
import { getStorage } from 'firebase/storage';

// Plugin tích hợp Firebase vào ứng dụng
export default defineNuxtPlugin(() => {
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
    const auth = getAuth(app); // Lấy Firebase Auth
    const db = getFirestore(app); // Lấy Firestore
    const storage = getStorage(app); // Lấy Firebase Storage

    // Cung cấp các dịch vụ Firebase
    return {
        provide: {
            firebaseApp: app,
            firebaseAuth: auth,
            firebaseDb: db,
            firebaseStorage: storage
        }
    };
});
