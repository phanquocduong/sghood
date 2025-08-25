<template>
    <!-- Tiêu đề và nút thêm người ở cùng -->
    <h4>
        <div style="display: flex; align-items: center; justify-content: space-between">
            Quản lý hợp đồng - Người ở cùng
            <!-- Hiển thị số lượng người ở hiện tại so với tối đa -->
            <span>(Hiện tại: {{ currentOccupants }} / {{ maxOccupants }})</span>
            <!-- Nút thêm người ở cùng, vô hiệu hóa nếu đã đạt số lượng tối đa -->
            <a
                href="#"
                @click.prevent="openAddTenantPopup"
                class="button border with-icon"
                :class="{ disabled: currentOccupants >= maxOccupants }"
            >
                Thêm người ở cùng <i class="sl sl-icon-plus"></i>
            </a>
        </div>
    </h4>

    <!-- Component hiển thị trạng thái loading -->
    <Loading :is-loading="isLoading" />
    <!-- Modal để thêm người ở cùng -->
    <AddTenantModal ref="addTenantModal" :contract-id="contractId" @add-tenant="handleAddTenant" @close="resetForm" />

    <!-- Danh sách người ở cùng -->
    <ul>
        <li v-for="item in tenants" :key="item.id" :class="getItemClass(item.status)">
            <div class="list-box-listing bookings">
                <div class="list-box-listing-content">
                    <div class="inner">
                        <!-- Tên và trạng thái của người ở cùng -->
                        <h3>
                            {{ item.name }}
                            <span :class="getStatusClass(item.status)">{{ item.status }}</span>
                        </h3>
                        <!-- Thông tin số điện thoại -->
                        <div class="inner-booking-list">
                            <h5>Số điện thoại:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ item.phone }}</li>
                            </ul>
                        </div>
                        <!-- Thông tin email (nếu có) -->
                        <div v-if="item.email" class="inner-booking-list">
                            <h5>Email:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ item.email }}</li>
                            </ul>
                        </div>
                        <!-- Mối quan hệ với người thuê chính (nếu có) -->
                        <div v-if="item.relation_with_primary" class="inner-booking-list">
                            <h5>Mối quan hệ với người thuê chính:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ item.relation_with_primary }}</li>
                            </ul>
                        </div>
                        <!-- Lý do từ chối (nếu trạng thái là Từ chối) -->
                        <div v-if="item.rejection_reason && item.status === 'Từ chối'" class="inner-booking-list">
                            <h5>Lý do từ chối:</h5>
                            <ul class="booking-list">
                                <li>{{ item.rejection_reason }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Các nút hành động -->
            <div class="buttons-to-right">
                <!-- Nút xem chi tiết -->
                <a href="#" @click.prevent="openDetailsPopup(item)" class="button gray approve">
                    <i class="sl sl-icon-magnifier"></i> Chi tiết
                </a>
                <!-- Nút hủy bỏ (hiển thị nếu trạng thái là Chờ duyệt hoặc Đã duyệt) -->
                <a
                    v-if="item.status === 'Chờ duyệt' || item.status === 'Đã duyệt'"
                    href="#"
                    @click.prevent="openConfirmRejectPopup(item.id)"
                    class="button gray reject"
                >
                    <i class="sl sl-icon-close"></i> Hủy bỏ
                </a>
                <!-- Nút xác nhận vào ở (hiển thị nếu trạng thái là Đã duyệt) -->
                <a v-if="item.status === 'Đã duyệt'" href="#" @click.prevent="openConfirmMoveInPopup(item.id)" class="button gray approve">
                    <i class="sl sl-icon-check"></i> Xác nhận chính thức vào ở
                </a>
            </div>
        </li>
        <!-- Hiển thị thông báo nếu không có người ở cùng -->
        <div v-if="!tenants.length" class="col-md-12 text-center">
            <p>Chưa có người ở cùng nào.</p>
        </div>
    </ul>
</template>

<script setup>
import Swal from 'sweetalert2';
import { ref, computed } from 'vue';
import { useFormatDate } from '~/composables/useFormatDate';

const { formatDate } = useFormatDate(); // Sử dụng composable để định dạng ngày

// Định nghĩa props cho component
const props = defineProps({
    tenants: {
        type: Array,
        required: true // Danh sách người ở cùng
    },
    isLoading: {
        type: Boolean,
        required: true // Trạng thái loading
    },
    contractId: {
        type: [Number, String],
        required: true // ID của hợp đồng
    },
    maxOccupants: {
        type: Number,
        default: 0 // Số lượng người ở tối đa
    }
});

// Định nghĩa các sự kiện emit
const emit = defineEmits(['cancelTenant', 'addTenant', 'confirmTenant']);
const addTenantModal = ref(null); // Tham chiếu đến modal thêm người ở cùng

// Tính số lượng người ở hiện tại (bao gồm người thuê chính)
const currentOccupants = computed(() => {
    return props.tenants.filter(item => item.status === 'Đang ở').length + 1; // +1 cho người thuê chính
});

// Xác định class cho item dựa trên trạng thái
const getItemClass = status => {
    switch (status) {
        case 'Chờ duyệt':
            return 'pending-booking'; // Class cho trạng thái chờ duyệt
        case 'Đã duyệt':
        case 'Đang ở':
            return 'approved-booking'; // Class cho trạng thái đã duyệt hoặc đang ở
        case 'Từ chối':
        case 'Huỷ bỏ':
        case 'Đã rời đi':
            return 'canceled-booking'; // Class cho trạng thái bị hủy hoặc từ chối
        default:
            return '';
    }
};

// Xác định class cho trạng thái hiển thị
const getStatusClass = status => {
    let statusClass = 'booking-status';
    if (status === 'Chờ duyệt') {
        statusClass += ' pending'; // Thêm class pending cho trạng thái chờ duyệt
    }
    return statusClass;
};

// Mở popup xác nhận hủy người ở cùng
const openConfirmRejectPopup = async id => {
    const result = await Swal.fire({
        title: 'Xác nhận hủy người ở cùng',
        text: 'Bạn chắc chắn muốn hủy đăng ký người ở cùng này?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xác nhận',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#f91942',
        cancelButtonColor: '#e0e0e0'
    });

    if (result.isConfirmed) {
        emit('cancelTenant', id); // Emit sự kiện hủy người ở cùng
    }
};

// Mở popup xác nhận vào ở chính thức
const openConfirmMoveInPopup = async id => {
    const result = await Swal.fire({
        title: 'Xác nhận vào ở chính thức',
        text: 'Bạn chắc chắn muốn xác nhận người này đã vào ở chính thức?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Xác nhận',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#667eea',
        cancelButtonColor: '#e0e0e0'
    });

    if (result.isConfirmed) {
        emit('confirmTenant', id); // Emit sự kiện xác nhận vào ở
    }
};

// Mở popup hiển thị chi tiết người ở cùng
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

// Mở popup thêm người ở cùng
const openAddTenantPopup = () => {
    if (currentOccupants.value >= props.maxOccupants) {
        // Hiển thị thông báo nếu số lượng người ở đã đạt tối đa
        Swal.fire({
            title: 'Không thể thêm',
            text: 'Số lượng người ở đã đạt tối đa cho phòng này.',
            icon: 'warning',
            confirmButtonText: 'Đóng'
        });
        return;
    }
    if (addTenantModal.value) {
        addTenantModal.value.openModal(); // Mở modal thêm người ở cùng
    }
};

// Xử lý sự kiện thêm người ở cùng
const handleAddTenant = () => {
    emit('addTenant'); // Emit sự kiện thêm người ở cùng
};

// Hàm reset form (được gọi khi đóng modal)
const resetForm = () => {
    // Hiện tại không có logic cụ thể, có thể mở rộng sau này
};
</script>

<style scoped>
.disabled {
    pointer-events: none; /* Vô hiệu hóa tương tác khi nút bị disabled */
    opacity: 0.6; /* Giảm độ mờ để biểu thị trạng thái disabled */
}
</style>
