<template>
    <!-- Modal hiển thị chi tiết kiểm kê và hoàn tiền -->
    <div id="small-dialog" class="zoom-anim-dialog mfp-hide">
        <div class="small-dialog-header">
            <h3>Chi tiết kiểm kê và hoàn tiền</h3>
            <p class="booking-subtitle">Thông tin kiểm kê tài sản và hoàn tiền</p>
        </div>
        <div class="message-reply margin-top-0">
            <!-- Bảng kiểm kê tài sản -->
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
            <!-- Hình ảnh kiểm kê -->
            <div v-if="checkout.images && checkout.images.length" class="inventory-images">
                <div class="image-gallery">
                    <div v-for="(image, index) in checkout.images" :key="index" class="image-item">
                        <img :src="useRuntimeConfig().public.baseUrl + image" :alt="'Hình ảnh kiểm kê ' + (index + 1)" />
                    </div>
                </div>
            </div>
            <!-- Thông tin tài chính -->
            <div v-if="checkout.contract || checkout.deduction_amount || checkout.final_refunded_amount" class="financial-info">
                <div class="financial-details">
                    <p v-if="checkout.contract?.deposit_amount">
                        <strong>Tiền cọc hợp đồng:</strong> {{ formatPrice(checkout.contract.deposit_amount) }}
                    </p>
                    <p><strong>Số tiền khấu hao (Đền bù):</strong> {{ formatPrice(checkout.deduction_amount) }}</p>
                    <p><strong>Số tiền hoàn lại cuối cùng:</strong> {{ formatPrice(checkout.final_refunded_amount) }}</p>
                </div>
            </div>

            <!-- Form từ chối kiểm kê -->
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
                    <!-- Nút mở form từ chối -->
                    <button v-if="!showRejectionForm" @click="$emit('update:show-rejection-form', true)" class="button gray" type="button">
                        <i class="sl sl-icon-close"></i> Từ chối
                    </button>
                    <!-- Nút xác nhận từ chối -->
                    <button
                        v-if="showRejectionForm"
                        @click="$emit('submit-rejection')"
                        class="button gray"
                        :disabled="rejectLoading || !rejectionReason"
                    >
                        <i class="sl sl-icon-close"></i> {{ rejectLoading ? 'Đang xử lý...' : 'Xác nhận từ chối' }}
                    </button>
                    <!-- Nút đồng ý kiểm kê -->
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

// Định nghĩa props
defineProps({
    checkout: { type: Object, required: true }, // Thông tin yêu cầu trả phòng
    showRejectionForm: { type: Boolean, required: true }, // Trạng thái hiển thị form từ chối
    rejectionReason: { type: String, required: true }, // Lý do từ chối
    confirmLoading: { type: Boolean, required: true }, // Trạng thái loading khi xác nhận
    rejectLoading: { type: Boolean, required: true } // Trạng thái loading khi từ chối
});

// Định nghĩa các sự kiện emit
defineEmits(['update:show-rejection-form', 'update:rejection-reason', 'submit-approval', 'submit-rejection']);

const { formatPrice } = useFormatPrice(); // Sử dụng composable để định dạng giá
</script>

<style scoped>
@import '~/public/css/modal.css';
@import '~/public/css/inventory-modal.css';
</style>
