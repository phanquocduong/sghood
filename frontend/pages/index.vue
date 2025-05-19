<template>
    <SearchBanner
        :search="search"
        :area-options="areaOptions"
        :price-options="priceOptions"
        @update:search="search = $event"
        @search="handleSearch"
    />

    <SectionFeaturedAreas />

    <SectionFeaturedRentals />

    <SectionRentalSteps />
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const search = ref({
    keyword: '',
    area: '',
    priceRange: ''
});

const areaOptions = ref([
    'Thủ Đức',
    'Quận 1',
    'Quận 3',
    'Quận 4',
    'Quận 5',
    'Quận 6',
    'Quận 7',
    'Quận 8',
    'Quận 10',
    'Quận 11',
    'Quận 12',
    'Tân Bình',
    'Bình Tân',
    'Bình Thạn',
    'Tân Phú',
    'Gò Vấp',
    'Phú Nhuậ',
    'Bình Cháh',
    'Hóc Môn',
    'Cần Giờ',
    'Củ Chi',
    'Nhà Bè'
]);

const priceOptions = ref([
    { value: '', label: 'Tất cả mức giá' },
    { value: 'under_1m', label: 'Dưới 1 triệu' },
    { value: '1m_2m', label: '1 - 2 triệu' },
    { value: '2m_3m', label: '2 - 3 triệu' },
    { value: '3m_5m', label: '3 - 5 triệu' },
    { value: 'over_5m', label: 'Trên 5 triệu' }
]);

const handleSearch = () => {
    console.log('Search triggered:', search.value); // Debug tìm kiếm
    // Thêm logic tìm kiếm, ví dụ: điều hướng hoặc gọi API
    router.push('/danh-sach-nha-tro');
};
</script>

<style scoped></style>
