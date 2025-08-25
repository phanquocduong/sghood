<template>
    <!-- Container chính cho thông tin hóa đơn -->
    <div v-if="invoice" id="invoice">
        <!-- Phần tiêu đề hóa đơn -->
        <div class="row">
            <div class="col-md-6">
                <!-- Logo công ty -->
                <div id="logo">
                    <img src="/images/sghood_logo1.png" alt="SGHood Logo" />
                </div>
                <!-- Thông tin công ty -->
                <div class="company-details">
                    <div class="clearfix"></div>
                    <p>SĐT: {{ config.contact_phone }}</p>
                    <p>Email: {{ config.contact_email }}</p>
                    <p>Website: {{ config.website_address }}</p>
                    <p>Địa chỉ: {{ config.office_address }}</p>
                </div>
            </div>

            <div class="col-md-6" id="details">
                <!-- Tiêu đề phiếu thu tiền -->
                <h2>PHIẾU THU TIỀN</h2>
                <div class="invoice-meta">
                    <!-- Tiêu đề hóa đơn dựa trên loại -->
                    <p>
                        <strong>{{
                            invoice.type === 'Đặt cọc'
                                ? 'ĐẶT CỌC HỢP ĐỒNG'
                                : `PHÒNG TRỌ THÁNG ${invoice.month.toString().padStart(2, '0')}/${invoice.year}`
                        }}</strong>
                    </p>
                    <!-- Tên phòng trọ -->
                    <p>
                        <strong
                            ><em>{{ invoice.contract.room.name }}</em></strong
                        >
                    </p>
                </div>
            </div>
        </div>

        <!-- Thông tin hóa đơn -->
        <div class="row margin-top-20">
            <div class="col-md-6">
                <div class="invoice-info">
                    <!-- Mã hóa đơn -->
                    <p><strong>Mã hóa đơn:</strong> {{ invoice.code }}</p>
                    <!-- Ngày tạo hóa đơn -->
                    <p><strong>Ngày tạo:</strong> {{ formatDate(invoice.created_at) }}</p>
                    <!-- Tên khách thuê -->
                    <p><strong>Khách thuê:</strong> {{ invoice.contract.user.name }}</p>
                    <!-- Số điện thoại khách thuê -->
                    <p><strong>Số điện thoại:</strong> {{ invoice.contract.user.phone || 'N/A' }}</p>
                </div>
            </div>

            <div class="col-md-6" id="details">
                <div class="status-section">
                    <!-- Nhãn trạng thái thanh toán -->
                    <p class="status-label">Trạng thái thanh toán:</p>
                    <!-- Giá trị trạng thái thanh toán với class tương ứng -->
                    <p class="status-value" :class="invoice.status === 'Đã trả' ? 'status-paid' : 'status-unpaid'">
                        {{ invoice.status }}
                    </p>
                    <!-- Thông tin hoàn tiền (nếu có) -->
                    <p style="color: #ee3535; font-weight: bold" v-if="invoice.refunded_at">
                        <em>Đã hoàn tiền lúc {{ formatDateTime(invoice.refunded_at) }}</em>
                    </p>
                </div>
            </div>
        </div>

        <!-- Bảng chi tiết các mục trong hóa đơn -->
        <table class="invoice-items-table">
            <thead>
                <tr>
                    <th width="8%"><span>STT</span></th>
                    <th width="60%"><span>Mô tả dịch vụ</span></th>
                    <th width="32%"><span>Thành tiền</span></th>
                </tr>
            </thead>
            <tbody>
                <!-- Mục tiền phòng (cho hóa đơn hàng tháng) -->
                <tr v-if="invoice.type === 'Hàng tháng'">
                    <td class="item-number">1</td>
                    <td class="item-description">
                        <strong>Tiền phòng</strong>
                        <div class="service-note">Phí thuê phòng hàng tháng</div>
                    </td>
                    <td class="item-amount">{{ formatPrice(invoice.room_fee) }}</td>
                </tr>

                <!-- Mục tiền đặt cọc (cho hóa đơn đặt cọc) -->
                <tr v-else>
                    <td class="item-number">1</td>
                    <td class="item-description">
                        <strong>Tiền đặt cọc</strong>
                        <div class="service-note">Tiền đặt cọc hợp đồng số {{ invoice.contract.id }}</div>
                    </td>
                    <td class="item-amount">{{ formatPrice(invoice.contract.deposit_amount) }}</td>
                </tr>

                <!-- Mục tiền điện (cho hóa đơn hàng tháng) -->
                <tr v-if="invoice.type === 'Hàng tháng'">
                    <td class="item-number">2</td>
                    <td class="item-description">
                        <strong>Tiền điện</strong>
                        <div class="meter-details">
                            <!-- Chỉ số điện cũ -->
                            <div class="meter-row">
                                <span>Chỉ số cũ:</span>
                                <span>{{ invoice.prev_electricity_kwh || 0 }} kWh</span>
                            </div>
                            <!-- Chỉ số điện mới -->
                            <div class="meter-row">
                                <span>Chỉ số mới:</span>
                                <span>{{ invoice.meter_reading.electricity_kwh }} kWh</span>
                            </div>
                            <!-- Điện tiêu thụ -->
                            <div class="meter-row highlight">
                                <span>Điện tiêu thụ:</span>
                                <span>{{ invoice.meter_reading.electricity_kwh - (invoice.prev_electricity_kwh || 0) }} kWh</span>
                            </div>
                            <!-- Đơn giá điện -->
                            <div class="meter-row">
                                <span>Đơn giá:</span>
                                <span>{{ formatPrice(invoice.contract.room.motel.electricity_fee) }}/kWh</span>
                            </div>
                        </div>
                    </td>
                    <td class="item-amount">{{ formatPrice(invoice.electricity_fee) }}</td>
                </tr>

                <!-- Mục tiền nước (cho hóa đơn hàng tháng) -->
                <tr v-if="invoice.type === 'Hàng tháng'">
                    <td class="item-number">3</td>
                    <td class="item-description">
                        <strong>Tiền nước</strong>
                        <div class="meter-details">
                            <!-- Chỉ số nước cũ -->
                            <div class="meter-row">
                                <span>Chỉ số cũ:</span>
                                <span>{{ invoice.prev_water_m3 || 0 }} m³</span>
                            </div>
                            <!-- Chỉ số nước mới -->
                            <div class="meter-row">
                                <span>Chỉ số mới:</span>
                                <span>{{ invoice.meter_reading.water_m3 }} m³</span>
                            </div>
                            <!-- Nước tiêu thụ -->
                            <div class="meter-row highlight">
                                <span>Nước tiêu thụ:</span>
                                <span>{{ invoice.meter_reading.water_m3 - (invoice.prev_water_m3 || 0) }} m³</span>
                            </div>
                            <!-- Đơn giá nước -->
                            <div class="meter-row">
                                <span>Đơn giá:</span>
                                <span>{{ formatPrice(invoice.contract.room.motel.water_fee) }}/m³</span>
                            </div>
                        </div>
                    </td>
                    <td class="item-amount">{{ formatPrice(invoice.water_fee) }}</td>
                </tr>

                <!-- Mục tiền gửi xe (cho hóa đơn hàng tháng) -->
                <tr v-if="invoice.type === 'Hàng tháng'">
                    <td class="item-number">4</td>
                    <td class="item-description">
                        <strong>Tiền gửi xe</strong>
                        <div class="service-note">Phí gửi xe máy hàng tháng</div>
                    </td>
                    <td class="item-amount">{{ formatPrice(invoice.parking_fee) }}</td>
                </tr>

                <!-- Mục tiền rác (cho hóa đơn hàng tháng) -->
                <tr v-if="invoice.type === 'Hàng tháng'">
                    <td class="item-number">5</td>
                    <td class="item-description">
                        <strong>Tiền rác</strong>
                        <div class="service-note">Phí thu gom rác thải hàng tháng</div>
                    </td>
                    <td class="item-amount">{{ formatPrice(invoice.junk_fee) }}</td>
                </tr>

                <!-- Mục tiền Internet (cho hóa đơn hàng tháng) -->
                <tr v-if="invoice.type === 'Hàng tháng'">
                    <td class="item-number">6</td>
                    <td class="item-description">
                        <strong>Tiền Internet</strong>
                        <div class="service-note">Phí internet băng thông rộng hàng tháng</div>
                    </td>
                    <td class="item-amount">{{ formatPrice(invoice.internet_fee) }}</td>
                </tr>

                <!-- Mục phí dịch vụ (cho hóa đơn hàng tháng) -->
                <tr v-if="invoice.type === 'Hàng tháng'">
                    <td class="item-number">7</td>
                    <td class="item-description">
                        <strong>Phí dịch vụ</strong>
                        <div class="service-note">Phí dịch vụ chung hàng tháng</div>
                    </td>
                    <td class="item-amount">{{ formatPrice(invoice.service_fee) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Phần tổng cộng -->
        <div class="invoice-total-section">
            <div class="row">
                <div class="col-md-6">
                    <!-- Tổng tiền bằng chữ -->
                    <div class="total-in-words">
                        <p><strong>Tổng tiền bằng chữ:</strong></p>
                        <p class="amount-words">{{ convertNumberToWords(invoice.total_amount) }} đồng</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <!-- Tổng tiền bằng số -->
                    <div class="total-amount-box">
                        <div class="total-row">
                            <span class="total-label">TỔNG CỘNG:</span>
                            <span class="total-amount">{{ formatPrice(invoice.total_amount) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phần chân trang -->
        <div class="row">
            <div class="col-md-12 text-center">
                <!-- Ghi chú thanh toán -->
                <strong v-if="invoice.type === 'Hàng tháng' && invoice.status === 'Chưa trả'">
                    Lưu ý: Vui lòng thanh toán đúng hạn từ ngày 1 đến ngày 10 hằng tháng. Có thể thanh toán trực tuyến hoặc tiền mặt tại văn
                    phòng quản lý.
                </strong>
                <strong v-if="invoice.type === 'Đặt cọc' && invoice.status === 'Chưa trả'">
                    Lưu ý: Vui lòng thanh toán tiền cọc để kích hoạt hợp đồng. Có thể thanh toán trực tuyến hoặc tiền mặt tại văn phòng quản
                    lý.
                </strong>
                <!-- Thông tin liên hệ ở chân trang -->
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

    <!-- Hiển thị thông báo khi đang tải hóa đơn -->
    <div v-else class="loading-section">
        <p>Đang tải thông tin hóa đơn...</p>
    </div>

    <!-- Nút thanh toán (nếu hóa đơn chưa trả) -->
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

// Cấu hình metadata cho trang, sử dụng layout 'blank'
definePageMeta({
    layout: 'blank'
});

// Sử dụng composable để định dạng giá và ngày
const { formatPrice } = useFormatPrice();
const { formatDate, formatDateTime } = useFormatDate();
const { $api } = useNuxtApp(); // Lấy instance API từ Nuxt
const config = useState('configs'); // Lấy cấu hình từ state
const route = useRoute(); // Lấy thông tin route hiện tại
const toast = useAppToast(); // Sử dụng composable để hiển thị thông báo
const invoice = ref(null); // Biến lưu thông tin hóa đơn

// Hàm chuyển đổi số thành chữ (bằng tiếng Việt)
const convertNumberToWords = number => {
    // Mảng chứa các từ tiếng Việt cho số
    const ones = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
    const tens = ['', '', 'hai mươi', 'ba mươi', 'bốn mươi', 'năm mươi', 'sáu mươi', 'bảy mươi', 'tám mươi', 'chín mươi'];
    const scales = ['', 'nghìn', 'triệu', 'tỷ'];

    if (number === 0) return 'không';

    // Chuyển đổi số thành chữ (phiên bản đơn giản hóa)
    const millions = Math.floor(number / 1000000); // Phần triệu
    const thousands = Math.floor((number % 1000000) / 1000); // Phần nghìn
    const hundreds = number % 1000; // Phần trăm

    let result = '';

    if (millions > 0) {
        result += `${millions} triệu `; // Thêm phần triệu
    }

    if (thousands > 0) {
        result += `${thousands} nghìn `; // Thêm phần nghìn
    }

    if (hundreds > 0) {
        result += `${hundreds} `; // Thêm phần trăm
    }

    return result.trim(); // Trả về kết quả đã được cắt bỏ khoảng trắng thừa
};

// Hàm lấy chi tiết hóa đơn từ server
const fetchInvoice = async () => {
    try {
        // Gửi yêu cầu GET để lấy chi tiết hóa đơn theo ID
        const response = await $api(`/invoices/${route.params.id}`, { method: 'GET' });
        invoice.value = response.data; // Cập nhật thông tin hóa đơn
    } catch (error) {
        const data = error.response?._data;
        toast.error(data?.error || 'Đã có lỗi xảy ra khi lấy chi tiết hóa đơn.'); // Hiển thị thông báo lỗi
    }
};

// Tải chi tiết hóa đơn khi component được mount
onMounted(() => {
    fetchInvoice();
});
</script>

<style scoped>
@import '~/public/css/invoice.css';
</style>
