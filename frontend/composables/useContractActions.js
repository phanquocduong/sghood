import { useAppToast } from '~/composables/useToast';
import { useApi } from '~/composables/useApi';

// Composable xử lý các hành động liên quan đến hợp đồng
export function useContractActions({ isLoading, contracts }) {
    const { $api } = useNuxtApp(); // Lấy instance của API
    const { handleBackendError } = useApi(); // Hàm xử lý lỗi từ backend
    const toast = useAppToast(); // Hàm hiển thị thông báo

    // Lấy danh sách hợp đồng
    const fetchContracts = async () => {
        isLoading.value = true; // Bật trạng thái loading
        try {
            const response = await $api('/contracts', { method: 'GET' }); // Gọi API lấy danh sách hợp đồng
            contracts.value = response.data; // Cập nhật danh sách hợp đồng
        } catch (error) {
            handleBackendError(error, toast); // Xử lý lỗi nếu có
        } finally {
            isLoading.value = false; // Tắt trạng thái loading
        }
    };

    // Hủy hợp đồng
    const cancelContract = async id => {
        isLoading.value = true; // Bật trạng thái loading
        try {
            await $api(`/contracts/${id}/cancel`, {
                method: 'POST',
                headers: {
                    'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value // Thêm token bảo mật
                },
                body: { _method: 'PATCH' }
            });
            toast.success('Hủy hợp đồng thành công'); // Hiển thị thông báo thành công
            await fetchContracts(); // Làm mới danh sách hợp đồng
        } catch (error) {
            handleBackendError(error, toast); // Xử lý lỗi nếu có
        } finally {
            isLoading.value = false; // Tắt trạng thái loading
        }
    };

    // Gia hạn hợp đồng
    const extendContract = async (id, months) => {
        isLoading.value = true; // Bật trạng thái loading
        try {
            await $api(`/contracts/${id}/extend`, {
                method: 'POST',
                headers: {
                    'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value // Thêm token bảo mật
                },
                body: { months }
            });
            toast.success('Yêu cầu gia hạn hợp đồng đã được gửi.'); // Hiển thị thông báo thành công
            await fetchContracts(); // Làm mới danh sách hợp đồng
        } catch (error) {
            handleBackendError(error, toast); // Xử lý lỗi nếu có
        } finally {
            isLoading.value = false; // Tắt trạng thái loading
        }
    };

    // Trả phòng và hoàn tiền cọc
    const returnContract = async (contractId, data) => {
        isLoading.value = true; // Bật trạng thái loading
        try {
            await $api(`/contracts/${contractId}/return`, {
                method: 'POST',
                headers: {
                    'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value // Thêm token bảo mật
                },
                body: data
            });
            toast.success('Yêu cầu trả phòng và hoàn tiền cọc đã được gửi.'); // Hiển thị thông báo thành công
            await fetchContracts(); // Làm mới danh sách hợp đồng
        } catch (error) {
            handleBackendError(error, toast); // Xử lý lỗi nếu có
        } finally {
            isLoading.value = false; // Tắt trạng thái loading
        }
    };

    return {
        fetchContracts,
        cancelContract,
        extendContract,
        returnContract
    };
}
