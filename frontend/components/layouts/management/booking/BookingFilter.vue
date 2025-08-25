<template>
    <!-- Bộ lọc đặt phòng -->
    <div class="booking-requests-filter">
        <ClientOnly>
            <!-- Chỉ render phía client để tránh lỗi SSR -->
            <!-- Sắp xếp đặt phòng -->
            <div class="sort-by">
                <select name="sort" v-model="filter.sort" class="chosen-select" @change="updateFilter('sort', $event.target.value)">
                    <option value="default">Sắp xếp mặc định</option>
                    <option value="oldest">Cũ nhất</option>
                    <option value="latest">Mới nhất</option>
                </select>
            </div>
            <!-- Lọc theo trạng thái -->
            <div class="sort-by">
                <select name="status" v-model="filter.status" class="chosen-select" @change="updateFilter('status', $event.target.value)">
                    <option value="">Tất cả trạng thái</option>
                    <option value="Chờ xác nhận">Chờ xác nhận</option>
                    <option value="Chấp nhận">Được chấp nhận</option>
                    <option value="Từ chối">Bị từ chối</option>
                    <option value="Huỷ bỏ">Huỷ bỏ</option>
                </select>
            </div>
        </ClientOnly>
    </div>
</template>

<script setup>
import { onMounted, nextTick } from 'vue';

const props = defineProps({
    filter: {
        type: Object,
        required: true // Prop chứa dữ liệu bộ lọc
    }
});

const emit = defineEmits(['update:filter']); // Emit sự kiện cập nhật bộ lọc

// Hàm cập nhật giá trị bộ lọc
const updateFilter = (key, value) => {
    emit('update:filter', { ...props.filter, [key]: value }); // Gửi dữ liệu bộ lọc mới
};

// Khởi tạo thư viện Chosen cho select box
const initChosenSelect = () => {
    if (!window.jQuery || !window.jQuery.fn.chosen) {
        console.error('jQuery hoặc Chosen không được tải'); // Báo lỗi nếu thư viện không tải
        return;
    }
    window
        .jQuery('.chosen-select')
        .chosen({
            width: '100%',
            no_results_text: 'Không tìm thấy kết quả' // Cấu hình Chosen
        })
        .on('change', event => {
            updateFilter(event.target.name, event.target.value); // Cập nhật bộ lọc khi select thay đổi
        });
};

// Khởi tạo Chosen khi component được mount
onMounted(() => {
    nextTick(initChosenSelect); // Đảm bảo DOM được render trước khi khởi tạo Chosen
});
</script>

<style scoped></style>
