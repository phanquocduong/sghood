import { ref } from 'vue';
import { defineStore } from 'pinia';
import { useAppToast } from '~/composables/useToast';
import { useFirebaseAuth } from '~/composables/useFirebaseAuth';
import { useApi } from '~/composables/useApi';

// Định nghĩa store xác thực
export const useAuthStore = defineStore('auth', () => {
    const toast = useAppToast(); // Lấy composable hiển thị thông báo
    const { $api } = useNuxtApp(); // Lấy đối tượng API từ Nuxt
    const { handleBackendError } = useApi(); // Lấy hàm xử lý lỗi backend
    const { sendOTP, verifyOTP, getIdToken, signOut } = useFirebaseAuth();
    const config = useRuntimeConfig(); // Lấy cấu hình runtime
    const nuxtApp = useNuxtApp(); // Lấy instance Nuxt

    // Khởi tạo các biến trạng thái
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

    // Hàm đóng popup
    const closePopup = () => {
        if (typeof window !== 'undefined' && window.$.magnificPopup) {
            window.$.magnificPopup.close();
        }
    };

    // Hàm reset form
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

    // Hàm lấy CSRF cookie
    const getCsrfCookie = async () => {
        if (typeof window === 'undefined') return;

        try {
            await $fetch(`${config.public.baseUrl}/sanctum/csrf-cookie`, {
                method: 'GET',
                credentials: 'include'
            });
            // Chờ cookie được thiết lập
            await new Promise(resolve => setTimeout(resolve, 100));
        } catch (error) {
            console.error('CSRF cookie error:', error);
            throw error;
        }
    };

    // Hàm đăng nhập người dùng
    const loginUser = async () => {
        try {
            loading.value = true;
            // Xóa trạng thái hiện tại
            user.value = null;
            isAuthenticated.value = false;

            await getCsrfCookie(); // Lấy CSRF cookie

            // Gửi yêu cầu đăng nhập
            const response = await $api('/login', {
                method: 'POST',
                body: { username: username.value, password: password.value },
                headers: {
                    'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
                }
            });

            // Lưu thông tin người dùng
            user.value = response.data;
            isAuthenticated.value = true;

            // Lưu FCM token nếu có
            try {
                await saveFcmToken();
            } catch (fcmError) {
                console.warn('FCM token save failed, but login successful:', fcmError);
                toast.warning('Đăng nhập thành công nhưng không thể kích hoạt thông báo');
            }

            toast.success(response.message); // Hiển thị thông báo thành công
            resetForm(); // Reset form
            closePopup(); // Đóng popup

            // Chờ trạng thái cập nhật
            await new Promise(resolve => setTimeout(resolve, 100));
        } catch (error) {
            // Xử lý lỗi đăng nhập
            user.value = null;
            isAuthenticated.value = false;
            handleBackendError(error, toast);
        } finally {
            loading.value = false;
        }
    };

    // Hàm đăng ký người dùng
    const registerUser = async () => {
        // Kiểm tra mật khẩu xác nhận
        if (password.value !== confirmPassword.value) {
            toast.error('Mật khẩu xác nhận không khớp!');
            return;
        }

        try {
            loading.value = true;
            await getCsrfCookie(); // Lấy CSRF cookie

            // Gửi yêu cầu đăng ký
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

            resetForm(); // Reset form
            closePopup(); // Đóng popup
            await signOut(); // Đăng xuất Firebase
            toast.success(response.message); // Hiển thị thông báo thành công
        } catch (error) {
            handleBackendError(error, toast); // Xử lý lỗi
        } finally {
            loading.value = false;
        }
    };

    // Hàm đăng xuất
    const logout = async () => {
        try {
            loading.value = true;

            // Gọi API đăng xuất backend
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

            // Xóa trạng thái và cookie
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

            // Xóa localStorage nếu sử dụng pinia-plugin-persistedstate
            if (localStorage.getItem('auth')) {
                localStorage.removeItem('auth');
            }

            // Chuyển hướng về trang chủ
            window.location.href = '/';
        } catch (error) {
            console.error('Logout error:', error);
            // Xóa trạng thái và cookie ngay cả khi có lỗi
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

    // Hàm đặt lại mật khẩu
    const resetPassword = async () => {
        // Kiểm tra mật khẩu xác nhận
        if (password.value !== confirmPassword.value) {
            toast.error('Mật khẩu xác nhận không khớp!');
            return;
        }

        try {
            loading.value = true;
            await getCsrfCookie(); // Lấy CSRF cookie
            const idToken = await getIdToken(); // Lấy ID token từ Firebase
            if (!idToken) {
                toast.error('Không thể lấy token xác thực!');
                return;
            }

            // Gửi yêu cầu đặt lại mật khẩu
            const response = await $api('/reset-password', {
                method: 'POST',
                body: {
                    _method: 'PATCH',
                    id_token: idToken,
                    phone: phone.value,
                    password: password.value,
                    password_confirmation: confirmPassword.value
                },
                headers: {
                    'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
                }
            });
            resetForm(); // Reset form
            closePopup(); // Đóng popup
            toast.success(response.message); // Hiển thị thông báo thành công
        } catch (error) {
            console.error(error);
            handleBackendError(error, toast); // Xử lý lỗi
        } finally {
            loading.value = false;
        }
    };

    // Hàm lưu FCM token
    const saveFcmToken = async () => {
        if (process.client && user.value) {
            try {
                const token = await nuxtApp.$getFcmToken(); // Lấy FCM token
                if (token) {
                    const response = await $api('/save-fcm-token', {
                        method: 'POST',
                        body: { fcm_token: token },
                        headers: {
                            'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
                        }
                    });
                    return true;
                } else {
                    console.error('No FCM token available to save');
                    return false;
                }
            } catch (error) {
                console.error('Error saving FCM token:', error);
                return false;
            }
        }
        return false;
    };

    // Hàm kiểm tra trạng thái xác thực
    const checkAuth = async () => {
        if (typeof window === 'undefined') return false;

        try {
            await fetchUser(); // Lấy thông tin người dùng
            return isAuthenticated.value;
        } catch (error) {
            console.error('Auth check failed:', error);
            user.value = null;
            isAuthenticated.value = false;
            return false;
        }
    };

    // Hàm lấy thông tin người dùng
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
            user.value = null;
            isAuthenticated.value = false;
        }
    };

    // Trả về trạng thái và các hàm xử lý
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
