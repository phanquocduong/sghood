<!-- Template hiển thị banner tìm kiếm -->
<template>
    <!-- Chỉ render ở client-side để tránh lỗi SSR -->
    <ClientOnly>
        <!-- Hiển thị banner nếu có cấu hình home_banner -->
        <div v-if="config.home_banner" class="main-search-container" :data-background-image="baseUrl + config.home_banner">
            <div class="main-search-inner">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- Tiêu đề và phụ đề của banner -->
                            <h2 v-if="config?.home_banner_title">{{ config.home_banner_title }}</h2>
                            <h4 v-if="config?.home_banner_subtitle">{{ config.home_banner_subtitle }}</h4>

                            <!-- Form tìm kiếm -->
                            <div class="main-search-input">
                                <!-- Ô nhập từ khóa tìm kiếm -->
                                <div class="main-search-input-item">
                                    <input
                                        type="text"
                                        placeholder="Nhập từ khoá..."
                                        :value="search.keyword"
                                        @input="updateSearch('keyword', $event.target.value)"
                                    />
                                </div>

                                <!-- Dropdown chọn quận -->
                                <div class="main-search-input-item">
                                    <select name="district" :value="search.district" class="chosen-select">
                                        <option value="">Tất cả khu vực</option>
                                        <option v-for="option in districts" :key="option" :value="option">
                                            {{ option }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Dropdown chọn khoảng giá -->
                                <div class="main-search-input-item">
                                    <select name="priceRange" :value="search.priceRange" class="chosen-select">
                                        <option value="">Tất cả mức giá</option>
                                        <option v-for="option in priceOptions" :key="option.key" :value="option.key">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Nút tìm kiếm -->
                                <button class="button" @click="$emit('search')">Tìm kiếm</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ClientOnly>
</template>

<script setup>
import { onMounted, watch, nextTick } from 'vue';

// Lấy cấu hình và base URL từ runtime config
const config = useState('configs');
const baseUrl = useRuntimeConfig().public.baseUrl;

// Định nghĩa props
const props = defineProps({
    search: { type: Object, default: () => ({ keyword: '', district: '', priceRange: '' }) },
    districts: { type: Array, default: () => [] },
    priceOptions: { type: Array, default: () => [] }
});

// Định nghĩa các sự kiện emit
const emit = defineEmits(['update:search', 'search']);

// Hàm cập nhật dữ liệu tìm kiếm
const updateSearch = (key, value) => {
    const newSearch = { ...props.search, [key]: value };
    emit('update:search', newSearch);
};

// Khởi tạo Chosen select khi component được mount
onMounted(() => {
    nextTick(() => {
        // Kiểm tra và khởi tạo Chosen select nếu jQuery và Chosen có sẵn
        if (window.jQuery && window.jQuery.fn.chosen) {
            window
                .jQuery('.chosen-select')
                .chosen({
                    width: '100%',
                    no_results_text: 'Không tìm thấy kết quả'
                })
                .on('change', event => {
                    const key = event.target.name;
                    const value = event.target.value;
                    updateSearch(key, value);
                });
        } else {
            console.error('jQuery hoặc Chosen không được tải');
        }
    });
});

// Theo dõi sự thay đổi của danh sách quận để cập nhật Chosen select
watch(
    () => props.districts,
    () => {
        nextTick(() => {
            if (window.jQuery && window.jQuery.fn.chosen) {
                window.jQuery('select[name="district"]').trigger('chosen:updated');
            }
        });
    },
    { deep: true }
);
</script>
