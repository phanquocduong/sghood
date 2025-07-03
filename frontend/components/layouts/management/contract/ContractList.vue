<template>
    <h4>Quản lý hợp đồng</h4>

    <!-- Hiển thị loading spinner -->
    <div v-if="isLoading" class="loading-overlay">
        <div class="spinner"></div>
        <p>Đang tải...</p>
    </div>

    <ul v-else>
        <li v-for="item in items" :key="item.id" :class="getItemClass(item.status)">
            <div class="list-box-listing bookings">
                <div class="list-box-listing-img">
                    <img :src="config.public.baseUrl + item.room_image" alt="" />
                </div>
                <div class="list-box-listing-content">
                    <div class="inner">
                        <h3>
                            {{ item.room_name }} - {{ item.motel_name }}
                            <span :class="getStatusClass(item.status)">{{ item.status }}</span>
                        </h3>
                        <div class="inner-booking-list">
                            <h5>Ngày bắt đầu:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDate(item.start_date) }}</li>
                            </ul>
                        </div>
                        <div class="inner-booking-list">
                            <h5>Ngày kết thúc:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDate(item.end_date) }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="buttons-to-right">
                <a
                    v-if="item.status === 'Chờ xác nhận'"
                    href="#"
                    @click.prevent="openConfirmRejectPopup(item.id)"
                    class="button gray reject"
                >
                    <i class="sl sl-icon-close"></i> Hủy bỏ
                </a>
                <a v-if="item.status === 'Hoạt động'" href="#" class="button gray approve" @click.prevent="downloadPdf(item.id)">
                    <i class="im im-icon-File-Download"></i> Tải hợp đồng
                </a>
                <NuxtLink
                    v-if="item.status === 'Chờ thanh toán tiền cọc'"
                    :to="`/quan-ly/hoa-don/${item.invoice_id}/thanh-toan`"
                    class="button gray approve popup-with-zoom-anim"
                >
                    <i class="im im-icon-Folder-Bookmark"></i> Thanh toán tiền cọc
                </NuxtLink>
                <NuxtLink v-else :to="`/quan-ly/hop-dong/${item.id}`" class="button gray approve popup-with-zoom-anim">
                    <i class="im im-icon-Folder-Bookmark"></i> {{ getActText(item.status) }}
                </NuxtLink>
            </div>
        </li>
        <div v-if="!items.length" class="col-md-12 text-center">
            <p>Chưa có hợp đồng nào.</p>
        </div>
    </ul>
</template>

<script setup>
import Swal from 'sweetalert2';
import { useToast } from 'vue-toastification';

const { $api } = useNuxtApp();
const toast = useToast();

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

const emit = defineEmits(['rejectItem', 'openPopup']);

const formatDate = dateString => {
    const date = new Date(dateString);
    return date.toISOString().split('T')[0];
};

const getItemClass = status => {
    switch (status) {
        case 'Chờ xác nhận':
        case 'Chờ duyệt':
        case 'Chờ chỉnh sửa':
        case 'Chờ ký':
        case 'Chờ thanh toán tiền cọc':
            return 'pending-booking';
        case 'Hoạt động':
            return 'approved-booking';
        case 'Kết thúc':
        case 'Huỷ bỏ':
            return 'canceled-booking';
        default:
            return '';
    }
};

const getActText = status => {
    switch (status) {
        case 'Chờ xác nhận':
            return 'Hoàn thiện thông tin';
        case 'Chờ chỉnh sửa':
            return 'Chỉnh sửa thông tin';
        case 'Chờ ký':
            return 'Ký hợp đồng';
        case 'Chờ duyệt':
        case 'Hoạt động':
        case 'Kết thúc':
        case 'Huỷ bỏ':
            return 'Xem chi tiết';
        default:
            return '';
    }
};

const getStatusClass = status => {
    let statusClass = 'booking-status';
    if (status === 'Chờ xác nhận') {
        statusClass += ' pending';
    }
    return statusClass;
};

const openConfirmRejectPopup = async id => {
    const result = await Swal.fire({
        title: `Xác nhận hủy hợp đồng`,
        text: `Bạn có chắc chắn muốn hủy hợp đồng này?`,
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
        emit('rejectItem', { id });
    }
};

const downloadPdf = async id => {
    try {
        const response = await $api(`/contracts/${id}/download-pdf`, { method: 'GET' });
        const fileUrl = response.data.file_url;
        window.open(fileUrl, '_blank');
    } catch (error) {
        const data = error.response?._data;
        if (data?.error) {
            toast.error(data.error);
        } else {
            toast.error('Đã có lỗi xảy ra khi tải PDF.');
        }
    }
};
</script>

<style scoped>
.bookings .list-box-listing-img {
    max-width: 150px;
    max-height: none;
    border-radius: 4px;
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #f91942;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

p {
    color: #333;
    margin-top: 10px;
    font-size: 16px;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>
