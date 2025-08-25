<!-- Template cho component sắp xếp -->
<template>
    <ClientOnly>
        <div class="sort-by">
            <div class="sort-by-select">
                <!-- Dropdown chọn kiểu sắp xếp -->
                <select class="chosen-select-no-single">
                    <option v-for="option in options" :key="option" :value="option">
                        {{ option }}
                    </option>
                </select>
            </div>
        </div>
    </ClientOnly>
</template>

<script setup>
import { onMounted, nextTick } from 'vue';

// Nhận danh sách tùy chọn từ props
const props = defineProps({
    options: {
        type: Array,
        default: () => []
    }
});

// Phát sự kiện khi thay đổi lựa chọn
const emit = defineEmits(['update:sort']);

// Khởi tạo thư viện Chosen và gắn sự kiện change
onMounted(() => {
    nextTick(() => {
        if (window.jQuery && window.jQuery.fn.chosen) {
            window
                .jQuery('.chosen-select-no-single')
                .chosen({
                    width: '100%', // Chiều rộng toàn phần
                    no_results_text: 'Không tìm thấy kết quả' // Thông báo khi không có kết quả
                })
                .on('change', event => {
                    const value = event.target.value; // Lấy giá trị đã chọn
                    emit('update:sort', value); // Phát sự kiện cập nhật
                });
        } else {
            console.error('jQuery hoặc Chosen không được tải'); // Ghi log lỗi nếu thiếu thư viện
        }
    });
});
</script>
