<template>
    <h4>Phụ lục hợp đồng</h4>

    <ul>
        <li v-for="extension in contract?.active_extensions" :key="extension.id" class="approved-booking">
            <div class="list-box-listing bookings">
                <div class="list-box-listing-content">
                    <div class="inner">
                        <h3>
                            Mã #{{ extension.id }}
                            <span class="booking-status approved">
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
                    </div>
                </div>
            </div>
            <div class="buttons-to-right">
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
</script>

<style>
.contract-document {
    padding: 8px 16px;
}
</style>
