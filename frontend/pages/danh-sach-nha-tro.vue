<template>
    <Titlebar
        title="Danh sách nhà trọ"
        resultCount="Có 128 kết quả phù hợp"
        :breadcrumbs="[{ text: 'Trang chủ', to: '/' }, { text: 'Danh sách nhà trọ' }]"
    />
    <div class="container">
        <div class="row">
            <div class="col-lg-9 col-md-8 padding-right-30">
                <!-- Sorting / Layout Switcher -->
                <div class="row margin-bottom-25">
                    <div class="col-md-12 col-xs-12">
                        <SortBy
                            :options="['Sắp xếp mặc định', 'Nổi bật nhất', 'Mới nhất', 'Cũ nhất']"
                            :selected="sortOption"
                            @update:sort="sortOption = $event"
                        />
                    </div>
                </div>
                <!-- Sorting / Layout Switcher / End -->

                <div class="row">
                    <div v-for="item in listings" :key="item.id" class="col-lg-6 col-md-12">
                        <ListingItem :item="item" />
                    </div>
                </div>

                <!-- Pagination -->
                <div class="row">
                    <div class="col-md-12">
                        <Pagination :current-page="currentPage" :total-pages="totalPages" @change:page="currentPage = $event" />
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-3 col-md-4">
                <div class="sidebar">
                    <!-- Widget -->
                    <FilterWidget
                        :filters="filters"
                        :area-options="areaOptions"
                        :price-options="priceOptions"
                        :area-range-options="areaRangeOptions"
                        :amenities-options="amenitiesOptions"
                        @update:filters="filters = $event"
                        @apply="applyFilters"
                    />
                </div>
            </div>
            <!-- Sidebar / End -->
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
const sortOption = ref('Sắp xếp mặc định');
const currentPage = ref(1);
const totalPages = ref(3);
const listings = ref([
    {
        id: 1,
        image: '/images/listing-item-03.jpg',
        district: 'Quận 12',
        name: 'Nhà trọ Phố Hiến',
        address: '01 đường Lê Văn Khương, phường Thới An',
        price: '3.500.000đ',
        availableRooms: 14
    },
    {
        id: 2,
        image: '/images/listing-item-03.jpg',
        district: 'Quận 12',
        name: 'Nhà trọ Phố Hiến',
        address: '01 đường Lê Văn Khương, phường Thới An',
        price: '3.500.000đ',
        availableRooms: 14
    },
    {
        id: 3,
        image: '/images/listing-item-03.jpg',
        district: 'Quận 12',
        name: 'Nhà trọ Phố Hiến',
        address: '01 đường Lê Văn Khương, phường Thới An',
        price: '3.500.000đ',
        availableRooms: 14
    },
    {
        id: 4,
        image: '/images/listing-item-03.jpg',
        district: 'Quận 12',
        name: 'Nhà trọ Phố Hiến',
        address: '01 đường Lê Văn Khương, phường Thới An',
        price: '3.500.000đ',
        availableRooms: 14
    },
    {
        id: 5,
        image: '/images/listing-item-03.jpg',
        district: 'Quận 12',
        name: 'Nhà trọ Phố Hiến',
        address: '01 đường Lê Văn Khương, phường Thới An',
        price: '3.500.000đ',
        availableRooms: 14
    },
    {
        id: 6,
        image: '/images/listing-item-03.jpg',
        district: 'Quận 12',
        name: 'Nhà trọ Phố Hiến',
        address: '01 đường Lê Văn Khương, phường Thới An',
        price: '3.500.000đ',
        availableRooms: 14
    }
]);
const filters = ref({
    keyword: '',
    area: '',
    priceRange: '',
    areaRange: '',
    amenities: []
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

const areaRangeOptions = ref([
    { value: '', label: 'Tất cả diện tích' },
    { value: 'under_20', label: 'Dưới 20m²' },
    { value: '20_30', label: 'Từ 20m² đến 30m²' },
    { value: '30_50', label: 'Từ 30m² đến 50m²' },
    { value: 'over_50', label: 'Trên 50m²' }
]);

const amenitiesOptions = ref([
    { value: 'full_furniture', label: 'Đầy đủ nội thất' },
    { value: 'loft', label: 'Có gác' },
    { value: 'kitchen_shelf', label: 'Kệ bếp' },
    { value: 'air_conditioner', label: 'Có máy lạnh' },
    { value: 'washing_machine', label: 'Có máy giặt' },
    { value: 'fridge', label: 'Có tủ lạnh' },
    { value: 'elevator', label: 'Có thang máy' },
    { value: 'no_shared_owner', label: 'Không chung chủ' }
]);

const applyFilters = () => {
    // Implement filtering logic here, e.g., API call or local filtering
    console.log('Applying filters:', filters.value);
};
</script>

<style scoped></style>
