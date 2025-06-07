<template>
    <div class="main-search-container" data-background-image="/images/main-search-background-01.jpg">
        <div class="main-search-inner">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h2>Tìm Phòng Trọ Phù Hợp Gần Bạn</h2>
                        <h4>Khám phá khu trọ chính chủ, giá tốt, cập nhật mỗi ngày tại Trọ Việt</h4>

                        <div class="main-search-input">
                            <div class="main-search-input-item">
                                <input
                                    type="text"
                                    placeholder="Nhập từ khoá..."
                                    :value="search.keyword"
                                    @input="updateSearch('keyword', $event.target.value)"
                                />
                            </div>

                            <ClientOnly>
                                <div class="main-search-input-item">
                                    <select name="district" :value="search.district" class="chosen-select">
                                        <option value="">Tất cả khu vực</option>
                                        <option v-for="option in districts" :key="option" :value="option">
                                            {{ option }}
                                        </option>
                                    </select>
                                </div>
                            </ClientOnly>

                            <ClientOnly>
                                <div class="main-search-input-item">
                                    <select name="priceRange" :value="search.priceRange" class="chosen-select">
                                        <option v-for="option in priceOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </div>
                            </ClientOnly>

                            <button class="button" @click="$emit('search')">Tìm kiếm</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, watch, nextTick } from 'vue';

const props = defineProps({
    search: { type: Object, default: () => ({ keyword: '', district: '', priceRange: '' }) },
    districts: { type: Array, default: () => [] },
    priceOptions: { type: Array, default: () => [] }
});

const emit = defineEmits(['update:search', 'search']);

const updateSearch = (key, value) => {
    const newSearch = { ...props.search, [key]: value };
    emit('update:search', newSearch);
};

// Khởi tạo Chosen khi component được mount
onMounted(() => {
    nextTick(() => {
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
