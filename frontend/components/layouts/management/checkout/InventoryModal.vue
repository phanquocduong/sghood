<template>
    <div id="small-dialog" class="zoom-anim-dialog mfp-hide">
        <div class="small-dialog-header">
            <h3>Chi tiết kiểm kê và hoàn tiền</h3>
            <p class="booking-subtitle">Thông tin kiểm kê tài sản và hoàn tiền</p>
        </div>
        <div class="message-reply margin-top-0">
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
                        <tr v-for="(item, index) in checkout.inventory_details" :key="index">
                            <td>{{ item.item_name }}</td>
                            <td>{{ item.item_condition }}</td>
                            <td>{{ formatPrice(item.item_cost) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Inventory Images -->
            <div v-if="checkout.images && checkout.images.length" class="inventory-images">
                <div class="image-gallery">
                    <div v-for="(image, index) in checkout.images" :key="index" class="image-item">
                        <img :src="useRuntimeConfig().public.baseUrl + image" :alt="'Hình ảnh kiểm kê ' + (index + 1)" />
                    </div>
                </div>
            </div>
            <!-- Financial Information -->
            <div v-if="checkout.contract || checkout.deduction_amount || checkout.final_refunded_amount" class="financial-info">
                <div class="financial-details">
                    <p v-if="checkout.contract?.deposit_amount">
                        <strong>Tiền cọc hợp đồng:</strong> {{ formatPrice(checkout.contract.deposit_amount) }}
                    </p>
                    <p><strong>Số tiền khấu hao (Đền bù):</strong> {{ formatPrice(checkout.deduction_amount) }}</p>
                    <p><strong>Số tiền hoàn lại cuối cùng:</strong> {{ formatPrice(checkout.final_refunded_amount) }}</p>
                </div>
            </div>

            <!-- Rejection Form -->
            <div v-if="checkout.user_confirmation_status === 'Chưa xác nhận'" class="inventory-actions">
                <div v-if="showRejectionForm" class="rejection-form">
                    <label><i class="fa fa-sticky-note"></i> Lý do từ chối:</label>
                    <textarea
                        :value="rejectionReason"
                        @input="$emit('update:rejection-reason', $event.target.value)"
                        cols="40"
                        rows="3"
                        placeholder="Nhập lý do từ chối..."
                    ></textarea>
                </div>
                <div class="booking-actions">
                    <button v-if="!showRejectionForm" @click="$emit('update:show-rejection-form', true)" class="button gray" type="button">
                        <i class="sl sl-icon-close"></i> Từ chối
                    </button>
                    <button
                        v-if="showRejectionForm"
                        @click="$emit('submit-rejection')"
                        class="button gray"
                        :disabled="rejectLoading || !rejectionReason"
                    >
                        <i class="sl sl-icon-close"></i> {{ rejectLoading ? 'Đang xử lý...' : 'Xác nhận từ chối' }}
                    </button>
                    <button @click="$emit('submit-approval')" class="button" :disabled="confirmLoading || showRejectionForm">
                        <span v-if="confirmLoading" class="spinner"></span>
                        <i v-else class="fa fa-check"></i> {{ confirmLoading ? 'Đang xử lý...' : 'Đồng ý' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useRuntimeConfig } from '#app';
import { useFormatPrice } from '~/composables/useFormatPrice';

defineProps({
    checkout: { type: Object, required: true },
    showRejectionForm: { type: Boolean, required: true },
    rejectionReason: { type: String, required: true },
    confirmLoading: { type: Boolean, required: true },
    rejectLoading: { type: Boolean, required: true }
});

defineEmits(['update:show-rejection-form', 'update:rejection-reason', 'submit-approval', 'submit-rejection']);

const { formatPrice } = useFormatPrice();
</script>

<style scoped>
@import '~/public/css/viewing-schedules.css';

/* Financial Information */
.financial-info {
    margin: 0 0 20px;
}

.financial-info h4 {
    font-size: 16px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 12px;
}

.financial-details {
    background: #f8fafc;
    border-radius: 8px;
    padding: 4px 16px;
    border-left: 4px solid #ccc;
}

.financial-details p {
    font-size: 14px;
    color: #4a5568;
    margin-bottom: 8px;
}

/* Refund Information */
.modal-content {
    padding: 16px;
}

.modal-content p {
    font-size: 14px;
    color: #4a5568;
    margin-bottom: 8px;
}

.qr-code,
.receipt {
    margin-top: 16px;
    text-align: center;
}

.qr-code h5,
.receipt h5 {
    font-size: 14px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
}

.refund-actions {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

/* Table Styles */
.inventory-table {
    margin: 0 0 20px;
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

/* Button Actions */
.inventory-actions {
    margin-top: 20px;
    padding-top: 20px;
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
```
