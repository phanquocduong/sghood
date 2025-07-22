<template>
    <h4>Quản lý yêu cầu gia hạn</h4>

    <!-- Hiển thị loading spinner -->
    <Loading :is-loading="isLoading" />

    <ul>
        <li v-for="extension in extensions" :key="extension.id" :class="getItemClass(extension.status)">
            <div class="list-box-listing bookings">
                <div class="list-box-listing-content">
                    <div class="inner">
                        <h3>
                            Mã #{{ extension.id }}
                            <span :class="getStatusClass(extension.status)">
                                {{ extension.status }}
                            </span>
                        </h3>
                        <div class="inner-booking-list">
                            <h5>Hợp đồng:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">#{{ extension.contract_id }}</li>
                            </ul>
                        </div>
                        <div class="inner-booking-list">
                            <h5>Gia hạn đến:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDate(extension.new_end_date) }}</li>
                            </ul>
                        </div>
                        <div class="inner-booking-list">
                            <h5>Giá thuê mới:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatPrice(extension.new_rental_price) }}</li>
                            </ul>
                        </div>
                        <div v-if="extension.rejection_reason" class="inner-booking-list">
                            <h5>Lý do từ chối:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ extension.rejection_reason }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="buttons-to-right">
                <a
                    v-if="extension.status === 'Chờ duyệt'"
                    href="#"
                    @click.prevent="openConfirmCancelPopup(extension.id)"
                    class="button gray reject"
                >
                    <i class="sl sl-icon-close"></i> Hủy bỏ
                </a>
                <NuxtLink :to="`/quan-ly/hop-dong/${extension.contract_id}`" class="button gray approve">
                    <i class="im im-icon-File-Download"></i> Xem hợp đồng
                </NuxtLink>
                <a href="#" @click.prevent="openExtensionDetailPopup(extension)" class="button gray approve popup-with-zoom-anim">
                    <i class="im im-icon-Folder-Bookmark"></i> Xem chi tiết
                </a>
            </div>
        </li>
        <div v-if="!extensions?.length" class="col-md-12 text-center">
            <p>Chưa có yêu cầu gia hạn nào.</p>
        </div>
    </ul>
</template>

<script setup>
import Swal from 'sweetalert2';
import { useFormatPrice } from '~/composables/useFormatPrice';
import { useFormatDate } from '~/composables/useFormatDate';

const { formatPrice } = useFormatPrice();
const { formatDate } = useFormatDate();

const props = defineProps({
    extensions: {
        type: Array,
        required: true
    },
    isLoading: {
        type: Boolean,
        required: true
    }
});

const emit = defineEmits(['cancelExtension']);

const getItemClass = status => {
    switch (status) {
        case 'Chờ duyệt':
            return 'pending-booking';
        case 'Hoạt động':
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
    if (status === 'Chờ duyệt') {
        statusClass += ' pending';
    } else if (status === 'Hoạt động') {
        statusClass += ' approved';
    } else if (status === 'Từ chối' || status === 'Huỷ bỏ') {
        statusClass += ' canceled';
    }
    return statusClass;
};

const openConfirmCancelPopup = async id => {
    const result = await Swal.fire({
        title: `Xác nhận hủy bỏ gia hạn`,
        text: `Bạn có chắc chắn muốn hủy bỏ gia hạn hợp đồng?`,
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
        emit('cancelExtension', id);
    }
};

const openExtensionDetailPopup = async extension => {
    await Swal.fire({
        title: `Chi tiết yêu cầu gia hạn hợp đồng #${extension.id}`,
        html: `
            <div style="text-align: left;">
                <div style="border: 1px solid #ddd; padding: 10px; background-color: #f9f9f9;">
                    ${extension.content || 'Không có nội dung yêu cầu gia hạn.'}
                </div>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Đóng',
        confirmButtonColor: '#666',
        customClass: {
            confirmButton: 'button gray'
        },
        width: '600px'
    });
};
</script>

<style>
.contract-document {
    padding: 8px 16px;
}
</style>
