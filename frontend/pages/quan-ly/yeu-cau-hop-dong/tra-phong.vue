<template>
    <div>
        <Titlebar title="Yêu cầu trả phòng" />

        <!-- Modal Dialog for Inventory Details -->
        <div id="small-dialog" class="zoom-anim-dialog mfp-hide">
            <div class="small-dialog-header">
                <h3>Chi tiết kiểm kê</h3>
                <p class="booking-subtitle">Danh sách kiểm kê tài sản phòng</p>
            </div>
            <div class="message-reply margin-top-0">
                <!-- Inventory Images -->
                <div v-if="selectedCheckout.images && selectedCheckout.images.length" class="inventory-images">
                    <h4>Hình ảnh kiểm kê</h4>
                    <div class="image-gallery">
                        <div v-for="(image, index) in selectedCheckout.images" :key="index" class="image-item">
                            <img :src="config.public.baseUrl + '/storage/' + image" :alt="'Hình ảnh kiểm kê ' + (index + 1)" />
                        </div>
                    </div>
                </div>
                <!-- Inventory Table -->
                <div class="inventory-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Tên mục</th>
                                <th>Tình trạng</th>
                                <th>Khấu hao (Đền bù)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in selectedCheckout.inventory_details" :key="index">
                                <td>{{ item.item_name }}</td>
                                <td>{{ item.item_condition }}</td>
                                <td>{{ formatPrice(item.item_cost) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="selectedCheckout.user_confirmation_status === 'Chưa xác nhận'" class="inventory-actions">
                    <div v-if="showRejectionForm" class="rejection-form">
                        <label><i class="fa fa-sticky-note"></i> Lý do từ chối:</label>
                        <textarea v-model="rejectionReason" cols="40" rows="3" placeholder="Nhập lý do từ chối..."></textarea>
                    </div>
                    <div class="booking-actions">
                        <button v-if="!showRejectionForm" @click="showRejectionForm = true" class="button gray" type="button">
                            <i class="sl sl-icon-close"></i> Từ chối
                        </button>
                        <button
                            v-if="showRejectionForm"
                            @click="submitRejection"
                            class="button gray"
                            :disabled="rejectLoading || !rejectionReason"
                        >
                            <i class="sl sl-icon-close"></i> {{ rejectLoading ? 'Đang xử lý...' : 'Xác nhận từ chối' }}
                        </button>
                        <button @click="submitApproval" class="button" :disabled="confirmLoading || showRejectionForm">
                            <span v-if="confirmLoading" class="spinner"></span>
                            <i v-else class="fa fa-check"></i> {{ confirmLoading ? 'Đang xử lý...' : 'Đồng ý' }}
                        </button>
                    </div>
                </div>
                <div v-if="!selectedCheckout.has_left && selectedCheckout.user_confirmation_status === 'Đồng ý'" class="inventory-actions">
                    <div class="booking-actions">
                        <button @click="confirmLeftRoom" class="button" :disabled="leaveLoading">
                            <span v-if="leaveLoading" class="spinner"></span>
                            <i v-else class="fa fa-door-open"></i> {{ leaveLoading ? 'Đang xử lý...' : 'Xác nhận đã rời phòng' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="dashboard-list-box margin-top-0">
                    <CheckoutFilter v-model:filter="filter" @update:filter="fetchCheckouts" />
                    <CheckoutList
                        :items="checkouts"
                        :is-loading="isLoading"
                        @cancel-checkout="cancelCheckout"
                        @open-inventory-popup="openInventoryPopup"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useToast } from 'vue-toastification';
import { useApi } from '~/composables/useApi';
import { useFormatPrice } from '~/composables/useFormatPrice';
import { useRuntimeConfig } from '#app';

definePageMeta({
    layout: 'management'
});

const { $api } = useNuxtApp();
const config = useRuntimeConfig();
const checkouts = ref([]);
const filter = ref({ sort: 'default', status: '' });
const isLoading = ref(false);
const toast = useToast();
const { handleBackendError } = useApi();
const { formatPrice } = useFormatPrice();

const selectedCheckout = ref({});
const showRejectionForm = ref(false);
const rejectionReason = ref('');
const confirmLoading = ref(false);
const rejectLoading = ref(false);
const leaveLoading = ref(false);

const fetchCheckouts = async () => {
    isLoading.value = true;
    try {
        const response = await $api('/checkouts', { method: 'GET', params: filter.value });
        checkouts.value = response.data;
    } catch (error) {
        handleBackendError(error, toast);
    } finally {
        isLoading.value = false;
    }
};

const cancelCheckout = async id => {
    isLoading.value = true;
    try {
        await $api(`/checkouts/${id}/cancel`, {
            method: 'POST',
            headers: {
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            }
        });
        await fetchCheckouts();
        toast.success('Hủy yêu cầu trả phòng thành công');
    } catch (error) {
        handleBackendError(error, toast);
    } finally {
        isLoading.value = false;
    }
};

const openInventoryPopup = item => {
    selectedCheckout.value = item;
    showRejectionForm.value = false;
    rejectionReason.value = '';

    if (!window.jQuery || !window.jQuery.fn.magnificPopup) {
        console.error('Magnific Popup không được tải');
        return;
    }

    window.jQuery.magnificPopup.open({
        items: { src: '#small-dialog', type: 'inline' },
        fixedContentPos: false,
        fixedBgPos: true,
        overflowY: 'auto',
        closeBtnInside: true,
        preloader: false,
        midClick: true,
        removalDelay: 300,
        mainClass: 'my-mfp-zoom-in',
        closeOnBgClick: false
    });
};

const closeModal = () => {
    if (window.jQuery && window.jQuery.fn.magnificPopup) {
        window.jQuery.magnificPopup.close();
    }
};

const submitApproval = async () => {
    confirmLoading.value = true;
    try {
        await $api(`/checkouts/${selectedCheckout.value.id}/confirm`, {
            method: 'POST',
            headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value },
            body: { status: 'Đồng ý' }
        });
        toast.success('Xác nhận kiểm kê thành công');
        closeModal();
        await fetchCheckouts();
    } catch (error) {
        handleBackendError(error, toast);
    } finally {
        confirmLoading.value = false;
    }
};

const submitRejection = async () => {
    if (!rejectionReason.value.trim()) {
        toast.error('Vui lòng nhập lý do từ chối');
        return;
    }

    rejectLoading.value = true;
    try {
        await $api(`/checkouts/${selectedCheckout.value.id}/confirm`, {
            method: 'POST',
            headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value },
            body: {
                status: 'Từ chối',
                user_rejection_reason: rejectionReason.value
            }
        });
        toast.success('Từ chối kiểm kê thành công');
        closeModal();
        await fetchCheckouts();
    } catch (error) {
        handleBackendError(error, toast);
    } finally {
        rejectLoading.value = false;
    }
};

const confirmLeftRoom = async () => {
    leaveLoading.value = true;
    try {
        await $api(`/checkouts/${selectedCheckout.value.id}/left-room`, {
            method: 'POST',
            headers: { 'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value }
        });
        toast.success('Xác nhận đã rời phòng thành công');
        closeModal();
        await fetchCheckouts();
    } catch (error) {
        handleBackendError(error, toast);
    } finally {
        leaveLoading.value = false;
    }
};

onMounted(() => {
    fetchCheckouts();
});
</script>

<style scoped>
.spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
    margin-right: 8px;
    vertical-align: middle;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Table Styles */
.inventory-table {
    margin: 20px 0;
}

.inventory-table table {
    width: 100%;
    border-collapse: collapse;
    background: #ffffff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.inventory-table th,
.inventory-table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

.inventory-table th {
    background: #f7fafc;
    font-weight: 600;
    color: #2d3748;
    font-size: 14px;
}

.inventory-table td {
    font-size: 14px;
    color: #4a5568;
}

.inventory-table tr:last-child td {
    border-bottom: none;
}

/* Rejection Form */
.rejection-form {
    margin-top: 20px;
}

.rejection-form label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
    font-size: 14px;
}

.rejection-form label i {
    color: #f91942;
    font-size: 16px;
}

.rejection-form textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #ffffff;
    outline: none;
    resize: vertical;
    min-height: 80px;
    font-family: inherit;
    box-sizing: border-box;
}

.rejection-form textarea:focus {
    border-color: #f91942;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    transform: translateY(-1px);
}

.rejection-form textarea:hover {
    border-color: #cbd5e0;
}

/* Modal Header */
.small-dialog-header {
    background: linear-gradient(135deg, #f91942 0%, #ff5f7e 100%);
    padding: 25px 30px;
    color: white;
    position: relative;
    overflow: hidden;
    margin-bottom: 0;
}

.small-dialog-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Ccircle cx='30' cy='30' r='4'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E")
        repeat;
}

.small-dialog-header h3 {
    margin: 0 0 5px 0;
    font-size: 22px;
    font-weight: 600;
    position: relative;
    z-index: 1;
    color: white;
    font-weight: bolder;
}

.booking-subtitle {
    margin: 0;
    opacity: 0.9;
    font-size: 14px;
    position: relative;
    z-index: 1;
    color: white;
    font-weight: 500;
}

/* Buttonpatient Actions */
.inventory-actions {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}

.booking-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 20px;
}

.button {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 14px;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 120px;
    justify-content: center;
    text-decoration: none;
}

.button.gray {
    background: #f7fafc;
    color: #4a5568;
    border: 2px solid #e2e8f0;
}

.button.gray:hover {
    background: #edf2f7;
    border-color: #cbd5e0;
    transform: translateY(-1px);
}

.button:not(.gray) {
    background: linear-gradient(135deg, #f91942 0%, #ff5f7e 100%);
    color: white;
    border: 2px solid transparent;
}

.oanh button:not(.gray):hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
}

.button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Image Gallery Styles */
.inventory-images {
    margin: 20px 0;
}

.inventory-images h4 {
    font-size: 16px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 12px;
}

.image-gallery {
    display: flex;
    overflow-x: auto;
    gap: 12px;
    padding-bottom: 10px;
}

.image-item {
    flex: 0 0 auto;
    width: 150px;
    height: 150px;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.image-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* Responsive Design */
@media (max-width: 768px) {
    .inventory-table table {
        display: block;
        overflow-x: auto;
    }

    .booking-actions {
        flex-direction: column;
        gap: 10px;
    }

    .button {
        width: 100%;
    }

    .small-dialog-header {
        padding: 20px;
    }

    .image-gallery {
        flex-direction: row;
        flex-wrap: nowrap;
    }

    .image-item {
        width: 120px;
        height: 120px;
    }
}
</style>
