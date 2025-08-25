<template>
    <h4>Phụ lục hợp đồng</h4>
    <!-- Tiêu đề danh sách phụ lục -->

    <ul>
        <!-- Hiển thị danh sách phụ lục hợp đồng -->
        <li v-for="extension in contract?.active_extensions" :key="extension.id" class="approved-booking">
            <div class="list-box-listing bookings">
                <div class="list-box-listing-content">
                    <div class="inner">
                        <h3>
                            Mã #{{ extension.id }}
                            <span class="booking-status approved">
                                {{ extension.status }}
                                <!-- Trạng thái phụ lục -->
                            </span>
                        </h3>
                        <div class="inner-booking-list">
                            <h5>Gia hạn đến:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDate(extension.new_end_date) }}</li>
                                <!-- Ngày kết thúc mới -->
                            </ul>
                        </div>
                        <div class="inner-booking-list">
                            <h5>Giá thuê mới:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatPrice(extension.new_rental_price) }}</li>
                                <!-- Giá thuê mới -->
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="buttons-to-right">
                <!-- Nút xem chi tiết phụ lục -->
                <a href="#" @click.prevent="openExtensionDetailPopup(extension)" class="button gray approve">
                    <i class="im im-icon-Folder-Bookmark"></i> Xem chi tiết
                </a>
            </div>
        </li>
    </ul>
</template>

<script setup>
import Swal from 'sweetalert2';
import { useFormatPrice } from '~/composables/useFormatPrice';
import { useFormatDate } from '~/composables/useFormatDate';

// Lấy composables
const { formatPrice } = useFormatPrice(); // Hàm định dạng giá tiền
const { formatDate } = useFormatDate(); // Hàm định dạng ngày

// Định nghĩa props
const props = defineProps({
    contract: {
        type: Object,
        required: true // Thông tin hợp đồng
    }
});

// Định nghĩa emits
const emit = defineEmits(['rejectExtension', 'openPopup']);

// Mở popup chi tiết phụ lục hợp đồng
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
/* CSS cho nội dung hợp đồng */
.contract-document {
    padding: 8px 16px;
}

/* CSS cho tiêu đề trong danh sách phụ lục */
.inner-booking-list h5 {
    font-size: 14px !important;
}
</style>
