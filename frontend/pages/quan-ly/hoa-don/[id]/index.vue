<template>
    <!-- Invoice -->
    <div v-if="invoice" id="invoice">
        <!-- Header Section -->
        <div class="row">
            <div class="col-md-6">
                <div id="logo">
                    <img src="/images/sghood_logo1.png" alt="SGHood Logo" />
                </div>
                <div class="company-details">
                    <h3><strong>SGHood</strong></h3>
                    <p>SĐT: {{ config.contact_phone }}</p>
                    <p>Email: {{ config.contact_email }}</p>
                    <p>Website: {{ config.website_address }}</p>
                    <p>Địa chỉ: {{ config.office_address }}</p>
                </div>
            </div>

            <div class="col-md-6" id="details">
                <h2>PHIẾU THU TIỀN</h2>
                <div class="invoice-meta">
                    <p>
                        <strong>{{
                            invoice.type === 'Đặt cọc'
                                ? 'ĐẶT CỌC HỢP ĐỒNG'
                                : `PHÒNG TRỌ THÁNG ${invoice.month.toString().padStart(2, '0')}/${invoice.year}`
                        }}</strong>
                    </p>
                    <p>
                        <strong
                            ><em>{{ invoice.contract.room.name }}</em></strong
                        >
                    </p>
                </div>
            </div>
        </div>

        <!-- Invoice Information -->
        <div class="row margin-top-20">
            <div class="col-md-6">
                <div class="invoice-info">
                    <p><strong>Mã hóa đơn:</strong> {{ invoice.code }}</p>
                    <p><strong>Ngày tạo:</strong> {{ formatDate(invoice.created_at) }}</p>
                    <p><strong>Khách thuê:</strong> {{ invoice.contract.user.name }}</p>
                    <p><strong>Số điện thoại:</strong> {{ invoice.contract.user.phone || 'N/A' }}</p>
                </div>
            </div>

            <div class="col-md-6" id="details">
                <div class="status-section">
                    <p class="status-label">Trạng thái thanh toán:</p>
                    <p class="status-value" :class="invoice.status === 'Đã trả' ? 'status-paid' : 'status-unpaid'">
                        {{ invoice.status }}
                    </p>
                    <p style="color: #ee3535; font-weight: bold" v-if="invoice.refunded_at">
                        <em>Đã hoàn tiền lúc {{ formatDateTime(invoice.refunded_at) }}</em>
                    </p>
                </div>
            </div>
        </div>

        <!-- Invoice Items Table -->
        <table class="invoice-items-table">
            <thead>
                <tr>
                    <th width="8%"><span>STT</span></th>
                    <th width="60%"><span>Mô tả dịch vụ</span></th>
                    <th width="32%"><span>Thành tiền</span></th>
                </tr>
            </thead>
            <tbody>
                <!-- Room Rent -->
                <tr v-if="invoice.type === 'Hàng tháng'">
                    <td class="item-number">1</td>
                    <td class="item-description">
                        <strong>Tiền phòng</strong>
                        <div class="service-note">Phí thuê phòng hàng tháng</div>
                    </td>
                    <td class="item-amount">{{ formatPrice(invoice.contract.rental_price) }}</td>
                </tr>

                <tr v-else>
                    <td class="item-number">1</td>
                    <td class="item-description">
                        <strong>Tiền đặt cọc</strong>
                        <div class="service-note">Tiền đặt cọc hợp đồng số {{ invoice.contract.id }}</div>
                    </td>
                    <td class="item-amount">{{ formatPrice(invoice.contract.deposit_amount) }}</td>
                </tr>

                <!-- Electricity Fee -->
                <tr v-if="invoice.type === 'Hàng tháng'">
                    <td class="item-number">2</td>
                    <td class="item-description">
                        <strong>Tiền điện</strong>
                        <div class="meter-details">
                            <div class="meter-row">
                                <span>Chỉ số cũ:</span>
                                <span>{{ invoice.prev_electricity_kwh || 0 }} kWh</span>
                            </div>
                            <div class="meter-row">
                                <span>Chỉ số mới:</span>
                                <span>{{ invoice.meter_reading.electricity_kwh }} kWh</span>
                            </div>
                            <div class="meter-row highlight">
                                <span>Điện tiêu thụ:</span>
                                <span>{{ invoice.meter_reading.electricity_kwh - (invoice.prev_electricity_kwh || 0) }} kWh</span>
                            </div>
                            <div class="meter-row">
                                <span>Đơn giá:</span>
                                <span>{{ formatPrice(invoice.contract.room.motel.electricity_fee) }}/kWh</span>
                            </div>
                        </div>
                    </td>
                    <td class="item-amount">{{ formatPrice(invoice.electricity_fee) }}</td>
                </tr>

                <!-- Water Fee -->
                <tr v-if="invoice.type === 'Hàng tháng'">
                    <td class="item-number">3</td>
                    <td class="item-description">
                        <strong>Tiền nước</strong>
                        <div class="meter-details">
                            <div class="meter-row">
                                <span>Chỉ số cũ:</span>
                                <span>{{ invoice.prev_water_m3 || 0 }} m³</span>
                            </div>
                            <div class="meter-row">
                                <span>Chỉ số mới:</span>
                                <span>{{ invoice.meter_reading.water_m3 }} m³</span>
                            </div>
                            <div class="meter-row highlight">
                                <span>Nước tiêu thụ:</span>
                                <span>{{ invoice.meter_reading.water_m3 - (invoice.prev_water_m3 || 0) }} m³</span>
                            </div>
                            <div class="meter-row">
                                <span>Đơn giá:</span>
                                <span>{{ formatPrice(invoice.contract.room.motel.water_fee) }}/m³</span>
                            </div>
                        </div>
                    </td>
                    <td class="item-amount">{{ formatPrice(invoice.water_fee) }}</td>
                </tr>

                <!-- Parking Fee -->
                <tr v-if="invoice.type === 'Hàng tháng'">
                    <td class="item-number">4</td>
                    <td class="item-description">
                        <strong>Tiền gửi xe</strong>
                        <div class="service-note">Phí gửi xe máy hàng tháng</div>
                    </td>
                    <td class="item-amount">{{ formatPrice(invoice.parking_fee) }}</td>
                </tr>

                <!-- Waste Fee -->
                <tr v-if="invoice.type === 'Hàng tháng'">
                    <td class="item-number">5</td>
                    <td class="item-description">
                        <strong>Tiền rác</strong>
                        <div class="service-note">Phí thu gom rác thải hàng tháng</div>
                    </td>
                    <td class="item-amount">{{ formatPrice(invoice.junk_fee) }}</td>
                </tr>

                <!-- TV Cable Fee -->
                <tr v-if="invoice.type === 'Hàng tháng'">
                    <td class="item-number">6</td>
                    <td class="item-description">
                        <strong>Tiền cáp TV</strong>
                        <div class="service-note">Phí truyền hình cáp hàng tháng</div>
                    </td>
                    <td class="item-amount">{{ formatPrice(invoice.internet_fee) }}</td>
                </tr>

                <!-- Internet Fee -->
                <tr v-if="invoice.type === 'Hàng tháng'">
                    <td class="item-number">7</td>
                    <td class="item-description">
                        <strong>Tiền Internet</strong>
                        <div class="service-note">Phí internet băng thông rộng</div>
                    </td>
                    <td class="item-amount">{{ formatPrice(invoice.service_fee) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Total Section -->
        <div class="invoice-total-section">
            <div class="row">
                <div class="col-md-6">
                    <div class="total-in-words">
                        <p><strong>Tổng tiền bằng chữ:</strong></p>
                        <p class="amount-words">{{ convertNumberToWords(invoice.total_amount) }} đồng</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="total-amount-box">
                        <div class="total-row">
                            <span class="total-label">TỔNG CỘNG:</span>
                            <span class="total-amount">{{ formatPrice(invoice.total_amount) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="row">
            <div class="col-md-12 text-center">
                <strong v-if="invoice.type === 'Hàng tháng' && invoice.status === 'Chưa trả'">
                    Lưu ý: Vui lòng thanh toán đúng hạn từ ngày 1 đến ngày 10 hằng tháng. Có thể thanh toán trực tuyến hoặc tiền mặt tại văn
                    phòng quản lý.
                </strong>
                <strong v-if="invoice.type === 'Đặt cọc' && invoice.status === 'Chưa trả'">
                    Lưu ý: Vui lòng thanh toán tiền cọc để kích hoạt hợp đồng. Có thể thanh toán trực tuyến hoặc tiền mặt tại văn phòng quản
                    lý.
                </strong>
                <ul id="footer">
                    <li>
                        <span>{{ config.website_address }}</span>
                    </li>
                    <li>{{ config.contact_email }}</li>
                    <li>{{ config.contact_phone }}</li>
                </ul>
            </div>
        </div>
    </div>

    <div v-else class="loading-section">
        <p>Đang tải thông tin hóa đơn...</p>
    </div>

    <NuxtLink v-if="invoice?.status === 'Chưa trả'" :to="`/quan-ly/hoa-don/${invoice?.code}/thanh-toan`" class="print-button"
        >Thanh toán</NuxtLink
    >
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAppToast } from '~/composables/useToast';
import { useNuxtApp } from '#app';
import { useFormatPrice } from '~/composables/useFormatPrice';
import { useFormatDate } from '~/composables/useFormatDate';

definePageMeta({
    layout: 'blank'
});

const { formatPrice } = useFormatPrice();
const { formatDate, formatDateTime } = useFormatDate();
const { $api } = useNuxtApp();
const config = useState('configs');
const route = useRoute();
const toast = useAppToast();
const invoice = ref(null);

// Convert number to words (Vietnamese)
const convertNumberToWords = number => {
    // This is a simplified version - you might want to use a proper library
    const ones = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
    const tens = ['', '', 'hai mươi', 'ba mươi', 'bốn mươi', 'năm mươi', 'sáu mươi', 'bảy mươi', 'tám mươi', 'chín mươi'];
    const scales = ['', 'nghìn', 'triệu', 'tỷ'];

    if (number === 0) return 'không';

    // Simplified conversion - you should implement a complete Vietnamese number-to-words converter
    const millions = Math.floor(number / 1000000);
    const thousands = Math.floor((number % 1000000) / 1000);
    const hundreds = number % 1000;

    let result = '';

    if (millions > 0) {
        result += `${millions} triệu `;
    }

    if (thousands > 0) {
        result += `${thousands} nghìn `;
    }

    if (hundreds > 0) {
        result += `${hundreds} `;
    }

    return result.trim();
};

const fetchInvoice = async () => {
    try {
        const response = await $api(`/invoices/${route.params.id}`, { method: 'GET' });
        invoice.value = response.data;
        console.log(invoice.value);
    } catch (error) {
        const data = error.response?._data;
        toast.error(data?.error || 'Đã có lỗi xảy ra khi lấy chi tiết hóa đơn.');
    }
};

onMounted(() => {
    fetchInvoice();
});
</script>

<style scoped>
@import '~/public/css/invoice.css';

#logo img {
    max-height: 100px;
}

/* Enhanced Custom Styles */
.company-details {
    margin-top: 15px;
}

.company-details h3 {
    margin-bottom: 10px;
    font-size: 22px;
    color: #333;
}

.company-details p {
    margin: 3px 0;
    font-size: 13px;
    color: #666;
    line-height: 1.5;
}

.invoice-meta {
    margin-top: 10px;
}

.invoice-meta p {
    margin: 8px 0;
    font-size: 16px;
    color: #333;
}

.invoice-info p {
    margin: 8px 0;
    font-size: 14px;
}

.status-section {
    text-align: right;
    margin-top: 20px;
}

.status-label {
    margin-bottom: 5px;
    font-size: 14px;
    color: #666;
}

.status-value {
    font-size: 18px;
    font-weight: bold;
    padding: 8px 16px;
    border-radius: 25px;
    display: inline-block;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-paid {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-unpaid {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Enhanced Table Styles */
.invoice-items-table {
    margin: 40px 0;
    border-collapse: collapse;
    width: 100%;
}

.invoice-items-table th {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    padding: 15px 10px;
    font-weight: 600;
    color: #333;
}

.invoice-items-table td {
    border: 1px solid #dee2e6;
    padding: 15px 10px;
    vertical-align: top;
}

.invoice-items-table tbody tr:nth-child(odd) {
    background-color: #f9f9f9;
}

.invoice-items-table tbody tr:hover {
    background-color: #f5f5f5;
}

.item-number {
    text-align: center;
    font-weight: bold;
    color: #666;
}

.item-description {
    line-height: 1.4;
}

.item-description strong {
    color: #333;
    font-size: 15px;
}

.item-amount {
    text-align: right;
    font-weight: bold;
    color: #333;
    font-size: 15px;
}

.service-note {
    margin-top: 5px;
    font-size: 12px;
    color: #888;
    font-style: italic;
}

.meter-details {
    margin-top: 8px;
    background-color: #f8f9fa;
    padding: 8px;
    border-radius: 4px;
    border-left: 3px solid #f91942;
}

.meter-row {
    display: flex;
    justify-content: space-between;
    margin: 3px 0;
    font-size: 12px;
    color: #666;
}

.meter-row.highlight {
    color: #333;
    font-weight: bold;
}

.meter-row span:first-child {
    flex: 1;
}

.meter-row span:last-child {
    font-weight: 500;
    color: #333;
}

/* Total Section */
.invoice-total-section {
    margin: 40px 0;
    padding: 20px;
    background-color: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.total-in-words {
    margin-bottom: 20px;
}

.total-in-words p:first-child {
    margin-bottom: 8px;
    font-size: 14px;
    color: #666;
}

.amount-words {
    font-size: 15px;
    color: #333;
    font-style: italic;
    text-transform: capitalize;
}

.total-amount-box {
    background-color: white;
    border: 2px solid #f91942;
    border-radius: 8px;
    padding: 20px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.total-label {
    font-size: 18px;
    font-weight: bold;
    color: #333;
}

.total-amount {
    font-size: 24px;
    font-weight: bold;
    color: #f91942;
}

.loading-section {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}
</style>
