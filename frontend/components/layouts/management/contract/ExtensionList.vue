<template>
    <h4>Gia hạn - Phụ lục hợp đồng</h4>

    <ul>
        <li v-for="extension in contract?.extensions" :key="extension.id" :class="getItemClass(extension.status)">
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
                            <h5>Gia hạn đến:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDate(extension.new_end_date) }}</li>
                            </ul>
                        </div>
                        <div class="inner-booking-list">
                            <h5>Giá thuê mới:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatCurrency(extension.new_rental_price) }}đ</li>
                            </ul>
                        </div>
                        <div v-if="extension.cancellation_reason" class="inner-booking-list">
                            <h5>Lý do từ chối</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ extension.cancellation_reason }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="buttons-to-right">
                <a
                    v-if="extension.status === 'Chờ duyệt'"
                    href="#"
                    @click.prevent="openConfirmRejectPopup(extension.id)"
                    class="button gray reject"
                >
                    <i class="sl sl-icon-close"></i> Hủy bỏ
                </a>
                <a
                    v-if="extension.status === 'Đã duyệt'"
                    href="#"
                    @click.prevent="downloadExtensionPdf(contract.id)"
                    class="button gray approve"
                >
                    <i class="im im-icon-File-Download"></i> Tải phụ lục
                </a>
                <a href="#" @click.prevent="openExtensionDetailPopup(extension)" class="button gray approve popup-with-zoom-anim">
                    <i class="im im-icon-Folder-Bookmark"></i> Xem chi tiết
                </a>
            </div>
        </li>
    </ul>
</template>

<script setup>
import Swal from 'sweetalert2';
import { useToast } from 'vue-toastification';

const { $api } = useNuxtApp();
const toast = useToast();

const props = defineProps({
    contract: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['rejectExtension', 'openPopup']);

const formatDate = dateString => {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const formatCurrency = amount => new Intl.NumberFormat('vi-VN').format(amount);

const getItemClass = status => {
    switch (status) {
        case 'Chờ duyệt':
            return 'pending-booking';
        case 'Đã duyệt':
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
    } else if (status === 'Đã duyệt') {
        statusClass += ' approved';
    } else if (status === 'Từ chối' || status === 'Huỷ bỏ') {
        statusClass += ' canceled';
    }
    return statusClass;
};

const openConfirmRejectPopup = async id => {
    const result = await Swal.fire({
        title: `Xác nhận hủy bỏ gia hạn (phụ lục)`,
        text: `Bạn có chắc chắn muốn hủy bỏ gia hạn (phụ lục) này của hợp đồng?`,
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
        emit('rejectExtension', id);
    }
};

const openExtensionDetailPopup = async extension => {
    await Swal.fire({
        title: `Chi tiết phụ lục hợp đồng #${extension.id}`,
        html: `
            <div style="text-align: left;">
                <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; background-color: #f9f9f9;">
                    ${extension.content || 'Không có nội dung phụ lục.'}
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

const downloadExtensionPdf = async id => {
    try {
        const response = await $api(`/extensions-contract/${id}/download-pdf`, { method: 'GET' });
        const fileUrl = response.data.file_url;
        window.open(fileUrl, '_blank');
    } catch (error) {
        const data = error.response?._data;
        if (data?.error) {
            toast.error(data.error);
        } else {
            toast.error('Đã có lỗi xảy ra khi tải PDF phụ lục.');
        }
    }
};
</script>

<style>
.contract-document {
    padding: 8px 16px;
}
</style>
