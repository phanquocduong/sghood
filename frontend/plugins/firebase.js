// firebase.js
import { initializeApp } from 'firebase/app';
import { getAuth } from 'firebase/auth';
import {  getFirestore } from 'firebase/firestore';
export default defineNuxtPlugin(() => {
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
    const auth = getAuth(app);
    const db = getFirestore(app);
    return {
        provide: {

            firebaseApp: app,
            firebaseAuth: auth,
            firebaseDb :db

        }
    };
});