import Toast, { useToast } from 'vue-toastification';
import 'vue-toastification/dist/index.css';

// Plugin tích hợp vue-toastification vào ứng dụng
export default defineNuxtPlugin(nuxtApp => {
    // Hàm kiểm tra thiết bị di động
    const isMobile = () => {
        if (process.client) {
            return window.innerWidth <= 768;
        }
        return false;
    };

    // Cấu hình cơ bản cho toast
    const baseConfig = {
        transition: 'Vue-Toastification__bounce',
        maxToasts: 20,
        newestOnTop: true,
        timeout: 5000,
        closeOnClick: true,
        pauseOnFocusLoss: true,
        pauseOnHover: true,
        draggable: true,
        draggablePercent: 0.6,
        showCloseButtonOnHover: false,
        hideProgressBar: false,
        closeButton: 'button',
        icon: true,
        rtl: false
    };

    // Hàm lấy cấu hình responsive cho toast
    const getResponsiveConfig = () => {
        if (isMobile()) {
            return {
                ...baseConfig,
                position: 'top-center', // Vị trí toast trên mobile
                maxToasts: 3, // Giới hạn số toast hiển thị
                timeout: 4000, // Thời gian hiển thị ngắn hơn
                toastClassName: 'mobile-toast', // Class tùy chỉnh cho mobile
                bodyClassName: 'mobile-toast-body',
                containerClassName: 'mobile-toast-container'
            };
        }
        return {
            ...baseConfig,
            position: 'top-right' // Vị trí toast trên desktop
        };
    };

    // Tích hợp vue-toastification với cấu hình
    nuxtApp.vueApp.use(Toast, getResponsiveConfig());

    // Thêm CSS responsive cho toast
    if (process.client) {
        const style = document.createElement('style');
        style.textContent = `
            /* CSS Responsive cho Toast trên mobile */
            @media screen and (max-width: 768px) {
                .Vue-Toastification__container {
                    width: 100% !important;
                    max-width: calc(100vw - 20px) !important;
                    left: 10px !important;
                    right: 10px !important;
                    top: 10px !important;
                }

                .Vue-Toastification__toast {
                    min-height: 48px !important;
                    margin-bottom: 8px !important;
                    border-radius: 8px !important;
                    font-size: 14px !important;
                    padding: 12px 16px !important;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
                }

                .Vue-Toastification__toast-body {
                    margin: 0 !important;
                    padding: 0 !important;
                    line-height: 1.4 !important;
                }

                .Vue-Toastification__close-button {
                    width: 20px !important;
                    height: 20px !important;
                    font-size: 16px !important;
                }

                .Vue-Toastification__icon {
                    width: 20px !important;
                    height: 20px !important;
                    margin-right: 12px !important;
                }

                .Vue-Toastification__progress-bar {
                    height: 3px !important;
                }
            }

            /* Tablet responsive */
            @media screen and (min-width: 769px) and (max-width: 1024px) {
                .Vue-Toastification__container {
                    width: 400px !important;
                    max-width: calc(100vw - 40px) !important;
                }

                .Vue-Toastification__toast {
                    font-size: 15px !important;
                }
            }

            /* Animation tối ưu cho mobile */
            @media screen and (max-width: 768px) {
                .Vue-Toastification__bounce-enter-active {
                    animation-duration: 0.3s !important;
                }

                .Vue-Toastification__bounce-leave-active {
                    animation-duration: 0.2s !important;
                }
            }
        `;
        document.head.appendChild(style); // Thêm CSS vào head

        // Cập nhật cấu hình khi thay đổi kích thước màn hình
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                console.log('Screen resized, current mobile status:', isMobile());
            }, 250);
        });
    }

    // Cung cấp toast cho ứng dụng
    return {
        provide: {
            toast: useToast()
        }
    };
});
