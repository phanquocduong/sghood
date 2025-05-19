<!-- components/SearchBanner.vue -->
<template>
    <div class="main-search-container" data-background-image="/images/main-search-background-01.jpg">
        <div class="main-search-inner">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h2>Tìm Phòng Trọ Phù Hợp Gần Bạn</h2>
                        <h4>Khám phá khu trọ chính chủ, giá tốt, cập nhật mỗi ngày tại Trọ Việt</h4>

                        <div class="main-search-input">
                            <!-- Keyword Input -->
                            <div class="main-search-input-item">
                                <input
                                    type="text"
                                    placeholder="Nhập từ khoá..."
                                    :value="search.keyword"
                                    @input="updateSearch('keyword', $event.target.value)"
                                />
                            </div>

                            <!-- Area Select -->
                            <ClientOnly>
                                <div class="main-search-input-item">
                                    <select name="area" :value="search.area" class="chosen-select">
                                        <option value="">Tất cả khu vực</option>
                                        <option v-for="option in areaOptions" :key="option" :value="option">
                                            {{ option }}
                                        </option>
                                    </select>
                                </div>
                            </ClientOnly>

                            <!-- Price Range Select -->
                            <ClientOnly>
                                <div class="main-search-input-item">
                                    <select name="priceRange" :value="search.priceRange" class="chosen-select">
                                        <option v-for="option in priceOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </div>
                            </ClientOnly>

                            <!-- Search Button -->
                            <button class="button" @click="$emit('search')">Tìm kiếm</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, onUnmounted, nextTick } from 'vue';

const props = defineProps({
    search: {
        type: Object,
        default: () => ({
            keyword: '',
            area: '',
            priceRange: ''
        })
    },
    areaOptions: {
        type: Array,
        default: () => []
    },
    priceOptions: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:search', 'search']);

const updateSearch = (key, value) => {
    const newSearch = { ...props.search, [key]: value };
    console.log(`Updating ${key}:`, value); // Debug giá trị
    emit('update:search', newSearch);
};

// Khởi tạo Chosen và gắn sự kiện change
onMounted(async () => {
    if (window.jQuery && window.jQuery.fn.chosen) {
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
                    console.log(`SearchBanner change - ${key}:`, value); // Debug sự kiện
                    updateSearch(key, value);
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

<style scoped>
/* Có thể thêm style nếu cần */
</style>
