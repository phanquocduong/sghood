import { RecaptchaVerifier, signInWithPhoneNumber } from 'firebase/auth';
import { useAppToast } from '~/composables/useToast';

// Composable xử lý xác thực Firebase
export const useFirebaseAuth = () => {
    const { $firebaseAuth } = useNuxtApp(); // Lấy đối tượng Firebase Auth từ Nuxt plugin
    const toast = useAppToast(); // Lấy composable hiển thị thông báo
    let confirmationResult = null; // Biến lưu trữ kết quả xác thực OTP

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
            case 'auth/quota-exceeded':
                toast.error('Đã vượt quá giới hạn gửi OTP. Vui lòng thử lại sau.');
                break;
            case 'auth/argument-error':
                toast.error('Lỗi cấu hình Firebase. Vui lòng thử lại sau.');
                break;
            default:
                toast.error('Đã có lỗi xảy ra.');
        }
    };

    // Hàm gửi mã OTP qua Firebase
    const sendOTP = async phone => {
        try {
            // Kiểm tra sự tồn tại của container reCAPTCHA
            if (typeof window !== 'undefined' && !document.getElementById('recaptcha-container')) {
                toast.error('Không tìm thấy container reCAPTCHA.');
                return false;
            }

            // Xóa reCAPTCHA cũ nếu có
            if (typeof window !== 'undefined' && window.recaptchaVerifier) {
                window.recaptchaVerifier.clear();
            }

            // Khởi tạo reCAPTCHA mới
            if (typeof window !== 'undefined') {
                window.recaptchaVerifier = new RecaptchaVerifier($firebaseAuth, 'recaptcha-container', {
                    size: 'invisible', // reCAPTCHA vô hình
                    callback: () => {}
                });
            }

            // Gửi OTP qua Firebase
            confirmationResult = await signInWithPhoneNumber($firebaseAuth, phone, window.recaptchaVerifier);
            toast.info('Mã OTP đã được gửi!');
            return true;
        } catch (error) {
            console.error(error);
            handleFirebaseError(error);
            return false;
        }
    };

    // Hàm xác minh mã OTP
    const verifyOTP = async otp => {
        if (!confirmationResult) {
            toast.error('Chưa gửi mã OTP. Vui lòng yêu cầu mã OTP trước!');
            return false;
        }

        try {
            await confirmationResult.confirm(otp); // Xác minh OTP
            toast.success('Xác minh OTP thành công!');
            return true;
        } catch (error) {
            handleFirebaseError(error);
            return false;
        }
    };

    // Hàm lấy ID token từ Firebase
    const getIdToken = async () => {
        try {
            return await $firebaseAuth.currentUser?.getIdToken();
        } catch (error) {
            handleFirebaseError(error);
            return null;
        }
    };

    // Hàm đăng xuất Firebase
    const signOut = async () => {
        try {
            await $firebaseAuth.signOut();
        } catch (error) {
            handleFirebaseError(error);
        }
    };

    return { sendOTP, verifyOTP, getIdToken, signOut }; // Trả về các hàm xử lý xác thực
};
