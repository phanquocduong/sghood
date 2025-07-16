export function useApi() {
    const handleBackendError = (error, toast) => {
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

    return { handleBackendError };
}
