<template>
    <div>
        <div id="titlebar" class="gradient">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h2>Danh sách nhà trọ</h2>
                        <span>Có {{ total }} kết quả phù hợp</span>
                        <nav id="breadcrumbs">
                            <ul>
                                <li>
                                    <NuxtLink to="/">Trang chủ</NuxtLink>
                                </li>
                                <li>Danh sách nhà trọ</li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-9 col-md-8 padding-right-30">
                    <div class="row margin-bottom-25">
                        <div class="col-md-12 col-xs-12">
                            <SortBy
                                :options="['Sắp xếp mặc định', 'Nổi bật nhất', 'Mới nhất', 'Cũ nhất']"
                                @update:sort="
                                    sortOption = $event;
                                    fetchMotels();
                                "
                            />
                        </div>
                    </div>

                    <div v-if="isLoading" class="loading-overlay">
                        <div class=""></div>
                       
                    </div>

                    <div v-else class="row">
                        <div v-if="listings.length" v-for="item in listings" :key="item.id" class="col-lg-6 col-md-12">
                            <MotelItem :item="item" />
                        </div>
                        <div v-else class="col-md-12 text-center">
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
                            :districts="districts"
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
const { $api } = useNuxtApp();

const sortOption = ref('Sắp xếp mặc định');
const currentPage = ref(0);
const totalPages = ref(0);
const total = ref(0);
const listings = ref([]);
const isLoading = ref(false);

// Khởi tạo bộ lọc từ query string
const filters = ref({
    keyword: route.query.keyword || '',
    district: route.query.district || '',
    priceRange: route.query.priceRange || '',
    areaRange: '',
    amenities: []
});

const districts = ref([]);
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

const fetchMotels = async () => {
    isLoading.value = true;
    try {
        const response = await $api('/motels/search', {
            method: 'GET',
            query: {
                keyword: filters.value.keyword || undefined,
                district: filters.value.district || undefined,
                priceRange: filters.value.priceRange || undefined,
                areaRange: filters.value.areaRange || undefined,
                'amenities[]': filters.value.amenities.length ? filters.value.amenities : undefined,
                sort: sortOption.value,
                page: currentPage.value,
                per_page: 6
            }
        });

        listings.value = response.data.map(item => ({
            id: item.id,
            slug: item.slug,
            mainImage: item.main_image,
            district: item.district_name,
            name: item.name,
            address: item.address,
            minPrice: new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(item.min_price),
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
        isLoading.value = false;
    }
};

onMounted(async () => {
    isLoading.value = true;
    try {
        const districtsResponse = await $api('/districts', { method: 'GET' });
        districts.value = districtsResponse.data.map(d => d.name);

        const amenitiesResponse = await $api('/amenities', { method: 'GET' });
        amenitiesOptions.value = amenitiesResponse.data;

        await fetchMotels();
    } catch (error) {
        console.error('Lỗi khi tải dữ liệu ban đầu:', error);
    } finally {
        isLoading.value = false;
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
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
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
