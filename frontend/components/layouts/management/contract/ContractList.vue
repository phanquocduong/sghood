<template>
    <h4>Quản lý hợp đồng</h4>

    <Loading :is-loading="isLoading" />

    <ul>
        <ContractItem
            v-for="item in items"
            :key="item.id"
            :item="item"
            :config="config"
            :today="today"
            @reject-item="handleRejectItem"
            @extend-contract="handleExtendContract"
            @return-contract="handleReturnContract"
            @download-pdf="downloadPdf"
        />
        <div v-if="!items.length" class="col-md-12 text-center">
            <p>Chưa có hợp đồng nào.</p>
        </div>
    </ul>
</template>

<script setup>
import { computed } from 'vue';
import { useToast } from 'vue-toastification';

const { $api } = useNuxtApp();
const toast = useToast();
const config = useRuntimeConfig();

const props = defineProps({
    items: {
        type: Array,
        required: true
    },
    isLoading: {
        type: Boolean,
        required: true
    }
});

const emit = defineEmits(['rejectItem', 'extendContract', 'returnContract']);
const today = computed(() => new Date().toISOString().split('T')[0]);

const handleRejectItem = id => {
    emit('rejectItem', id);
};

const handleExtendContract = (id, months) => {
    emit('extendContract', id, months);
};

const handleReturnContract = (id, data) => {
    emit('returnContract', id, data);
};

const downloadPdf = async id => {
    try {
        const response = await $api(`/contracts/${id}/download-pdf`, { method: 'GET' });
        window.open(response.data.file_url, '_blank');
    } catch (error) {
        toast.error(error.response?._data?.error || 'Đã có lỗi xảy ra khi tải PDF.');
    }
};
</script>

<style>
.custom-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10000 !important;
}

.custom-modal {
    background: #ffffff;
    border-radius: 10px;
    width: 40em;
    max-width: 100%;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    margin-top: 5%;
}

.custom-modal-header {
    padding: 15px 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.custom-modal-header h3 {
    margin: 0;
    font-size: 1.5em;
    color: #2c3e50;
    font-weight: 600;
}

.close-button {
    background: none;
    border: none;
    font-size: 1.5em;
    cursor: pointer;
    color: #7f8c8d;
    transition: color 0.3s ease;
}

.close-button:hover {
    color: #e74c3c;
}

.custom-modal-body {
    padding: 20px;
    max-height: 60vh;
    overflow-y: auto;
    background: #fff;
}

.modal-content p {
    margin: 10px 0;
    color: #34495e;
    font-size: 1em;
}

.modal-content p strong {
    color: #2c3e50;
}

.modal-content hr {
    margin: 20px 0;
    border: 0;
    border-top: 1px solid #e9ecef;
}

.form-group {
    margin-bottom: 15px;
}

.form-label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #2c3e50;
    font-size: 0.95em;
}

.form-control,
.custom-select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 1em;
    line-height: 28px;
    color: #495057;
    background: #fff;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.form-control:focus,
.custom-select:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
}

.custom-select {
    appearance: none;
    background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23333' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E")
        no-repeat right 10px center;
    padding-right: 30px;
}

.custom-select option {
    padding: 10px;
    background: #fff;
    color: #333;
}

.error-message {
    color: #e74c3c;
    font-size: 0.9em;
    margin-top: 5px;
}

.custom-modal-footer {
    padding: 15px 20px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    background: #f8f9fa;
}

.custom-modal-footer .button {
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    border: none;
    font-weight: 500;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.custom-modal-footer .button.gray {
    background-color: #dcdde1;
    color: #2c3e50;
}

.custom-modal-footer .button.confirm {
    background-color: #2ecc71;
    color: #fff;
}

.custom-modal-footer .button:hover {
    transform: translateY(-1px);
}

.custom-modal-footer .button.gray:hover {
    background-color: #c4c6cc;
}

.custom-modal-footer .button.confirm:hover {
    background-color: #27ae60;
}

.ts-wrapper {
    position: relative;
    width: 100%;
}

.ts-control {
    border: 1px solid #ced4da;
    border-radius: 6px;
    padding: 10px 14px;
    background: #fff;
    display: flex;
    align-items: center;
    min-height: 40px;
    cursor: pointer;
    transition: border-color 0.3s ease;
    font-size: 16px;
    line-height: 28px;
}

.items-placeholder {
    font-size: 16px !important;
    line-height: 28px !important;
    height: auto !important;
}

.ts-control:hover {
    border-color: #3498db;
}

.ts-control .item {
    display: flex;
    align-items: center;
    padding: 2px 6px;
    background: #e9ecef;
    border-radius: 4px;
    margin-right: 5px;
}

.ts-control .item img {
    max-width: 40px;
    margin-right: 8px;
    vertical-align: middle;
}

.ts-dropdown {
    border: 1px solid #ced4da;
    border-radius: 6px;
    background: #fff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    margin-top: 2px;
    z-index: 1001;
}

.ts-dropdown .option {
    padding: 10px 12px;
    display: flex;
    align-items: center;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.ts-dropdown .option:hover,
.ts-dropdown .option.active {
    background-color: #f1f3f5;
}

.ts-dropdown .option img {
    max-width: 40px;
    margin-right: 10px;
    vertical-align: middle;
    object-fit: contain;
}

.ts-dropdown .option span {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: calc(100% - 50px);
}

.item:hover {
    flex: 0;
}

.text-danger {
    color: #e74c3c;
}

.swal2-popup.swal2-modal {
    width: 50em !important;
}

.swal2-actions .swal2-confirm {
    background-color: #64bc36 !important;
}

.swal2-actions .swal2-confirm:hover {
    background-color: #68cf30 !important;
}

.booking-status.pending.extension-status,
.booking-status.pending.checkout-status {
    background-color: #61b2db !important;
}

.booking-status.canceled.extension-status,
.booking-status.canceled.checkout-status {
    background-color: #ee3535 !important;
}

.bookings .list-box-listing-img {
    max-width: 150px;
    max-height: none;
    border-radius: 4px;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #f91942;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

p {
    color: #333;
    margin-top: 10px;
    font-size: 16px;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>
