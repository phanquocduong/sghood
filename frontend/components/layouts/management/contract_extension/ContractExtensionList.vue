<template>
    <!-- Tiêu đề danh sách yêu cầu gia hạn -->
    <h4>Quản lý yêu cầu gia hạn</h4>

    <!-- Hiển thị spinner khi đang loading -->
    <Loading :is-loading="isLoading" />

    <!-- Danh sách các yêu cầu gia hạn -->
    <ul>
        <li v-for="extension in extensions" :key="extension.id" :class="getItemClass(extension.status)">
            <div class="list-box-listing bookings">
                <div class="list-box-listing-content">
                    <div class="inner">
                        <!-- Mã yêu cầu và trạng thái -->
                        <h3>
                            Mã #{{ extension.id }}
                            <span :class="getStatusClass(extension.status)">
                                {{ extension.status }}
                            </span>
                        </h3>
                        <!-- Thông tin hợp đồng liên quan -->
                        <div class="inner-booking-list">
                            <h5>Hợp đồng:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">#{{ extension.contract_id }}</li>
                            </ul>
                        </div>
                        <!-- Ngày gia hạn đến -->
                        <div class="inner-booking-list">
                            <h5>Gia hạn đến:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDate(extension.new_end_date) }}</li>
                            </ul>
                        </div>
                        <!-- Giá thuê mới -->
                        <div class="inner-booking-list">
                            <h5>Giá thuê mới:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatPrice(extension.new_rental_price) }}</li>
                            </ul>
                        </div>
                        <!-- Lý do từ chối (nếu có) -->
                        <div v-if="extension.rejection_reason" class="inner-booking-list">
                            <h5>Lý do từ chối:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ extension.rejection_reason }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Các nút hành động -->
            <div class="buttons-to-right">
                <!-- Nút hủy yêu cầu gia hạn (chỉ hiển thị nếu trạng thái là Chờ duyệt) -->
                <a
                    v-if="extension.status === 'Chờ duyệt'"
                    href="#"
                    @click.prevent="openConfirmCancelPopup(extension.id)"
                    class="button gray reject"
                >
                    <i class="sl sl-icon-close"></i> Hủy bỏ
                </a>
                <!-- Liên kết xem hợp đồng liên quan -->
                <NuxtLink :to="`/quan-ly/hop-dong/${extension.contract_id}`" class="button gray approve" target="_blank">
                    <i class="im im-icon-File-Download"></i> Xem hợp đồng
                </NuxtLink>
                <!-- Nút xem chi tiết yêu cầu gia hạn -->
                <a href="#" @click.prevent="openExtensionDetailPopup(extension)" class="button gray approve">
                    <i class="im im-icon-Folder-Bookmark"></i> Xem chi tiết
                </a>
            </div>
        </li>
        <!-- Hiển thị thông báo nếu không có yêu cầu gia hạn -->
        <div v-if="!extensions?.length" class="col-md-12 text-center">
            <p>Chưa có yêu cầu gia hạn nào.</p>
        </div>
    </ul>
</template>

<script setup>
import Swal from 'sweetalert2';
import { useFormatPrice } from '~/composables/useFormatPrice';
import { useFormatDate } from '~/composables/useFormatDate';

// Sử dụng composable để định dạng giá và ngày
const { formatPrice } = useFormatPrice();
const { formatDate } = useFormatDate();

// Định nghĩa props cho component
const props = defineProps({
    extensions: {
        type: Array,
        required: true // Danh sách yêu cầu gia hạn
    },
    isLoading: {
        type: Boolean,
        required: true // Trạng thái loading
    }
});

// Định nghĩa sự kiện emit
const emit = defineEmits(['cancelExtension']);

// Xác định class cho item dựa trên trạng thái
const getItemClass = status => {
    switch (status) {
        case 'Chờ duyệt':
            return 'pending-booking'; // Class cho trạng thái chờ duyệt
        case 'Hoạt động':
            return 'approved-booking'; // Class cho trạng thái hoạt động
        case 'Từ chối':
        case 'Huỷ bỏ':
            return 'canceled-booking'; // Class cho trạng thái từ chối hoặc hủy
        default:
            return '';
    }
};

// Xác định class cho trạng thái hiển thị
const getStatusClass = status => {
    let statusClass = 'booking-status';
    if (status === 'Chờ duyệt') {
        statusClass += ' pending'; // Thêm class pending cho trạng thái chờ duyệt
    } else if (status === 'Hoạt động') {
        statusClass += ' approved'; // Thêm class approved cho trạng thái hoạt động
    } else if (status === 'Từ chối' || status === 'Huỷ bỏ') {
        statusClass += ' canceled'; // Thêm class canceled cho trạng thái từ chối hoặc hủy
    }
    return statusClass;
};

// Mở popup xác nhận hủy yêu cầu gia hạn
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
        emit('cancelExtension', id); // Emit sự kiện hủy yêu cầu gia hạn
    }
};

// Mở popup hiển thị chi tiết yêu cầu gia hạn
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
/* Style cho tài liệu hợp đồng trong popup */
.contract-document {
    padding: 8px 16px;
}
</style>
