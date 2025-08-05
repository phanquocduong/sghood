export const useAppToast = () => {
    const { $toast } = useNuxtApp();
    return {
        success: (message, options = {}) => $toast.success(message, options),
        error: (message, options = {}) => $toast.error(message, options),
        info: (message, options = {}) => $toast.info(message, options),
        warning: (message, options = {}) => $toast.warning(message, options)
    };
};
