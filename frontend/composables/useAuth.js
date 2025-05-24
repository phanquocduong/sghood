import { ref } from 'vue';
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
    const showRegisterFields = ref(false);
    const name = ref('');
    const email = ref('');
    const birthdate = ref('');
    const loading = ref(false);
    const user = ref(null);
    let confirmationResult = null;

    // Theo dõi trạng thái đăng nhập
    onAuthStateChanged($firebaseAuth, currentUser => {
        user.value = currentUser ? { ...currentUser } : null;
    });

    // Methods
    const closePopup = () => {
        if (typeof window !== 'undefined' && window.$.magnificPopup) {
            window.$.magnificPopup.close();
        }
    };

    const handleBackendError = error => {
        if (error.response?._data?.error) {
            toast.error(error.response._data.error);
        } else if (error.response?._data?.errors) {
            const errors = error.response._data.errors;
            Object.values(errors).forEach(err => toast.error(err[0]));
        } else {
            toast.error('Đã có lỗi xảy ra. Vui lòng thử lại.');
        }
    };

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
            console.log(idToken);

            const response = await $api('/firebase-auth', {
                method: 'POST',
                body: { id_token: idToken }
            });

            if (response.message === 'Đăng nhập thành công') {
                resetForm();
                closePopup();
                toast.success(response.message);
                if (response.data?.name) {
                    await updateProfile($firebaseAuth.currentUser, {
                        displayName: response.data.name
                    });
                    user.value = { ...$firebaseAuth.currentUser };
                }
            } else if (response.error === 'Người dùng chưa tồn tại') {
                showRegisterFields.value = true;
            }
        } catch (error) {
            handleBackendError(error);
        } finally {
            loading.value = false;
        }
    };

    const registerUser = async () => {
        if (!name.value || !email.value || !birthdate.value) {
            toast.error('Vui lòng điền đầy đủ thông tin đăng ký!');
            return;
        }

        try {
            loading.value = true;
            const idToken = await $firebaseAuth.currentUser.getIdToken();

            const response = await $api('/firebase-register', {
                method: 'POST',
                body: {
                    id_token: idToken,
                    name: name.value,
                    email: email.value,
                    birthdate: birthdate.value
                }
            });

            user.value = { ...$firebaseAuth.currentUser };

            toast.success(response.message);
            if (response.data?.name) {
                await updateProfile($firebaseAuth.currentUser, {
                    displayName: response.data.name
                });
                user.value = { ...$firebaseAuth.currentUser };
            }

            closePopup();

            if (typeof window !== 'undefined') {
                window.location.reload();
                resetForm();
            }
        } catch (error) {
            handleBackendError(error);
        } finally {
            loading.value = false;
        }
    };

    const logout = async () => {
        try {
            loading.value = true;
            await $firebaseAuth.signOut();
            await $api('/logout', { method: 'POST' });
            router.push('/');
            toast.success('Đăng xuất thành công!');
            resetForm();
        } catch (error) {
            toast.error('Lỗi đăng xuất. Vui lòng thử lại.');
        }
    };

    const resetForm = () => {
        phone.value = '';
        otp.value = '';
        name.value = '';
        email.value = '';
        birthdate.value = '';
        otpSent.value = false;
        showRegisterFields.value = false;
    };

    return {
        phone,
        otp,
        otpSent,
        showRegisterFields,
        name,
        email,
        birthdate,
        loading,
        user,
        sendOTP,
        verifyOTP,
        registerUser,
        logout
    };
}
