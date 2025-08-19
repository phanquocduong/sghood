<template>
    <h4>
        <div style="display: flex; align-items: center; justify-content: space-between">
            Quản lý hợp đồng - Người ở cùng
            <a href="#" class="button border with-icon">Thêm người ở cùng <i class="sl sl-icon-plus"></i></a>
        </div>
    </h4>

    <Loading :is-loading="isLoading" />

    <ul>
        <li v-for="item in tenants" :key="item.id" :class="getItemClass(item.status)">
            <div class="list-box-listing bookings">
                <div class="list-box-listing-content">
                    <div class="inner">
                        <h3>
                            {{ item.name }}
                            <span :class="getStatusClass(item.status)">{{ item.status }}</span>
                        </h3>
                        <div class="inner-booking-list">
                            <h5>Số điện thoại:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ item.phone }}</li>
                            </ul>
                        </div>
                        <div v-if="item.email" class="inner-booking-list">
                            <h5>Email:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ item.email }}</li>
                            </ul>
                        </div>
                        <div v-if="item.relation_with_primary" class="inner-booking-list">
                            <h5>Mối quan hệ với người thuê chính:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ item.relation_with_primary }}</li>
                            </ul>
                        </div>
                        <div v-if="item.rejection_reason && item.status === 'Từ chối'" class="inner-booking-list">
                            <h5>Lý do từ chối:</h5>
                            <ul class="booking-list">
                                <li>{{ item.rejection_reason }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="buttons-to-right">
                <a href="#" @click.prevent="openDetailsPopup(item)" class="button gray approve">
                    <i class="sl sl-icon-magnifier"></i> Chi tiết
                </a>
                <a
                    v-if="item.status === 'Chờ duyệt' || item.status === 'Đã duyệt'"
                    href="#"
                    @click.prevent="openConfirmRejectPopup(item.id)"
                    class="button gray reject"
                >
                    <i class="sl sl-icon-close"></i> Hủy bỏ
                </a>
            </div>
        </li>
        <div v-if="!tenants.length" class="col-md-12 text-center">
            <p>Chưa có người ở cùng nào.</p>
        </div>
    </ul>
</template>

<script setup>
import Swal from 'sweetalert2';
import { useFormatDate } from '~/composables/useFormatDate';

const { formatDate } = useFormatDate();
const props = defineProps({
    tenants: {
        type: Array,
        required: true
    },
    isLoading: {
        type: Boolean,
        required: true
    }
});

const emit = defineEmits(['cancelTenant']);

const getItemClass = status => {
    switch (status) {
        case 'Chờ duyệt':
            return 'pending-booking';
        case 'Đã duyệt':
        case 'Đang ở':
            return 'approved-booking';
        case 'Từ chối':
        case 'Huỷ bỏ':
        case 'Đã rời đi':
            return 'canceled-booking';
        default:
            return '';
    }
};

const getStatusClass = status => {
    let statusClass = 'booking-status';
    if (status === 'Chờ duyệt') {
        statusClass += ' pending';
    }
    return statusClass;
};

const openConfirmRejectPopup = async id => {
    const result = await Swal.fire({
        title: 'Xác nhận hủy người ở cùng',
        text: 'Bạn có chắc chắn muốn hủy đăng ký người ở cùng này?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xác nhận',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#f91942',
        cancelButtonColor: '#e0e0e0'
    });

    if (result.isConfirmed) {
        emit('cancelTenant', id);
    }
};

const openDetailsPopup = async tenant => {
    const htmlContent = `
        <div style="text-align: left;">
            <h3 style="margin-bottom: 20px;">Thông tin chi tiết người ở cùng</h3>
            <p><strong>Tên:</strong> ${tenant.name}</p>
            <p><strong>Số điện thoại:</strong> ${tenant.phone}</p>
            ${tenant.email ? `<p><strong>Email:</strong> ${tenant.email}</p>` : ''}
            ${tenant.gender ? `<p><strong>Giới tính:</strong> ${tenant.gender}</p>` : ''}
            ${tenant.birthdate ? `<p><strong>Ngày sinh:</strong> ${formatDate(tenant.birthdate)}</p>` : ''}
            ${tenant.address ? `<p><strong>Địa chỉ:</strong> ${tenant.address}</p>` : ''}
            ${
                tenant.relation_with_primary
                    ? `<p><strong>Mối quan hệ với người thuê chính:</strong> ${tenant.relation_with_primary}</p>`
                    : ''
            }
            <p><strong>Trạng thái:</strong> ${tenant.status}</p>
            ${
                tenant.rejection_reason && tenant.status === 'Từ chối'
                    ? `<p><strong>Lý do từ chối:</strong> ${tenant.rejection_reason}</p>`
                    : ''
            }
            <p><strong>Ngày tạo:</strong> ${new Date(tenant.created_at).toLocaleString('vi-VN')}</p>
        </div>
    `;

    await Swal.fire({
        title: 'Chi tiết người ở cùng',
        html: htmlContent,
        icon: 'info',
        confirmButtonText: 'Đóng',
        confirmButtonColor: '#667eea',
        width: '600px',
        customClass: {
            popup: 'swal2-popup-custom'
        }
    });
};
</script>

<style></style>
