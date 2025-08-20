import { useApi } from '~/composables/useApi';

export function useTenant({
    formData,
    identityImages,
    extractLoading,
    toast,
    dropzoneInstance,
    contractId,
    bypassExtract,
    extractErrorCount
}) {
    const { $api } = useNuxtApp();
    const { handleBackendError } = useApi();

    const handleIdentityUpload = async files => {
        if (bypassExtract.value) {
            identityImages.value = files;
            return;
        }

        const formDataToSend = new FormData();
        files.forEach(file => formDataToSend.append('identity_images[]', file));

        extractLoading.value = true;
        try {
            const response = await $api('/extract-identity-images', {
                method: 'POST',
                body: formDataToSend,
                headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value }
            });
            formData.value.identity_document = response.data;
            identityImages.value = files;
            toast.success(response.message);
            extractErrorCount.value = 0;

            if (dropzoneInstance.value && formData.value.identity_document.has_valid) {
                dropzoneInstance.value.disable();
            }
        } catch (error) {
            if (error.response?.status === 422 && error.response?._data?.error) {
                const errorMessage = error.response._data.error;
                if (
                    errorMessage.includes('Lỗi xử lý ảnh CCCD') ||
                    errorMessage.includes('Không thể xác định mặt trước hoặc mặt sau') ||
                    errorMessage.includes('Không tìm thấy văn bản trong ảnh CCCD')
                ) {
                    extractErrorCount.value += 1;
                }
            }

            handleBackendError(error, toast);
            formData.value.identity_document = {
                identity_number: '',
                full_name: '',
                year_of_birth: '',
                date_of_issue: '',
                place_of_issue: '',
                permanent_address: '',
                has_valid: false
            };
            identityImages.value = [];
            if (dropzoneInstance.value) {
                dropzoneInstance.value.removeAllFiles(true);
            }

            if (extractErrorCount.value >= 5) {
                bypassExtract.value = true;
                toast.warning('Quét CCCD thất bại 5 lần. Bạn có thể tải ảnh lên để admin xác nhận.');
            }
        } finally {
            extractLoading.value = false;
        }
    };

    const submitTenant = async () => {
        if (
            !formData.value.name ||
            !formData.value.phone ||
            !formData.value.relation_with_primary ||
            (!formData.value.identity_document.has_valid && identityImages.value.length !== 2)
        ) {
            toast.error('Vui lòng điền đầy đủ các trường bắt buộc và tải lên 2 ảnh CCCD.');
            return;
        }

        const formDataToSend = new FormData();
        formDataToSend.append('name', formData.value.name);
        formDataToSend.append('phone', formData.value.phone);
        if (formData.value.email) formDataToSend.append('email', formData.value.email);
        if (formData.value.gender) formDataToSend.append('gender', formData.value.gender);
        if (formData.value.birthdate) formDataToSend.append('birthdate', formData.value.birthdate);
        if (formData.value.address) formDataToSend.append('address', formData.value.address);
        formDataToSend.append('relation_with_primary', formData.value.relation_with_primary);
        identityImages.value.forEach(file => formDataToSend.append('identity_images[]', file));

        try {
            const response = await $api(`/contracts/${contractId}/tenants`, {
                method: 'POST',
                headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value },
                body: formDataToSend
            });
            toast.success('Thêm người ở cùng thành công');
            return response.data;
        } catch (error) {
            handleBackendError(error, toast);
            throw error;
        }
    };

    return {
        handleIdentityUpload,
        submitTenant
    };
}
