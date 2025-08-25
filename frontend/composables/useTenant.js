import { useApi } from '~/composables/useApi';
import { useCookie } from '#app';

// Composable xử lý logic liên quan đến người ở cùng
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
    const { $api } = useNuxtApp(); // Lấy instance API từ Nuxt
    const { handleBackendError } = useApi(); // Sử dụng composable useApi để xử lý lỗi backend

    // Xử lý tải lên ảnh CCCD và trích xuất thông tin
    const handleIdentityUpload = async files => {
        if (bypassExtract.value) {
            identityImages.value = files; // Lưu ảnh nếu bypass quét
            return;
        }

        const formDataToSend = new FormData(); // Tạo FormData để gửi ảnh
        files.forEach(file => formDataToSend.append('identity_images[]', file)); // Thêm ảnh vào FormData

        extractLoading.value = true; // Bật trạng thái loading
        try {
            // Gửi yêu cầu POST để trích xuất thông tin từ ảnh CCCD
            const response = await $api('/extract-identity-images', {
                method: 'POST',
                body: formDataToSend,
                headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value }
            });
            formData.value.identity_document = response.data; // Cập nhật thông tin CCCD
            identityImages.value = files; // Lưu danh sách ảnh
            toast.success(response.message); // Hiển thị thông báo thành công
            extractErrorCount.value = 0; // Reset số lần quét lỗi

            // Vô hiệu hóa Dropzone nếu CCCD hợp lệ
            if (dropzoneInstance.value && formData.value.identity_document.has_valid) {
                dropzoneInstance.value.disable();
            }
        } catch (error) {
            // Xử lý lỗi khi quét CCCD
            if (error.response?.status === 422 && error.response?._data?.error) {
                const errorMessage = error.response._data.error;
                if (
                    errorMessage.includes('Lỗi xử lý ảnh CCCD') ||
                    errorMessage.includes('Không thể xác định mặt trước hoặc mặt sau') ||
                    errorMessage.includes('Không tìm thấy văn bản trong ảnh CCCD')
                ) {
                    extractErrorCount.value += 1; // Tăng số lần quét lỗi
                }
            }

            handleBackendError(error, toast); // Xử lý lỗi backend
            // Reset thông tin CCCD khi quét thất bại
            formData.value.identity_document = {
                identity_number: '',
                full_name: '',
                year_of_birth: '',
                date_of_issue: '',
                place_of_issue: '',
                permanent_address: '',
                has_valid: false
            };
            identityImages.value = []; // Xóa danh sách ảnh
            if (dropzoneInstance.value) {
                dropzoneInstance.value.removeAllFiles(true); // Xóa tất cả file trong Dropzone
            }

            // Nếu quét thất bại 5 lần, bật chế độ bypass
            if (extractErrorCount.value >= 5) {
                bypassExtract.value = true;
                toast.warning('Quét CCCD thất bại 5 lần. Bạn có thể tải ảnh lên để admin xác nhận.');
            }
        } finally {
            extractLoading.value = false; // Tắt trạng thái loading
        }
    };

    // Gửi thông tin người ở cùng lên server
    const submitTenant = async () => {
        // Kiểm tra các trường bắt buộc
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
        // Thêm các trường dữ liệu vào FormData
        formDataToSend.append('name', formData.value.name);
        formDataToSend.append('phone', formData.value.phone);
        if (formData.value.email) formDataToSend.append('email', formData.value.email);
        if (formData.value.gender) formDataToSend.append('gender', formData.value.gender);
        if (formData.value.birthdate) formDataToSend.append('birthdate', formData.value.birthdate);
        if (formData.value.address) formDataToSend.append('address', formData.value.address);
        formDataToSend.append('relation_with_primary', formData.value.relation_with_primary);
        identityImages.value.forEach(file => formDataToSend.append('identity_images[]', file)); // Thêm ảnh CCCD

        try {
            // Gửi yêu cầu POST để thêm người ở cùng
            const response = await $api(`/contracts/${contractId}/tenants`, {
                method: 'POST',
                headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value },
                body: formDataToSend
            });
            toast.success('Thêm người ở cùng thành công'); // Hiển thị thông báo thành công
            return response.data; // Trả về dữ liệu phản hồi
        } catch (error) {
            handleBackendError(error, toast); // Xử lý lỗi backend
            throw error; // Ném lỗi để xử lý ở nơi gọi
        }
    };

    // Trả về các hàm để sử dụng trong component
    return {
        handleIdentityUpload,
        submitTenant
    };
}
