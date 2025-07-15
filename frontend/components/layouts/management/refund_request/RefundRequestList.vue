<template>
    <h4>Quản lý yêu cầu hoàn tiền</h4>

    <!-- Hiển thị loading spinner -->
    <Loading :is-loading="isLoading" />

    <ul v-if="!isLoading">
        <li v-for="item in items" :key="item.id" :class="getItemClass(item.status)">
            <div class="list-box-listing bookings">
                <div class="list-box-listing-content">
                    <div class="inner">
                        <h3>
                            Yêu cầu hoàn tiền #{{ item.id }}
                            <span :class="getStatusClass(item.status)">{{ item.status }}</span>
                        </h3>
                        <div class="inner-booking-list">
                            <h5>Hợp đồng:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">#{{ item.contract_id }}</li>
                            </ul>
                        </div>
                        <div class="inner-booking-list">
                            <h5>Tiền cọc:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatCurrency(item.deposit_amount) }}đ</li>
                            </ul>
                        </div>
                        <div v-if="item.deduction_amount" class="inner-booking-list">
                            <h5>Số tiền khấu trừ:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatCurrency(item.deduction_amount) }}đ</li>
                            </ul>
                        </div>
                        <div v-if="item.final_amount" class="inner-booking-list">
                            <h5>Số tiền hoàn:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatCurrency(item.final_amount) }}đ</li>
                            </ul>
                        </div>
                        <div v-if="item.bank_info" class="inner-booking-list">
                            <h5>Thông tin ngân hàng:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatBankInfo(item.bank_info) }}</li>
                            </ul>
                        </div>
                        <div v-if="item.rejection_reason" class="inner-booking-list">
                            <h5>Lý do từ chối:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ item.rejection_reason }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="buttons-to-right">
                <a v-if="item.status === 'Chờ xử lý'" href="#" @click.prevent="openConfirmRejectPopup(item.id)" class="button gray reject">
                    <i class="sl sl-icon-close"></i> Hủy bỏ
                </a>
                <a :href="item.qr_code_path" target="_blank" class="button gray approve"> <i class="im im-icon-QR-Code"></i> Mã QR </a>
            </div>
        </li>
        <div v-if="!items.length" class="col-md-12 text-center">
            <p>Chưa có yêu cầu hoàn tiền nào.</p>
        </div>
    </ul>
</template>

<script setup>
import Swal from 'sweetalert2';
import { useRuntimeConfig } from '#app';

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

const emit = defineEmits(['rejectItem']);

const formatCurrency = amount => {
    if (!amount) return '0';
    return new Intl.NumberFormat('vi-VN').format(amount);
};

const formatBankInfo = bankInfo => {
    if (!bankInfo || typeof bankInfo !== 'object') return 'Không có thông tin';
    return [
        bankInfo.bank_name ? `Ngân hàng: ${bankInfo.bank_name}` : '',
        bankInfo.account_number ? `Số tài khoản: ${bankInfo.account_number}` : '',
        bankInfo.account_holder ? `Chủ tài khoản: ${bankInfo.account_holder}` : ''
    ]
        .filter(Boolean)
        .join(', ');
};

const getItemClass = status => {
    switch (status) {
        case 'Chờ xử lý':
        case 'Đã duyệt':
            return 'pending-booking';
        case 'Đã xử lý':
            return 'approved-booking';
        case 'Từ chối':
        case 'Huỷ bỏ':
            return 'canceled-booking';
        default:
            return '';
    }
};

const getStatusClass = status => {
    let statusClass = 'booking-status';
    switch (status) {
        case 'Chờ xử lý':
        case 'Đã duyệt':
            statusClass += ' pending';
            break;
        case 'Đã xử lý':
            statusClass += ' approved';
            break;
        case 'Từ chối':
        case 'Huỷ bỏ':
            statusClass += ' canceled';
            break;
    }
    return statusClass;
};

const openConfirmRejectPopup = async id => {
    const result = await Swal.fire({
        title: 'Xác nhận hủy yêu cầu hoàn tiền',
        text: 'Bạn có chắc chắn muốn hủy yêu cầu hoàn tiền này?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xác nhận',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#f91942',
        cancelButtonColor: '#e0e0e0',
        customClass: {
            confirmButton: 'button',
            cancelButton: 'button gray'
        }
    });

    if (result.isConfirmed) {
        emit('rejectItem', id);
    }
};
</script>

<style>
.bookings .list-box-listing-img {
    max-width: 150px;
    max-height: none;
    border-radius: 4px;
}
</style>
