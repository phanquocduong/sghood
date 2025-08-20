<template>
    <h4>Quản lý yêu cầu trả phòng</h4>

    <Loading :is-loading="isLoading" />

    <ul v-if="!isLoading">
        <li v-for="item in items" :key="item.id" :class="getItemClass(item.inventory_status, item.canceled_at)">
            <div class="list-box-listing bookings">
                <div class="list-box-listing-img">
                    <NuxtLink :to="`/danh-sach-nha-tro/${item.motel_slug}`" target="_blank" style="height: 150px">
                        <img :src="config.public.baseUrl + item.room_image" :alt="item.room_name - item.motel_name" />
                    </NuxtLink>
                </div>
                <div class="list-box-listing-content">
                    <div class="inner">
                        <h3>
                            Hợp đồng #{{ item.contract_id }} [{{ item.room_name }} - {{ item.motel_name }}]
                            <span v-if="item.canceled_at" class="booking-status canceled">Huỷ bỏ</span>
                            <span v-if="!item.canceled_at" :class="getInventoryStatusClass(item.inventory_status)">{{
                                item.inventory_status
                            }}</span>
                            <span
                                v-if="!item.canceled_at && item.inventory_status === 'Đã kiểm kê'"
                                :class="getUserConfirmationStatusClass(item.user_confirmation_status)"
                                >{{ getUserConfirmationStatusText(item.user_confirmation_status) }}</span
                            >
                        </h3>
                        <div v-if="item.user_rejection_reason" class="inner-booking-list">
                            <h5>Lý do từ chối:</h5>
                            <ul class="booking-list">
                                <li>{{ item.user_rejection_reason }}</li>
                            </ul>
                        </div>
                        <div
                            v-if="item.inventory_status === 'Đã kiểm kê' && item.user_confirmation_status === 'Đồng ý'"
                            class="inner-booking-list"
                        >
                            <h5>Trạng thái hoàn tiền:</h5>
                            <ul class="booking-list">
                                <li class="highlighted" :class="item.refund_status === 'Chờ xử lý' ? 'pending' : ''">
                                    {{ item.refund_status }}
                                </li>
                            </ul>
                        </div>
                        <div class="inner-booking-list">
                            <h5>Ngày rời phòng:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDate(item.check_out_date) }}</li>
                            </ul>
                        </div>
                        <div class="inner-booking-list">
                            <h5>Trạng thái rời phòng:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ item.has_left ? 'Đã rời' : 'Chưa rời' }}</li>
                            </ul>
                        </div>
                        <div v-if="item.contract.deposit_amount" class="inner-booking-list">
                            <h5>Tiền cọc hợp đồng:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatPrice(item.contract.deposit_amount) }}</li>
                            </ul>
                        </div>
                        <div v-if="item.deduction_amount" class="inner-booking-list">
                            <h5>Số tiền khấu trừ:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatPrice(item.deduction_amount) }}</li>
                            </ul>
                        </div>
                        <div v-if="item.final_refunded_amount" class="inner-booking-list">
                            <h5>Số tiền hoàn lại cuối cùng:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatPrice(item.final_refunded_amount) }}</li>
                            </ul>
                        </div>
                        <div v-if="item.note" class="inner-booking-list">
                            <h5>Ghi chú:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ item.note }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="buttons-to-right">
                <a
                    v-if="item.inventory_status === 'Đã kiểm kê'"
                    href="#"
                    @click.prevent="emitOpenInventoryPopup(item)"
                    class="button gray approve"
                >
                    <i class="im im-icon-Check"></i>
                    {{ item.user_confirmation_status === 'Chưa xác nhận' ? 'Xác nhận kiểm kê' : 'Xem kiểm kê' }}
                </a>
                <a
                    v-if="
                        item.inventory_status === 'Đã kiểm kê' &&
                        item.user_confirmation_status === 'Đồng ý' &&
                        !item.has_left &&
                        !item.canceled_at
                    "
                    href="#"
                    @click.prevent="openConfirmLeftRoomPopup(item)"
                    class="button gray approve"
                >
                    <i class="fa fa-door-open"></i> Xác nhận đã rời phòng
                </a>
                <a
                    v-if="item.refund_status === 'Chờ xử lý' && item.bank_info && !item.canceled_at"
                    href="#"
                    @click.prevent="emitOpenBankInfoPopup(item)"
                    class="button gray"
                >
                    <i class="im im-icon-Bank"></i> Thông tin chuyển khoản
                </a>
                <a
                    v-if="item.inventory_status === 'Chờ kiểm kê' && !item.canceled_at"
                    href="#"
                    @click.prevent="openConfirmCancelPopup(item.id)"
                    class="button gray reject"
                >
                    <i class="sl sl-icon-close"></i> Hủy bỏ
                </a>
            </div>
        </li>
        <div v-if="!items.length" class="col-md-12 text-center">
            <p>Chưa có yêu cầu trả phòng nào.</p>
        </div>
    </ul>
</template>

<script setup>
import Swal from 'sweetalert2';
import { useRuntimeConfig } from '#app';
import { useFormatPrice } from '~/composables/useFormatPrice';
import { useFormatDate } from '~/composables/useFormatDate';

const { formatDate } = useFormatDate();
const { formatPrice } = useFormatPrice();
const config = useRuntimeConfig();

const props = defineProps({
    items: { type: Array, required: true },
    isLoading: { type: Boolean, required: true }
});

const emit = defineEmits(['cancelCheckout', 'openInventoryPopup', 'openBankInfoPopup', 'confirmLeftRoom']);

const getItemClass = (status, cancel) => {
    if (cancel) {
        return 'canceled-booking';
    }
    switch (status) {
        case 'Chờ kiểm kê':
        case 'Kiểm kê lại':
            return 'pending-booking';
        case 'Đã kiểm kê':
            return 'approved-booking';
        case 'Huỷ bỏ':
            return 'canceled-booking';
        default:
            return '';
    }
};

const getInventoryStatusClass = status => {
    let statusClass = 'booking-status';
    switch (status) {
        case 'Chờ kiểm kê':
        case 'K BS kiểm kê lại':
            statusClass += ' pending';
            break;
        case 'Đã kiểm kê':
            statusClass += ' approved';
            break;
        case 'Huỷ bỏ':
            statusClass += ' canceled';
            break;
    }
    return statusClass;
};

const getUserConfirmationStatusClass = status => {
    let statusClass = 'booking-status';
    switch (status) {
        case 'Chưa xác nhận':
            statusClass += ' pending user-confirmation-status';
            break;
        case 'Đồng ý':
            statusClass += ' approved';
            break;
        case 'Từ chối':
            statusClass += ' canceled user-confirmation-status';
            break;
    }
    return statusClass;
};

const getUserConfirmationStatusText = status => {
    switch (status) {
        case 'Chưa xác nhận':
            return 'Chờ xác nhận từ bạn';
        case 'Đồng ý':
            return 'Bạn đã đồng ý với kết quả';
        case 'Từ chối':
            return 'Bạn đã từ chối kết quả';
        default:
            return '';
    }
};

const openConfirmCancelPopup = async id => {
    const result = await Swal.fire({
        title: 'Xác nhận hủy yêu cầu trả phòng',
        text: 'Bạn có chắc chắn muốn hủy yêu cầu trả phòng này?',
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
        emit('cancelCheckout', id);
    }
};

const openConfirmLeftRoomPopup = async item => {
    const result = await Swal.fire({
        title: 'Xác nhận đã rời phòng',
        text: 'Bạn có chắc chắn muốn xác nhận đã rời phòng này?',
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
        emit('confirmLeftRoom', item);
    }
};

const emitOpenInventoryPopup = item => {
    emit('openInventoryPopup', item);
};

const emitOpenBankInfoPopup = item => {
    emit('openBankInfoPopup', item);
};
</script>

<style scoped>
.bookings .list-box-listing-img {
    max-width: 150px;
    max-height: none;
    border-radius: 4px;
}

.booking-status.pending.user-confirmation-status {
    background-color: #2196f3 !important;
}

.booking-status.canceled.user-confirmation-status {
    background-color: #e42929 !important;
}

.approved-booking .inner-booking-list ul li.highlighted.pending {
    background-color: #e6f3ff !important;
    color: #1a5490 !important;
    border-color: #b3d9ff !important;
}
</style>
