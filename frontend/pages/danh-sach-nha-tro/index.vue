<template>
    <div class="container" style="margin-top: 50px">
        <div class="row">
            <div class="col-lg-9 col-md-8 padding-right-30">
                <div class="row margin-bottom-25" style="display: flex; align-items: flex-end">
                    <div class="col-md-6 col-xs-12">
                        <h2>Danh sách nhà trọ</h2>
                        <span>Có {{ total }} kết quả phù hợp</span>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <SortBy
                            :options="['Sắp xếp mặc định', 'Nổi bật nhất', 'Mới nhất', 'Cũ nhất']"
                            @update:sort="
                                sortOption = $event;
                                fetchMotels();
                            "
                        />
                    </div>
                </div>

                <Loading :is-loading="isLoading" />

                <div class="row">
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
                <div class="sidebar" style="margin-top: 25px">
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
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useHead } from '#app';
import { useRoute } from 'vue-router';

const route = useRoute();
const { $api } = useNuxtApp();
const config = useState('configs');

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
const priceOptions = ref([]);
const areaRangeOptions = ref([]);
const amenitiesOptions = ref([]);

// Cấu hình SEO cho trang danh sách nhà trọ
useHead({
    title: 'SGHood - Danh Sách Nhà Trọ Tại TP. Hồ Chí Minh',
    meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        {
            hid: 'description',
            name: 'description',
            content:
                'Tìm nhà trọ tại TP. Hồ Chí Minh với SGHood. Khám phá danh sách nhà trọ chất lượng, minh bạch với giá thuê và tiện ích, hỗ trợ đặt phòng trực tuyến.'
        },
        {
            name: 'keywords',
            content:
                'SGHood, nhà trọ TP. Hồ Chí Minh, thuê nhà trọ, tìm phòng trọ, đặt phòng trực tuyến, nhà trọ giá rẻ, nhà trọ chất lượng'
        },
        { name: 'author', content: 'SGHood Team' },
        // Open Graph
        {
            property: 'og:title',
            content: 'SGHood - Danh Sách Nhà Trọ Tại TP. Hồ Chí Minh'
        },
        {
            property: 'og:description',
            content:
                'Tìm nhà trọ tại TP. Hồ Chí Minh với SGHood. Khám phá danh sách nhà trọ chất lượng, minh bạch với giá thuê và tiện ích, hỗ trợ đặt phòng trực tuyến.'
        },
        { property: 'og:type', content: 'website' },
        { property: 'og:url', content: 'https://sghood.com.vn/danh-sach-nha-tro' }
    ]
});

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
        if (config.value?.price_filter_options) {
            priceOptions.value = JSON.parse(config.value.price_filter_options) || [];
        }

        if (config.value?.area_filter_options) {
            areaRangeOptions.value = JSON.parse(config.value.area_filter_options) || [];
        }

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
    background: white;
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
