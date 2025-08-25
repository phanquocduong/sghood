<!-- Template cho component bộ lọc -->
<template>
    <div class="widget margin-bottom-40 margin-top-60">
        <h3 class="margin-top-0 margin-bottom-30">Bộ lọc</h3>

        <!-- Trường nhập từ khóa -->
        <div class="row with-forms">
            <div class="col-md-12">
                <input
                    type="text"
                    placeholder="Nhập từ khoá..."
                    :value="filters.keyword"
                    @input="updateFilter('keyword', $event.target.value)"
                />
            </div>
        </div>

        <!-- Dropdown chọn quận -->
        <ClientOnly>
            <div class="row with-forms">
                <div class="col-md-12">
                    <select name="district" :value="filters.district" class="chosen-select">
                        <option value="">Tất cả khu vực</option>
                        <option v-for="option in districts" :key="option" :value="option">
                            {{ option }}
                        </option>
                    </select>
                </div>
            </div>
        </ClientOnly>

        <!-- Dropdown chọn khoảng giá -->
        <ClientOnly>
            <div class="row with-forms">
                <div class="col-md-12">
                    <select name="priceRange" :value="filters.priceRange" class="chosen-select">
                        <option value="">Tất cả mức giá</option>
                        <option v-for="option in priceOptions" :key="option.key" :value="option.key">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </div>
        </ClientOnly>

        <!-- Dropdown chọn khoảng diện tích -->
        <ClientOnly>
            <div class="row with-forms">
                <div class="col-md-12">
                    <select name="areaRange" :value="filters.areaRange" class="chosen-select">
                        <option value="">Tất cả diện tích</option>
                        <option v-for="option in areaRangeOptions" :key="option.key" :value="option.key">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </div>
        </ClientOnly>

        <!-- Checkbox chọn tiện ích -->
        <a
            href="#"
            class="more-search-options-trigger margin-bottom-5 margin-top-20"
            data-open-title="Tiện ích chung"
            data-close-title="Tiện ích chung"
        ></a>
        <div class="more-search-options relative">
            <div class="checkboxes one-in-row margin-bottom-15">
                <div v-for="amenity in amenitiesOptions" :key="amenity.value">
                    <input
                        :id="'check-' + amenity.value"
                        type="checkbox"
                        :checked="filters.amenities.includes(amenity.value)"
                        @change="toggleAmenity(amenity.value)"
                    />
                    <label :for="'check-' + amenity.value">{{ amenity.label }}</label>
                </div>
            </div>
        </div>

        <!-- Nút áp dụng bộ lọc -->
        <button class="button fullwidth margin-top-25" @click="$emit('apply')">Cập nhật</button>
    </div>
</template>

<script setup>
import { onMounted, watch, nextTick } from 'vue';

// Nhận dữ liệu từ props
const props = defineProps({
    filters: {
        type: Object,
        default: () => ({
            keyword: '',
            district: '',
            priceRange: '',
            areaRange: '',
            amenities: []
        })
    },
    districts: { type: Array, default: () => [] },
    priceOptions: { type: Array, default: () => [] },
    areaRangeOptions: { type: Array, default: () => [] },
    amenitiesOptions: { type: Array, default: () => [] }
});

// Phát sự kiện cập nhật bộ lọc và áp dụng
const emit = defineEmits(['update:filters', 'apply']);

// Hàm cập nhật giá trị bộ lọc
const updateFilter = (key, value) => {
    const newFilters = { ...props.filters, [key]: value }; // Tạo bộ lọc mới
    emit('update:filters', newFilters); // Phát sự kiện cập nhật
};

// Hàm bật/tắt tiện ích
const toggleAmenity = amenity => {
    const amenities = props.filters.amenities.includes(amenity)
        ? props.filters.amenities.filter(a => a !== amenity) // Xóa tiện ích nếu đã chọn
        : [...props.filters.amenities, amenity]; // Thêm tiện ích nếu chưa chọn
    const newFilters = { ...props.filters, amenities }; // Cập nhật danh sách tiện ích
    emit('update:filters', newFilters); // Phát sự kiện cập nhật
};

// Khởi tạo Chosen và gắn sự kiện change
onMounted(() => {
    nextTick(() => {
        if (window.jQuery && window.jQuery.fn.chosen) {
            window
                .jQuery('.chosen-select')
                .chosen({
                    width: '100%', // Chiều rộng toàn phần
                    no_results_text: 'Không tìm thấy kết quả' // Thông báo khi không có kết quả
                })
                .on('change', event => {
                    const key = event.target.name; // Lấy tên trường
                    const value = event.target.value; // Lấy giá trị đã chọn
                    updateFilter(key, value); // Cập nhật bộ lọc
                });
        } else {
            console.error('jQuery hoặc Chosen không được tải'); // Ghi log lỗi nếu thiếu thư viện
        }
    });
});

// Theo dõi thay đổi danh sách quận để cập nhật Chosen
watch(
    () => props.districts,
    () => {
        nextTick(() => {
            if (window.jQuery && window.jQuery.fn.chosen) {
                window.jQuery('.chosen-select').trigger('chosen:updated'); // Cập nhật dropdown Chosen
            }
        });
    },
    { deep: true }
);
</script>
