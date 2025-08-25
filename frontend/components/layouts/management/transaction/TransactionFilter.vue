<template>
    <div class="booking-requests-filter">
        <!-- Bộ lọc sắp xếp giao dịch -->
        <ClientOnly>
            <div class="sort-by">
                <div class="sort-by-select">
                    <!-- Dropdown chọn kiểu sắp xếp -->
                    <select v-model="filter.sort" name="sort" class="chosen-select">
                        <option value="default">Sắp xếp mặc định</option>
                        <option value="oldest">Cũ nhất</option>
                        <option value="latest">Mới nhất</option>
                    </select>
                </div>
            </div>
        </ClientOnly>

        <!-- Bộ lọc loại giao dịch -->
        <ClientOnly>
            <div class="sort-by">
                <div class="sort-by-select">
                    <!-- Dropdown chọn loại giao dịch -->
                    <select v-model="filter.type" name="type" class="chosen-select">
                        <option value="">Tất cả loại</option>
                        <option value="in">Chi</option>
                        <option value="out">Thu</option>
                    </select>
                </div>
            </div>
        </ClientOnly>
    </div>
</template>

<script setup>
import { watch, nextTick } from 'vue';

// Nhận filter từ component cha
const props = defineProps({
    filter: {
        type: Object,
        required: true
    }
});

// Phát sự kiện khi filter thay đổi
const emit = defineEmits(['update:filter']);

// Theo dõi thay đổi của filter và cập nhật giao diện Chosen
watch(
    () => props.filter,
    () => {
        nextTick(() => {
            // Kích hoạt cập nhật Chosen sau khi DOM được render
            if (window.jQuery && window.jQuery.fn.chosen) {
                window.jQuery('.chosen-select').trigger('chosen:updated');
            }
        });
    },
    { deep: true } // Theo dõi sâu để phát hiện thay đổi trong object filter
);

// Khởi tạo plugin Chosen khi component được mounted
onMounted(() => {
    nextTick(() => {
        if (window.jQuery && window.jQuery.fn.chosen) {
            // Áp dụng plugin Chosen cho các select có class 'chosen-select'
            window
                .jQuery('.chosen-select')
                .chosen({
                    width: '100%', // Độ rộng dropdown
                    no_results_text: 'Không tìm thấy kết quả' // Thông báo khi không có kết quả
                })
                .on('change', event => {
                    // Xử lý sự kiện thay đổi giá trị dropdown
                    const key = event.target.name; // Lấy tên trường (sort hoặc type)
                    const value = event.target.value; // Lấy giá trị được chọn
                    emit('update:filter', { ...props.filter, [key]: value }); // Phát sự kiện update filter
                });
        } else {
            console.error('jQuery hoặc Chosen không được tải'); // Báo lỗi nếu jQuery hoặc Chosen không có
        }
    });
});
</script>
