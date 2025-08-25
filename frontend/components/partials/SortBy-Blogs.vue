<!-- components/SortBy-Blogs.vue -->
<template>
    <!-- Container cho bộ lọc danh mục bài viết -->
    <div class="sort-by">
        <div class="sort-by-select">
            <!-- Dropdown chọn danh mục bài viết -->
            <select class="chosen-select-no-single" v-if="categories?.length">
                <!-- Tùy chọn mặc định -->
                <option value="">Danh mục mặc định</option>
                <!-- Lặp qua danh sách danh mục để tạo các tùy chọn -->
                <option v-for="cate in categories" :key="cate" :value="cate">
                    {{ cate }}
                </option>
            </select>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';

// Định nghĩa các props nhận từ component cha
const props = defineProps({
    categories: {
        type: Array,
        default: () => [] // Danh sách danh mục bài viết
    },
    selectedCategory: {
        type: String,
        default: '' // Danh mục đang được chọn
    },
    handleFilter: Function // Hàm xử lý lọc bài viết
});

// Định nghĩa sự kiện emit để cập nhật danh mục được chọn
const emit = defineEmits(['update:selectedCategory']);

// Hàm khởi tạo plugin Chosen cho dropdown
const initChosen = () => {
    const $ = window.jQuery; // Sử dụng jQuery từ global
    const $el = $('.chosen-select-no-single'); // Lấy phần tử select

    // Kiểm tra nếu không tìm thấy phần tử select thì thoát
    if ($el.length === 0) return;

    // Hủy instance Chosen cũ nếu có
    if ($el.data('chosen')) {
        $el.chosen('destroy');
    }

    // Khởi tạo plugin Chosen với tùy chọn vô hiệu hóa tìm kiếm nếu ít hơn 10 mục
    $el.chosen({ disable_search_threshold: 10 });

    // Cập nhật giá trị được chọn nếu có
    if (props.selectedCategory) {
        $el.val(props.selectedCategory).trigger('chosen:updated');
    }

    // Xử lý sự kiện thay đổi giá trị dropdown
    $el.off('change').on('change', function (e) {
        const value = $(e.target).val();
        emit('update:selectedCategory', value); // Phát sự kiện để cập nhật danh mục
        props.handleFilter?.(value); // Gọi hàm lọc nếu được cung cấp
    });
};

// Theo dõi thay đổi danh sách danh mục để khởi tạo lại Chosen
watch(
    () => props.categories,
    () => {
        nextTick(() => {
            initChosen(); // Khởi tạo lại Chosen sau khi danh mục thay đổi
        });
    },
    { deep: true }
);

// Theo dõi thay đổi danh mục được chọn để cập nhật dropdown
watch(
    () => props.selectedCategory,
    val => {
        const $ = window.jQuery;
        const $el = $('.chosen-select-no-single');
        if ($el.data('chosen')) {
            $el.val(val).trigger('chosen:updated'); // Cập nhật giá trị hiển thị
        }
    }
);

// Khởi tạo Chosen khi component được gắn vào DOM
onMounted(() => {
    nextTick(() => {
        initChosen();
    });
});
</script>

<style scoped>
/* CSS cho container bộ lọc */
.sort-by-select {
    display: flex;
    justify-content: flex-end; /* Căn phải */
}

/* Định dạng container của Chosen */
.sort-by .chosen-container {
    width: auto !important;
    min-width: 120px; /* Độ rộng tối thiểu */
}

/* Định dạng dropdown Chosen */
.sort-by .chosen-container-single .chosen-single {
    padding: 6px 12px !important;
    height: auto;
    line-height: normal;
    text-align: right; /* Căn phải nội dung */
    margin-left: -18px !important;
    float: right;
}

/* Định dạng văn bản trong dropdown */
.sort-by .chosen-single span {
    text-align: right;
    width: 100%;
}

/* Định dạng select mặc định */
.sort-by-select select {
    padding: 5px;
}
</style>
