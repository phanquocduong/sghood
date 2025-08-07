import { useAppToast } from '~/composables/useToast';
import { useApi } from '~/composables/useApi';

export function useContractActions({ isLoading, contracts }) {
    const { $api } = useNuxtApp();
    const { handleBackendError } = useApi();
    const toast = useAppToast();

    const fetchContracts = async () => {
        isLoading.value = true;
        try {
            const response = await $api('/contracts', { method: 'GET' });
            contracts.value = response.data;
        } catch (error) {
            handleBackendError(error, toast);
        } finally {
            isLoading.value = false;
        }
    };

    const cancelContract = async id => {
        isLoading.value = true;
        try {
            await $api(`/contracts/${id}/cancel`, {
                method: 'POST',
                headers: {
                    'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
                }
            });
            toast.success('Hủy hợp đồng thành công');
            await fetchContracts();
        } catch (error) {
            handleBackendError(error, toast);
        } finally {
            isLoading.value = false;
        }
    };

    const extendContract = async (id, months) => {
        isLoading.value = true;
        try {
            await $api(`/contracts/${id}/extend`, {
                method: 'POST',
                headers: {
                    'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
                },
                body: { months }
            });
            toast.success('Yêu cầu gia hạn hợp đồng đã được gửi.');
            await fetchContracts();
        } catch (error) {
            handleBackendError(error, toast);
        } finally {
            isLoading.value = false;
        }
    };

    const returnContract = async (contractId, data) => {
        isLoading.value = true;
        try {
            console.log(data);
            await $api(`/contracts/${contractId}/return`, {
                method: 'POST',
                headers: {
                    'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
                },
                body: data
            });
            toast.success('Yêu cầu trả phòng và hoàn tiền cọc đã được gửi.');
            await fetchContracts();
        } catch (error) {
            handleBackendError(error, toast);
        } finally {
            isLoading.value = false;
        }
    };

    const earlyTermination = async id => {
        isLoading.value = true;
        try {
            await $api(`/contracts/${id}/early-termination`, {
                method: 'POST',
                headers: {
                    'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
                }
            });
            toast.success('Yêu cầu kết thúc hợp đồng sớm đã được gửi.');
            await fetchContracts();
        } catch (error) {
            handleBackendError(error, toast);
        } finally {
            isLoading.value = false;
        }
    };

    return {
        fetchContracts,
        cancelContract,
        extendContract,
        returnContract,
        earlyTermination
    };
}
