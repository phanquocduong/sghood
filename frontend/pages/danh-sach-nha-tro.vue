<!-- pages/danh-sach-nha-tro.vue -->
<template>
    <div>
        <Titlebar
            title="Danh sách nhà trọ"
            :resultCount="`Có ${total} kết quả phù hợp`"
            :breadcrumbs="[{ text: 'Trang chủ', to: '/' }, { text: 'Danh sách nhà trọ' }]"
        />
        <div class="container">
            <div class="row">
                <div class="col-lg-9 col-md-8 padding-right-30">
                    <div class="row margin-bottom-25">
                        <div class="col-md-12 col-xs-12">
                            <SortBy
                                :options="['Sắp xếp mặc định', 'Nổi bật nhất', 'Mới nhất', 'Cũ nhất']"
                                :selected="sortOption"
                                @update:sort="
                                    sortOption = $event;
                                    fetchMotels();
                                "
                            />
                        </div>
                    </div>

                    <!-- Hiển thị loading spinner -->
                    <div v-if="isLoading" class="loading-overlay">
                        <div class="spinner"></div>
                        <p>Đang tải...</p>
                    </div>

                    <!-- Danh sách nhà trọ -->
                    <div v-else class="row">
                        <div v-for="item in listings" :key="item.id" class="col-lg-6 col-md-12">
                            <ListingItem :item="item" />
                        </div>
                        <!-- Thông báo khi không có dữ liệu -->
                        <div v-if="!listings.length" class="col-md-12 text-center">
                            <p>Không tìm thấy nhà trọ nào phù hợp.</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <Pagination
                                :current-page="currentPage"
                                :total-pages="totalPages"
                                @change:page="
                                    currentPage = $event;
                                    fetchMotels();
                                "
                            />
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-4">
                    <div class="sidebar">
                        <FilterWidget
                            :filters="filters"
                            :area-options="areaOptions"
                            :price-options="priceOptions"
                            :area-range-options="areaRangeOptions"
                            :amenities-options="amenitiesOptions"
                            @update:filters="filters = $event"
                            @apply="fetchMotels"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();

const sortOption = ref('Sắp xếp mặc định');
const currentPage = ref(0);
const totalPages = ref(0);
const total = ref(0);
const listings = ref([]);
const isLoading = ref(false); // Biến isLoading

// Khởi tạo bộ lọc từ query string
const filters = ref({
    keyword: route.query.keyword || '',
    area: route.query.area || '',
    priceRange: route.query.priceRange || '',
    areaRange: '',
    amenities: route.query.amenities ? (Array.isArray(route.query.amenities) ? route.query.amenities : [route.query.amenities]) : []
});

const areaOptions = ref([]);
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

const amenitiesOptions = ref([]);

const { $api } = useNuxtApp();

const fetchMotels = async () => {
    isLoading.value = true; // Bật loading
    try {
        const response = await $api('/motels/search', {
            method: 'GET',
            query: {
                keyword: filters.value.keyword || undefined,
                area: filters.value.area || undefined,
                priceRange: filters.value.priceRange || undefined,
                areaRange: filters.value.areaRange || undefined,
                amenities: filters.value.amenities.length ? filters.value.amenities : undefined,
                sort: sortOption.value,
                page: currentPage.value,
                per_page: 6
            }
        });

        listings.value = response.data.map(item => ({
            id: item.id,
            slug: item.slug,
            image: item.image,
            district: item.district_name,
            name: item.name,
            address: item.address,
            price: new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(item.price),
            availableRooms: item.room_count
        }));
        currentPage.value = response.current_page;
        totalPages.value = response.total_pages;
        total.value = response.total;

        if (!listings.value.length) {
            currentPage.value = 0;
            totalPages.value = 0;
        }
    } catch (error) {
        console.error('Lỗi khi lấy danh sách nhà trọ:', error);
        listings.value = [];
        total.value = 0;
    } finally {
        isLoading.value = false; // Tắt loading
    }
};

onMounted(async () => {
    isLoading.value = true; // Bật loading khi tải dữ liệu ban đầu
    try {
        const districtsResponse = await $api('/districts', { method: 'GET' });
        areaOptions.value = districtsResponse.data.map(d => d.name);

        const amenitiesResponse = await $api('/amenities', { method: 'GET' });
        amenitiesOptions.value = amenitiesResponse.data;

        await fetchMotels();
    } catch (error) {
        console.error('Lỗi khi tải dữ liệu ban đầu:', error);
    } finally {
        isLoading.value = false; // Tắt loading
    }
});
</script>

<style scoped>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8); /* Nền trắng mờ */
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999; /* Đảm bảo overlay hiển thị trên cùng */
}

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #f91942;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

p {
    color: #333;
    margin-top: 10px;
    font-size: 16px;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>
