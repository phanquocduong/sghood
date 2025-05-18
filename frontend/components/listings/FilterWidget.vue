<template>
    <div class="widget margin-bottom-40">
        <h3 class="margin-top-0 margin-bottom-30">Bộ lọc</h3>

        <!-- Keyword Input -->
        <div class="row with-forms">
            <div class="col-md-12">
                <input
                    type="text"
                    placeholder="Nhập từ khoá bạn muốn tìm..."
                    :value="filters.keyword"
                    @input="updateFilter('keyword', $event.target.value)"
                />
            </div>
        </div>

        <!-- Area Select -->
        <ClientOnly>
            <div class="row with-forms">
                <div class="col-md-12">
                    <select name="area" :value="filters.area" class="chosen-select">
                        <option value="">Tất cả khu vực</option>
                        <option v-for="option in areaOptions" :key="option" :value="option">
                            {{ option }}
                        </option>
                    </select>
                </div>
            </div>
        </ClientOnly>

        <!-- Price Range Select -->
        <ClientOnly>
            <div class="row with-forms">
                <div class="col-md-12">
                    <select name="priceRange" :value="filters.priceRange" class="chosen-select">
                        <option v-for="option in priceOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </div>
        </ClientOnly>

        <!-- Area Range Select -->
        <ClientOnly>
            <div class="row with-forms">
                <div class="col-md-12">
                    <select name="areaRange" :value="filters.areaRange" class="chosen-select">
                        <option v-for="option in areaRangeOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </div>
        </ClientOnly>

        <!-- Amenities Checkboxes -->
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

        <!-- Apply Button -->
        <button class="button fullwidth margin-top-25" @click="$emit('apply')">Cập nhật</button>
    </div>
</template>

<script setup>
import { onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
    filters: {
        type: Object,
        default: () => ({
            keyword: '',
            area: '',
            priceRange: '',
            areaRange: '',
            amenities: []
        })
    },
    areaOptions: {
        type: Array,
        default: () => []
    },
    priceOptions: {
        type: Array,
        default: () => []
    },
    areaRangeOptions: {
        type: Array,
        default: () => []
    },
    amenitiesOptions: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:filters', 'apply']);

const updateFilter = (key, value) => {
    const newFilters = { ...props.filters, [key]: value };
    console.log(`Updating ${key}:`, value); // Debug giá trị
    emit('update:filters', newFilters);
};

const toggleAmenity = amenity => {
    const amenities = props.filters.amenities.includes(amenity)
        ? props.filters.amenities.filter(a => a !== amenity)
        : [...props.filters.amenities, amenity];
    const newFilters = { ...props.filters, amenities };
    console.log('New filters:', newFilters); // Debug filters
    emit('update:filters', newFilters);
};

// Khởi tạo Chosen và gắn sự kiện change
onMounted(async () => {
    if (window.jQuery && window.jQuery.fn.chosen) {
        // Hoãn khởi tạo Chosen để đảm bảo DOM trong ClientOnly đã sẵn sàng
        await nextTick();
        window.jQuery('.chosen-select').each(function () {
            const $select = window.jQuery(this);
            $select
                .chosen({
                    width: '100%',
                    no_results_text: 'Không tìm thấy kết quả'
                })
                .on('change', event => {
                    const key = $select.attr('name');
                    const value = event.target.value;
                    console.log(`Chosen change - ${key}:`, value); // Debug sự kiện
                    updateFilter(key, value);
                });
        });
    } else {
        console.error('jQuery hoặc Chosen không được tải');
    }
});

// Hủy Chosen khi component bị hủy
onUnmounted(() => {
    if (window.jQuery && window.jQuery.fn.chosen) {
        window.jQuery('.chosen-select').chosen('destroy');
        window.jQuery('.chosen-select').off('change');
    }
});
</script>
