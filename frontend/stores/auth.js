import { ref } from 'vue';
import { defineStore } from 'pinia';
import { useToast } from 'vue-toastification';
import { useRouter } from 'vue-router';
import { useFirebaseAuth } from '~/composables/useFirebaseAuth';

export const useAuthStore = defineStore('auth', () => {
    const toast = useToast();
    const router = useRouter();
    const { $api } = useNuxtApp();
    const { sendOTP, verifyOTP, getIdToken, signOut } = useFirebaseAuth();
    const config = useRuntimeConfig();
    const nuxtApp = useNuxtApp();

    // State
    const username = ref('');
    const password = ref('');
    const confirmPassword = ref('');
    const phone = ref('');
    const otp = ref('');
    const otpSent = ref(false);
    const showRegisterFields = ref(false);
    const showResetFields = ref(false);
    const name = ref('');
    const email = ref('');
    const loading = ref(false);
    const user = ref(null);

    // Methods
    const handleBackendError = error => {
        const data = error.response?._data;
        if (data?.error) {
            toast.error(data.error);
            return;
        }
        if (data?.errors) {
            Object.values(data.errors).forEach(err => toast.error(err[0]));
            return;
        }
        toast.error('Đã có lỗi xảy ra. Vui lòng thử lại.');
    };

    const closePopup = () => {
        if (typeof window !== 'undefined' && window.$.magnificPopup) {
            window.$.magnificPopup.close();
        }
    };

    const resetForm = () => {
        username.value = '';
        password.value = '';
        confirmPassword.value = '';
        phone.value = '';
        otp.value = '';
        name.value = '';
        email.value = '';
        otpSent.value = false;
        showRegisterFields.value = false;
        showResetFields.value = false;
    };

    const getCsrfCookie = async () => {
        if (typeof window === 'undefined') return;
        await $fetch(`${config.public.baseUrl}/sanctum/csrf-cookie`, { method: 'GET' });
    };

    const loginUser = async () => {
        try {
            loading.value = true;
            await getCsrfCookie();
            const response = await $api('/login', {
                method: 'POST',
                body: { username: username.value, password: password.value },
                headers: {
                    'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
                }
            });

            user.value = response.data;

            // Delay một chút để đảm bảo user state đã được set
            setTimeout(async () => {
                const tokenSaved = await saveFcmToken();
                if (tokenSaved) {
                    console.log('FCM token saved after login');
                }
            }, 1000);

            resetForm();
            closePopup();
            toast.success(response.message);
        } catch (error) {
            handleBackendError(error);
        } finally {
            loading.value = false;
        }
    };

    const registerUser = async () => {
        if (password.value !== confirmPassword.value) {
            toast.error('Mật khẩu xác nhận không khớp!');
            return;
        }

        try {
            loading.value = true;
            await getCsrfCookie();
            const idToken = await getIdToken();
            if (!idToken) {
                toast.error('Không thể lấy token xác thực!');
                return;
            }

            const response = await $api('/register', {
                method: 'POST',
                body: {
                    id_token: idToken,
                    phone: phone.value,
                    name: name.value,
                    email: email.value,
                    password: password.value,
                    password_confirmation: confirmPassword.value
                },
                headers: {
                    'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
                }
            });

            await saveFcmToken(); // Lưu FCM token sau khi đăng ký
            resetForm();
            closePopup();
            await signOut();
            toast.success(response.message);
        } catch (error) {
            handleBackendError(error);
        } finally {
            loading.value = false;
        }
    };

    const logout = async () => {
        try {
            loading.value = true;
            const response = await $api('/logout', {
                method: 'POST',
                headers: {
                    'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
                }
            });
            user.value = null;
            router.push('/');
            toast.success(response.message);
            resetForm();
        } catch (error) {
            toast.error('Lỗi đăng xuất. Vui lòng thử lại.');
        } finally {
            loading.value = false;
        }
    };

    const fetchUser = async () => {
        if (typeof window === 'undefined') return;

        try {
            const response = await $api('/user', { method: 'GET' });
            user.value = response.data;
            await saveFcmToken();
        } catch (error) {
            console.error('Failed to fetch user:', error);
            user.value = null;
        }
    };

    const resetPassword = async () => {
        if (password.value !== confirmPassword.value) {
            toast.error('Mật khẩu xác nhận không khớp!');
            return;
        }

        try {
            loading.value = true;
            await getCsrfCookie();
            const idToken = await getIdToken();
            if (!idToken) {
                toast.error('Không thể lấy token xác thực!');
                return;
            }

            const response = await $api('/reset-password', {
                method: 'POST',
                body: {
                    id_token: idToken,
                    phone: phone.value,
                    password: password.value,
                    password_confirmation: confirmPassword.value
                },
                headers: {
                    'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
                }
            });
            resetForm();
            closePopup();
            toast.success(response.message);
        } catch (error) {
            console.log(error);
            handleBackendError(error);
        } finally {
            loading.value = false;
        }
    };

    const saveFcmToken = async () => {
        if (process.client && user.value) {
            try {
                const token = await nuxtApp.$getFcmToken();
                if (token) {
                    console.log('Saving FCM token for user:', user.value.id);

                    const response = await $api('/save-fcm-token', {
                        method: 'POST',
                        body: { fcm_token: token },
                        headers: {
                            'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
                        }
                    });

                    console.log('FCM token saved successfully');
                    return true;
                } else {
                    console.log('No FCM token available to save');
                    return false;
                }
            } catch (error) {
                console.error('Error saving FCM token:', error);
                return false;
            }
        }
        return false;
    };

    return {
        username,
        password,
        confirmPassword,
        phone,
        otp,
        otpSent,
        showRegisterFields,
        showResetFields,
        name,
        email,
        loading,
        user,
        sendOTP: async () => {
            loading.value = true;
            const success = await sendOTP(phone.value);
            if (success) {
                otpSent.value = true;
            }
            loading.value = false;
        },
        verifyOTP: async () => {
            loading.value = true;
            const success = await verifyOTP(otp.value);
            if (success) {
                showRegisterFields.value = true;
                showResetFields.value = true;
            }
            loading.value = false;
        },
        loginUser,
        registerUser,
        logout,
        fetchUser,
        resetPassword
    };
});
