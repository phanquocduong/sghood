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
                            <span :class="getStatusClass(item.status)">
                                {{ item.status }}
                            </span>
                            <span v-if="item.latest_extension_status" :class="getExtensionStatusClass(item.latest_extension_status)">
                                {{
                                    item.latest_extension_status === 'Chờ duyệt'
                                        ? 'Chờ duyệt gia hạn'
                                        : item.latest_extension_status === 'Đã duyệt'
                                        ? 'Đã gia hạn'
                                        : 'Từ chối gia hạn'
                                }}
                            </span>
                            <span v-if="item.checkout_status" :class="getCheckoutStatusClass(item.checkout_status)">
                                {{
                                    item.checkout_status === 'Chờ kiểm kê'
                                        ? 'Chờ kiểm kê trả phòng'
                                        : item.checkout_status === 'Đã kiểm kê'
                                        ? 'Đã kiểm kê'
                                        : 'Từ chối trả phòng'
                                }}
                            </span>
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
                        <div class="inner-booking-list">
                            <h5>Tiền cọc:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatCurrency(item.deposit_amount) }}đ</li>
                            </ul>
                        </div>
                        <div v-if="item.checkout_date" class="inner-booking-list">
                            <h5>Ngày rời phòng:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ formatDate(item.checkout_date) }}</li>
                            </ul>
                        </div>
                        <div v-if="item.checkout_deposit_refunded !== null" class="inner-booking-list">
                            <h5>Trạng thái hoàn tiền cọc:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ item.checkout_deposit_refunded ? 'Đã hoàn' : 'Chưa hoàn' }}</li>
                            </ul>
                        </div>
                        <div v-if="item.checkout_has_left !== null" class="inner-booking-list">
                            <h5>Trạng thái rời phòng:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ item.checkout_has_left ? 'Đã rời' : 'Chưa rời' }}</li>
                            </ul>
                        </div>
                        <div v-if="item.checkout_note" class="inner-booking-list">
                            <h5>Ghi chú kiểm kê:</h5>
                            <ul class="booking-list">
                                <li class="highlighted">{{ item.checkout_note || 'Không có' }}</li>
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
                <NuxtLink :to="`/quan-ly/hop-dong/${item.id}`" class="button gray approve popup-with-zoom-anim">
                    <i class="im im-icon-Folder-Bookmark"></i> {{ getActText(item.status) }}
                </NuxtLink>
                <a
                    v-if="
                        item.status === 'Hoạt động' &&
                        isNearExpiration(item.end_date) &&
                        item.latest_extension_status !== 'Chờ duyệt' &&
                        !item.checkout_status
                    "
                    href="#"
                    @click.prevent="openConfirmExtendPopup(item)"
                    class="button"
                >
                    <i class="im im-icon-Clock-Forward"></i> Gia hạn
                </a>
                <a
                    v-if="
                        item.status === 'Hoạt động' &&
                        isNearExpiration(item.end_date) &&
                        item.latest_extension_status !== 'Chờ duyệt' &&
                        !item.checkout_status
                    "
                    href="#"
                    @click.prevent="openReturnModal(item)"
                    class="button"
                >
                    <i class="sl sl-icon-logout"></i> Trả phòng
                </a>
            </div>
        </li>
        <div v-if="!items.length" class="col-md-12 text-center">
            <p>Chưa có hợp đồng nào.</p>
        </div>
    </ul>

    <!-- Modal trả phòng -->
    <div v-if="showReturnModal" class="custom-modal-overlay">
        <div class="custom-modal">
            <div class="custom-modal-header">
                <h3>Xác nhận trả phòng</h3>
                <button class="close-button" @click="closeReturnModal">×</button>
            </div>
            <div class="custom-modal-body">
                <div class="modal-content">
                    <p><strong>Số hợp đồng:</strong> {{ selectedContract.id }}</p>
                    <p><strong>Phòng:</strong> {{ selectedContract.room_name }} - {{ selectedContract.motel_name }}</p>
                    <p><strong>Ngày kết thúc:</strong> {{ formatDate(selectedContract.end_date) }}</p>
                    <p><strong>Tiền cọc:</strong> {{ formatCurrency(selectedContract.deposit_amount) }}đ</p>
                    <hr />
                    <h5>Thông tin trả phòng</h5>
                    <div class="form-group">
                        <label for="check_out_date">Ngày trả phòng:</label>
                        <input
                            id="check_out_date"
                            v-model="returnForm.check_out_date"
                            type="date"
                            :min="today"
                            class="form-control"
                            required
                        />
                    </div>
                    <h5>Thông tin tài khoản ngân hàng</h5>
                    <div class="form-group">
                        <label for="bank_name">Tên ngân hàng:</label>
                        <input
                            id="bank_name"
                            v-model="returnForm.bank_name"
                            type="text"
                            class="form-control"
                            placeholder="Tên ngân hàng"
                            required
                        />
                    </div>
                    <div class="form-group">
                        <label for="account_number">Số tài khoản:</label>
                        <input
                            id="account_number"
                            v-model="returnForm.account_number"
                            type="text"
                            class="form-control"
                            placeholder="Số tài khoản"
                            required
                        />
                    </div>
                    <div class="form-group">
                        <label for="account_holder">Tên chủ tài khoản:</label>
                        <input
                            id="account_holder"
                            v-model="returnForm.account_holder"
                            type="text"
                            class="form-control"
                            placeholder="Tên chủ tài khoản"
                            required
                        />
                    </div>
                    <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button class="button gray" @click="closeReturnModal">Hủy</button>
                <button class="button confirm" @click="submitReturn">Xác nhận trả phòng</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
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

const emit = defineEmits(['rejectItem', 'openPopup', 'extendContract', 'returnContract']);

const showReturnModal = ref(false);
const selectedContract = ref({});
const returnForm = ref({
    check_out_date: '',
    bank_name: '',
    account_number: '',
    account_holder: ''
});
const errorMessage = ref('');
const today = new Date().toISOString().split('T')[0];

const formatDate = dateString => {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const formatCurrency = amount => new Intl.NumberFormat('vi-VN').format(amount);

const isNearExpiration = endDate => {
    const today = new Date();
    const end = new Date(endDate);
    const diffInDays = Math.ceil((end - today) / (1000 * 60 * 60 * 24));
    return diffInDays <= 15 && diffInDays >= 0;
};

const getItemClass = status => {
    switch (status) {
        case 'Chờ xác nhận':
        case 'Chờ duyệt':
        case 'Chờ chỉnh sửa':
        case 'Chờ ký':
        case 'Chờ thanh toán tiền cọc':
        case 'Chờ hoàn tiền':
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
        case 'Chờ hoàn tiền':
            return 'Xem chi tiết';
        default:
            return '';
    }
};

const getStatusClass = status => {
    let statusClass = 'booking-status';
    if (status === 'Chờ xác nhận' || status === 'Chờ duyệt' || status === 'Chờ chỉnh sửa' || status === 'Chờ ký') {
        statusClass += ' pending';
    } else if (status === 'Hoạt động') {
        statusClass += ' approved';
    } else if (status === 'Kết thúc' || status === 'Huỷ bỏ') {
        statusClass += ' canceled';
    }
    return statusClass;
};

const getExtensionStatusClass = extensionStatus => {
    let statusClass = 'booking-status';
    if (extensionStatus === 'Chờ duyệt') {
        statusClass += ' pending extension-status';
    } else if (extensionStatus === 'Đã duyệt') {
        statusClass += ' approved';
    } else {
        statusClass += ' canceled extension-status';
    }
    return statusClass;
};

const getCheckoutStatusClass = checkoutStatus => {
    let statusClass = 'booking-status';
    if (checkoutStatus === 'Chờ kiểm kê') {
        statusClass += ' pending checkout-status';
    } else if (checkoutStatus === 'Đã kiểm kê') {
        statusClass += ' approved';
    } else {
        statusClass += ' canceled checkout-status';
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
        emit('rejectItem', id);
    }
};

const openConfirmExtendPopup = async contract => {
    const currentEndDate = new Date(contract.end_date);
    const newEndDate = new Date(currentEndDate).setMonth(currentEndDate.getMonth() + 6);
    const formattedNewEndDate = formatDate(newEndDate);

    const result = await Swal.fire({
        title: `Xác nhận gia hạn hợp đồng`,
        html: `
            <div style="text-align: left;">
                <p><strong>Số hợp đồng:</strong>: ${contract.id}</p>
                <p><strong>Phòng:</strong>: ${contract.room_name} - ${contract.motel_name}</p>
                <p><strong>Ngày kết thúc hiện tại:</strong>: ${formatDate(contract.end_date)}</p>
                <p><strong>Ngày kết thúc mới:</strong>: ${formattedNewEndDate}</p>
                <p><strong>Giá thuê phòng mới:</strong>: ${contract?.room_price.toLocaleString('vi-VN', {
                    style: 'currency',
                    currency: 'VND'
                })}</p>
                <p>Các điều khoản khác của hợp đồng gốc vẫn giữ nguyên hiệu lực.</p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Xác nhận gia hạn',
        cancelButtonText: 'Hủy',
        confirmButtonColor: '#64bc36',
        cancelButtonColor: '#e0e0e0',
        customClass: {
            confirmButton: 'button',
            cancelButton: 'button gray'
        }
    });

    if (result.isConfirmed) {
        emit('extendContract', contract.id);
    }
};

const openReturnModal = contract => {
    selectedContract.value = contract;
    returnForm.value = {
        check_out_date: '',
        bank_name: '',
        account_number: '',
        account_holder: ''
    };
    errorMessage.value = '';
    showReturnModal.value = true;
};

const closeReturnModal = () => {
    showReturnModal.value = false;
    selectedContract.value = {};
    returnForm.value = {
        check_out_date: '',
        bank_name: '',
        account_number: '',
        account_holder: ''
    };
    errorMessage.value = '';
};

const submitReturn = async () => {
    if (
        !returnForm.value.check_out_date ||
        !returnForm.value.bank_name ||
        !returnForm.value.account_number ||
        !returnForm.value.account_holder
    ) {
        errorMessage.value = 'Vui lòng nhập đầy đủ thông tin trả phòng và tài khoản ngân hàng.';
        return;
    }

    try {
        emit('returnContract', selectedContract.value.id, returnForm.value);
        closeReturnModal();
    } catch (error) {
        // Error handling is managed in the parent component
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

<style>
.custom-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.custom-modal {
    background: white;
    border-radius: 8px;
    width: 40em;
    max-width: 90%;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    margin-top: 5%;
}

.custom-modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.custom-modal-header h3 {
    margin: 0;
    font-size: 1.5em;
    color: #333;
}

.close-button {
    background: none;
    border: none;
    font-size: 1.5em;
    cursor: pointer;
    color: #333;
}

.custom-modal-body {
    padding: 20px;
    max-height: 60vh;
    overflow-y: auto;
}

.modal-content p {
    margin: 10px 0;
    text-align: left;
}

.modal-content hr {
    margin: 20px 0;
    border: 0;
    border-top: 1px solid #e0e0e0;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 8px;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    font-size: 1em;
}

.error-message {
    color: #f91942;
    font-size: 0.9em;
    margin-top: 10px;
}

.custom-modal-footer {
    padding: 15px 20px;
    border-top: 1px solid #e0e0e0;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.custom-modal-footer .button {
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
}

.custom-modal-footer .button.gray {
    background-color: #e0e0e0;
    color: #333;
}

.custom-modal-footer .button.confirm {
    background-color: #28a745;
    color: white;
}

.custom-modal-footer .button.confirm:hover {
    background-color: #218838;
}

.custom-modal-footer .button.gray:hover {
    background-color: #d0d0d0;
}

/* Existing styles */
.swal2-popup.swal2-modal {
    width: 50em !important;
}

.swal2-actions .swal2-confirm {
    background-color: #64bc36 !important;
}

.swal2-actions .swal2-confirm:hover {
    background-color: #68cf30 !important;
}

.booking-status.pending.extension-status,
.booking-status.pending.checkout-status {
    background-color: #61b2db !important;
}

.booking-status.canceled.extension-status,
.booking-status.canceled.checkout-status {
    background-color: #ee3535 !important;
}

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
