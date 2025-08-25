<template>
    <!-- Container cho các bộ lọc hóa đơn -->
    <div class="booking-requests-filter">
        <!-- Dropdown chọn tháng -->
        <ClientOnly>
            <div class="sort-by">
                <div class="sort-by-select">
                    <select v-model="filter.month" name="month" class="chosen-select">
                        <option value="">Tất cả tháng</option>
                        <option v-for="month in months" :key="month" :value="month">Tháng {{ month }}</option>
                    </select>
                </div>
            </div>
        </ClientOnly>

        <!-- Dropdown chọn năm -->
        <ClientOnly>
            <div class="sort-by">
                <div class="sort-by-select">
                    <select v-model="filter.year" name="year" class="chosen-select">
                        <option value="">Tất cả năm</option>
                        <option v-for="year in years" :key="year" :value="year">{{ year }}</option>
                    </select>
                </div>
            </div>
        </ClientOnly>

        <!-- Dropdown chọn kiểu sắp xếp -->
        <ClientOnly>
            <div class="sort-by">
                <div class="sort-by-select">
                    <select v-model="filter.sort" name="sort" class="chosen-select">
                        <option value="default">Sắp xếp mặc định</option>
                        <option value="oldest">Cũ nhất</option>
                        <option value="latest">Mới nhất</option>
                    </select>
                </div>
            </div>
        </ClientOnly>

        <!-- Dropdown chọn loại hóa đơn -->
        <ClientOnly>
            <div class="sort-by">
                <div class="sort-by-select">
                    <select v-model="filter.type" name="type" class="chosen-select">
                        <option value="">Tất cả loại</option>
                        <option value="Đặt cọc">Đặt cọc</option>
                        <option value="Hàng tháng">Hàng tháng</option>
                    </select>
                </div>
            </div>
        </ClientOnly>
    </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import { useNuxtApp } from '#app';
import { useAppToast } from '~/composables/useToast';

const { $api } = useNuxtApp(); // Lấy instance API từ Nuxt
const toast = useAppToast(); // Sử dụng composable để hiển thị thông báo
const months = ref([]); // Danh sách các tháng
const years = ref([]); // Danh sách các năm

// Định nghĩa props
const props = defineProps({
    filter: {
        type: Object,
        required: true // Bộ lọc hóa đơn
    }
});

// Định nghĩa sự kiện emit
const emit = defineEmits(['update:filter']);

// Hàm lấy danh sách tháng và năm từ server
const fetchMonthsAndYears = async () => {
    try {
        // Gửi yêu cầu GET để lấy danh sách tháng và năm
        const response = await $api('/invoices/months-years', { method: 'GET' });
        months.value = response.data.months; // Cập nhật danh sách tháng
        years.value = response.data.years; // Cập nhật danh sách năm
        // Cập nhật Chosen sau khi dữ liệu được fetch
        nextTick(() => {
            if (window.jQuery && window.jQuery.fn.chosen) {
                window.jQuery('.chosen-select').trigger('chosen:updated');
            }
        });
    } catch (error) {
        toast.error('Lỗi khi lấy danh sách tháng và năm.'); // Hiển thị thông báo lỗi
    }
};

// Theo dõi thay đổi của filter để cập nhật Chosen
watch(
    () => props.filter,
    () => {
        nextTick(() => {
            if (window.jQuery && window.jQuery.fn.chosen) {
                window.jQuery('.chosen-select').trigger('chosen:updated'); // Cập nhật giao diện Chosen
            }
        });
    },
    { deep: true }
);

// Khởi tạo Chosen khi component được mount
onMounted(() => {
    fetchMonthsAndYears(); // Lấy danh sách tháng và năm
    nextTick(() => {
        if (window.jQuery && window.jQuery.fn.chosen) {
            // Khởi tạo Chosen cho các dropdown
            window
                .jQuery('.chosen-select')
                .chosen({
                    width: '100%',
                    no_results_text: 'Không tìm thấy kết quả'
                })
                .on('change', event => {
                    const key = event.target.name; // Lấy tên trường thay đổi
                    const value = event.target.value; // Lấy giá trị mới
                    emit('update:filter', { ...props.filter, [key]: value }); // Emit sự kiện cập nhật filter
                });
        } else {
            console.error('jQuery hoặc Chosen không được tải');
        }
    });
});
</script>
