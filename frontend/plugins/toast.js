import Toast, { useToast } from 'vue-toastification';
import 'vue-toastification/dist/index.css';

export default defineNuxtPlugin(nuxtApp => {
    // Kiểm tra screen size để tùy chỉnh config
    const isMobile = () => {
        if (process.client) {
            return window.innerWidth <= 768;
        }
        return false;
    };

    // Config cơ bản
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

    // Config responsive cho mobile
    const getResponsiveConfig = () => {
        if (isMobile()) {
            return {
                ...baseConfig,
                position: 'top-center', // Thay đổi vị trí cho mobile
                maxToasts: 3, // Giảm số toast hiển thị
                timeout: 4000, // Giảm thời gian hiển thị
                toastClassName: 'mobile-toast', // Class tùy chỉnh cho mobile
                bodyClassName: 'mobile-toast-body',
                containerClassName: 'mobile-toast-container'
            };
        }
        return {
            ...baseConfig,
            position: 'top-right'
        };
    };

    nuxtApp.vueApp.use(Toast, getResponsiveConfig());

    // Thêm CSS responsive
    if (process.client) {
        let style = document.querySelector('style[data-toast-plugin]');
        if (!style) {
            style = document.createElement('style');
            style.setAttribute('data-toast-plugin', 'true');
            style.textContent = `
            /* CSS Responsive cho Toast trên mobile */
            @media screen and (max-width: 768px) {
                .Vue-Toastification__container.top-center {
                    width: 100% !important;
                    max-width: calc(100vw - 20px) !important;
                    left: 10px !important;
                    right: 10px !important;
                    top: 10px !important;
                    z-index: 1000000 !important; /* Cao hơn mmenu và Magnific Popup */
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

                .Vue-Toastification__toast--success {
                    background-color: #10b981 !important;
                }

                .Vue-Toastification__toast--error {
                    background-color: #ef4444 !important;
                }

                .Vue-Toastification__toast--warning {
                    background-color: #f59e0b !important;
                }

                .Vue-Toastification__toast--info {
                    background-color: #3b82f6 !important;
                }

                /* Ẩn các container toast không sử dụng */
                .Vue-Toastification__container:not(.top-center) {
                    display: none !important;
                }
            }

            /* Tablet responsive */
            @media screen and (min-width: 769px) and (max-width: 1024px) {
                .Vue-Toastification__container.top-right {
                    width: 400px !important;
                    max-width: calc(100vw - 40px) !important;
                    z-index: 1000000 !important;
                }

                .Vue-Toastification__toast {
                    font-size: 15px !important;
                    padding: 14px 18px !important;
                }

                /* Ẩn các container toast không sử dụng */
                .Vue-Toastification__container:not(.top-right) {
                    display: none !important;
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

            /* Đảm bảo toast luôn ở trên */
            .Vue-Toastification__container {
                z-index: 1000000 !important;
            }
        `;
            document.head.appendChild(style);
        }
    }

    return {
        provide: {
            toast: useToast()
        }
    };
});
