import { ref, nextTick } from 'vue';
import { signInWithPhoneNumber, RecaptchaVerifier, updateProfile, onAuthStateChanged } from 'firebase/auth';
import { useToast } from 'vue-toastification';
import { useRouter } from 'vue-router';

export function useAuth() {
    const toast = useToast();
    const router = useRouter();
    const { $firebaseAuth, $api } = useNuxtApp();

    // State
    const phone = ref('');
    const otp = ref('');
    const otpSent = ref(false);
    const loading = ref(false);
    const user = ref(null);
    const role = ref(null);
    let confirmationResult = null;

    // Hàm xử lý lỗi backend
    const handleBackendError = async error => {
        console.error('Backend error:', error.response?.status, error.response?._data);
        if (error.response?._data?.error) {
            toast.error(error.response._data.error);
            if (error.response.status === 403) {
                await $firebaseAuth.signOut();
                user.value = null;
                role.value = null;
                router.push('/');
            }
        } else if (error.response?._data?.errors) {
            Object.values(error.response._data.errors).forEach(err => toast.error(err[0]));
        } else {
            toast.error('Đã có lỗi xảy ra. Vui lòng thử lại.');
        }
    };

    // Hàm xử lý lỗi Firebase
    const handleFirebaseError = error => {
        switch (error.code) {
            case 'auth/too-many-requests':
                toast.error('Bạn đã gửi OTP quá nhiều lần. Vui lòng thử lại sau.');
                break;
            case 'auth/invalid-phone-number':
                toast.error('Số điện thoại không hợp lệ. Vui lòng kiểm tra lại.');
                break;
            case 'auth/invalid-verification-code':
                toast.error('Mã OTP không đúng. Vui lòng thử lại.');
                break;
            default:
                toast.error('Đã có lỗi xảy ra. Vui lòng thử lại sau.');
        }
    };

    // Hàm lấy role từ Custom Claims
    const fetchRole = async user => {
        try {
            const idTokenResult = await user.getIdTokenResult(true); // true để làm mới token
            role.value = idTokenResult.claims.role || null;
            console.log('Role from Custom Claims:', role.value);
        } catch (error) {
            console.error('Error fetching role:', error);
            toast.error('Không thể lấy vai trò người dùng.');
        }
    };

    // Promise để đợi trạng thái xác thực và vai trò
    const authReady = new Promise(resolve => {
        const unsubscribe = onAuthStateChanged($firebaseAuth, async currentUser => {
            user.value = currentUser ? { ...currentUser } : null;
            if (currentUser) {
                await fetchRole(currentUser); // Lấy role từ Custom Claims
            } else {
                role.value = null;
            }
            resolve();
            unsubscribe();
        });
    });

    const sendOTP = async () => {
        if (!phone.value) {
            toast.error('Vui lòng nhập số điện thoại!');
            return;
        }

        const phoneRegex = /^(?:\+84|0)(3|5|7|8|9)\d{8}$/;
        if (!phoneRegex.test(phone.value)) {
            toast.error('Số điện thoại không hợp lệ!');
            return;
        }

        try {
            loading.value = true;
            if (window.recaptchaVerifier) window.recaptchaVerifier.clear();

            if (typeof window !== 'undefined') {
                window.recaptchaVerifier = new RecaptchaVerifier($firebaseAuth, 'recaptcha-container', {
                    size: 'invisible',
                    callback: () => {}
                });
            }

            confirmationResult = await signInWithPhoneNumber($firebaseAuth, phone.value, window.recaptchaVerifier);
            otpSent.value = true;
            toast.info('Mã OTP đã được gửi!');
        } catch (error) {
            handleFirebaseError(error);
        } finally {
            loading.value = false;
        }
    };

    const verifyOTP = async () => {
        if (!otp.value) {
            toast.error('Vui lòng nhập mã OTP!');
            return;
        }

        try {
            loading.value = true;
            if (!confirmationResult) {
                toast.error('Chưa có mã OTP nào được gửi!');
                return;
            }

            const result = await confirmationResult.confirm(otp.value);
            const idToken = await result.user.getIdToken();

            const response = await $api('/firebase-auth', {
                method: 'POST',
                body: {
                    id_token: idToken,
                    type: 'admin'
                }
            });

            if (response.message === 'Đăng nhập thành công') {
                resetForm();
                toast.success(response.message);
                await fetchRole(result.user); // Lấy role từ Custom Claims sau khi đăng nhập
                if (response.data?.name) {
                    await updateProfile($firebaseAuth.currentUser, {
                        displayName: response.data.name
                    });
                }
                await nextTick(); // Đợi trạng thái cập nhật
                router.push('/dashboard');
            } else if (response.error) {
                toast.error(response.error);
            }
        } catch (error) {
            await handleBackendError(error);
        } finally {
            loading.value = false;
        }
    };

    const logout = async () => {
        try {
            loading.value = true;
            await $firebaseAuth.signOut();
            await $api('/logout', { method: 'POST' });
            user.value = null;
            role.value = null;
            router.push('/');
            toast.success('Đăng xuất thành công!');
            resetForm();
        } catch (error) {
            toast.error('Lỗi đăng xuất. Vui lòng thử lại.');
        } finally {
            loading.value = false;
        }
    };

    const resetForm = () => {
        phone.value = '';
        otp.value = '';
        otpSent.value = false;
    };

    return {
        phone,
        otp,
        otpSent,
        loading,
        user,
        role,
        authReady,
        sendOTP,
        verifyOTP,
        logout
    };
}
