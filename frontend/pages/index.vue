<template>
    <SearchBanner
        :search="search"
        :area-options="areaOptions"
        :price-options="priceOptions"
        @update:search="search = $event"
        @search="handleSearch"
    />

    <SectionFeaturedAreas :districts="districts" />

    <SectionFeaturedRentals />

    <SectionRentalSteps />

    <SectionRoomsAvailableSoon />
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const { $api } = useNuxtApp();
const router = useRouter();

const search = ref({ keyword: '', area: '', priceRange: '' });
const areaOptions = ref([]);
const districts = ref([]);

const priceOptions = ref([
    { value: '', label: 'Tất cả mức giá' },
    { value: 'under_1m', label: 'Dưới 1 triệu' },
    { value: '1m_2m', label: '1 - 2 triệu' },
    { value: '2m_3m', label: '2 - 3 triệu' },
    { value: '3m_5m', label: '3 - 5 triệu' },
    { value: 'over_5m', label: 'Trên 5 triệu' }
]);

const handleSearch = () => {
    router.push({
        path: '/danh-sach-nha-tro',
        query: {
            keyword: search.value.keyword || undefined,
            area: search.value.area || undefined,
            priceRange: search.value.priceRange || undefined
        }
    });
};

onMounted(async () => {
    try {
        const response = await $api('/districts', { method: 'GET' });
        areaOptions.value = response.data.map(d => d.name);
        districts.value = response.data;
    } catch (error) {
        console.error('Lỗi khi tải danh sách quận:', error);
    }
});
</script>

<style scoped></style>
