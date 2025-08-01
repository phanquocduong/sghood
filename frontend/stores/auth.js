import { ref } from 'vue';
import { defineStore } from 'pinia';
// import { useToast } from 'vue-toastification';
import { useAppToast } from '~/composables/useToast';
import { useRouter } from 'vue-router';
import { useFirebaseAuth } from '~/composables/useFirebaseAuth';

export const useAuthStore = defineStore('auth', () => {
    const toast = useAppToast();
    const router = useRouter();
    const { $api } = useNuxtApp();
    const { sendOTP, verifyOTP, getIdToken, signOut } = useFirebaseAuth();
    const config = useRuntimeConfig();
    const nuxtApp = useNuxtApp();

    // State
    const username = ref('');
    const password = ref('');
    const confirmPassword = ref('');
    const role = ref('');
    const phone = ref('');
    const otp = ref('');
    const otpSent = ref(false);
    const showRegisterFields = ref(false);
    const showResetFields = ref(false);
    const name = ref('');
    const email = ref('');
    const loading = ref(false);
    const user = ref(null);
    const isAuthenticated = ref(false);

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

        try {
            await $fetch(`${config.public.baseUrl}/sanctum/csrf-cookie`, {
                method: 'GET',
                credentials: 'include'
            });

            // Wait a bit for cookie to be set
            await new Promise(resolve => setTimeout(resolve, 100));
        } catch (error) {
            console.error('CSRF cookie error:', error);
            throw error;
        }
    };

    const loginUser = async () => {
        try {
            loading.value = true;

            // Clear any existing state first
            user.value = null;
            isAuthenticated.value = false;

            await getCsrfCookie();

            const response = await $api('/login', {
                method: 'POST',
                body: { username: username.value, password: password.value },
                headers: {
                    'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
                }
            });

            // Set user data
            user.value = response.data;
            isAuthenticated.value = true;

            // Try to save FCM token, but don't fail login if it fails
            try {
                await saveFcmToken();
            } catch (fcmError) {
                console.warn('FCM token save failed, but login successful:', fcmError);
                toast.warning('Đăng nhập thành công nhưng không thể kích hoạt thông báo');
            }

            toast.success(response.message);
            resetForm();
            closePopup();

            // Small delay to ensure state is updated
            await new Promise(resolve => setTimeout(resolve, 100));
        } catch (error) {
            // Reset auth state on login failure
            user.value = null;
            isAuthenticated.value = false;
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

            const response = await $api('/register', {
                method: 'POST',
                body: {
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

            // Gọi API logout backend
            try {
                await getCsrfCookie();
                const response = await $api('/logout', {
                    method: 'POST',
                    headers: {
                        'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
                    }
                });
                toast.success(response.message);
            } catch (error) {
                console.warn('Backend logout failed:', error);
            }

            // Xóa tất cả state
            user.value = null;
            isAuthenticated.value = false;
            resetForm();

            // Xóa tất cả cookie liên quan
            if (process.client) {
                const cookies = ['sanctum_token', 'XSRF-TOKEN', 'laravel_session'];
                cookies.forEach(cookieName => {
                    const cookie = useCookie(cookieName);
                    cookie.value = null;
                });
            }

            // Xóa localStorage nếu sử dụng pinia-plugin-persistedstate
            if (localStorage.getItem('auth')) {
                localStorage.removeItem('auth');
            }

            // Chuyển hướng để đảm bảo trạng thái sạch
            window.location.href = '/';
        } catch (error) {
            console.error('Logout error:', error);
            // Xóa state và cookie ngay cả khi có lỗi
            user.value = null;
            isAuthenticated.value = false;
            resetForm();

            if (process.client) {
                const cookies = ['sanctum_token', 'XSRF-TOKEN', 'laravel_session'];
                cookies.forEach(cookieName => {
                    const cookie = useCookie(cookieName);
                    cookie.value = null;
                });
            }

            toast.error('Đã đăng xuất (có thể cần tải lại trang)');
            window.location.href = '/';
        } finally {
            loading.value = false;
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
                // Don't throw error, just return false
                return false;
            }
        }
        return false;
    };

    const checkAuth = async () => {
        if (typeof window === 'undefined') return false;

        try {
            await fetchUser();
            return isAuthenticated.value;
        } catch (error) {
            console.error('Auth check failed:', error);
            user.value = null;
            isAuthenticated.value = false;
            return false;
        }
    };

    const fetchUser = async () => {
        if (typeof window === 'undefined') return;

        try {
            const response = await $api('/user', {
                method: 'GET',
                headers: {
                    'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
                }
            });
            user.value = response.data;
            isAuthenticated.value = !!response.data;
        } catch (error) {
            console.error('Fetch user failed:', error);
            user.value = null;
            isAuthenticated.value = false;
        }
    };

    return {
        username,
        role,
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
        isAuthenticated,
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
        resetPassword,
        checkAuth
    };
});
