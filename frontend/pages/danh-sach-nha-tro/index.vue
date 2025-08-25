<!-- Template cho trang danh sách nhà trọ -->
<template>
    <div class="container" style="margin-top: 50px">
        <div class="row">
            <!-- Cột chính chứa danh sách nhà trọ -->
            <div class="col-lg-9 col-md-8 padding-right-30">
                <!-- Tiêu đề và bộ lọc sắp xếp -->
                <div class="row margin-bottom-25" style="display: flex; align-items: flex-end">
                    <div class="col-md-6 col-xs-12">
                        <h2>Danh sách nhà trọ</h2>
                        <span>Có {{ total }} kết quả phù hợp</span>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <!-- Component sắp xếp -->
                        <SortBy
                            :options="['Sắp xếp mặc định', 'Nổi bật nhất', 'Mới nhất', 'Cũ nhất']"
                            @update:sort="
                                sortOption = $event;
                                fetchMotels();
                            "
                        />
                    </div>
                </div>

                <!-- Hiển thị trạng thái đang tải -->
                <Loading :is-loading="isLoading" />

                <!-- Danh sách nhà trọ -->
                <div class="row">
                    <!-- Hiển thị danh sách nếu có kết quả -->
                    <div v-if="listings.length" v-for="item in listings" :key="item.id" class="col-lg-6 col-md-12">
                        <MotelItem :item="item" />
                    </div>
                    <!-- Thông báo khi không có kết quả -->
                    <div v-else class="col-md-12 text-center">
                        <p>Không tìm thấy nhà trọ nào phù hợp.</p>
                    </div>
                </div>

                <!-- Phân trang -->
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

            <!-- Cột sidebar chứa bộ lọc -->
            <div class="col-lg-3 col-md-4">
                <div class="sidebar" style="margin-top: 25px">
                    <!-- Component bộ lọc -->
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
const { $api } = useNuxtApp(); // Lấy đối tượng API từ Nuxt
const config = useState('configs'); // Lấy cấu hình từ state toàn cục

// Biến trạng thái
const sortOption = ref('Sắp xếp mặc định'); // Lựa chọn sắp xếp
const currentPage = ref(0); // Trang hiện tại
const totalPages = ref(0); // Tổng số trang
const total = ref(0); // Tổng số nhà trọ
const listings = ref([]); // Danh sách nhà trọ
const isLoading = ref(false); // Trạng thái đang tải

// Khởi tạo bộ lọc từ query string
const filters = ref({
    keyword: route.query.keyword || '',
    district: route.query.district || '',
    priceRange: route.query.priceRange || '',
    areaRange: '',
    amenities: []
});

// Danh sách tùy chọn bộ lọc
const districts = ref([]); // Danh sách quận
const priceOptions = ref([]); // Tùy chọn khoảng giá
const areaRangeOptions = ref([]); // Tùy chọn khoảng diện tích
const amenitiesOptions = ref([]); // Tùy chọn tiện ích

// Cấu hình SEO cho trang danh sách nhà trọ
useHead({
    title: 'SGHood - Danh Sách Nhà Trọ Tại TP. Hồ Chí Minh', // Tiêu đề trang
    meta: [
        { charset: 'utf-8' }, // Thiết lập mã hóa ký tự
        { name: 'viewport', content: 'width=device-width, initial-scale=1' }, // Responsive viewport
        {
            hid: 'description',
            name: 'description',
            content:
                'Tìm nhà trọ tại TP. Hồ Chí Minh với SGHood. Khám phá danh sách nhà trọ chất lượng, minh bạch với giá thuê và tiện ích, hỗ trợ đặt phòng trực tuyến.' // Mô tả SEO
        },
        {
            name: 'keywords',
            content:
                'SGHood, nhà trọ TP. Hồ Chí Minh, thuê nhà trọ, tìm phòng trọ, đặt phòng trực tuyến, nhà trọ giá rẻ, nhà trọ chất lượng' // Từ khóa SEO
        },
        { name: 'author', content: 'SGHood Team' }, // Tác giả
        // Open Graph metadata
        {
            property: 'og:title',
            content: 'SGHood - Danh Sách Nhà Trọ Tại TP. Hồ Chí Minh' // Tiêu đề Open Graph
        },
        {
            property: 'og:description',
            content:
                'Tìm nhà trọ tại TP. Hồ Chí Minh với SGHood. Khám phá danh sách nhà trọ chất lượng, minh bạch với giá thuê và tiện ích, hỗ trợ đặt phòng trực tuyến.' // Mô tả Open Graph
        },
        { property: 'og:type', content: 'website' }, // Loại nội dung Open Graph
        { property: 'og:url', content: 'https://sghood.com.vn/danh-sach-nha-tro' } // URL Open Graph
    ]
});

// Hàm lấy danh sách nhà trọ từ API
const fetchMotels = async () => {
    isLoading.value = true; // Bật trạng thái đang tải
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
                per_page: 6 // Số lượng nhà trọ mỗi trang
            }
        });

        // Chuyển đổi dữ liệu từ API thành định dạng hiển thị
        listings.value = response.data.map(item => ({
            id: item.id,
            slug: item.slug,
            mainImage: item.main_image,
            district: item.district_name,
            name: item.name,
            address: item.address,
            minPrice: new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(item.min_price), // Định dạng giá tiền
            availableRooms: item.room_count
        }));
        currentPage.value = response.current_page; // Cập nhật trang hiện tại
        totalPages.value = response.total_pages; // Cập nhật tổng số trang
        total.value = response.total; // Cập nhật tổng số nhà trọ

        // Xử lý trường hợp không có kết quả
        if (!listings.value.length) {
            currentPage.value = 0;
            totalPages.value = 0;
        }
    } catch (error) {
        console.error('Lỗi khi lấy danh sách nhà trọ:', error); // Ghi log lỗi
        listings.value = []; // Xóa danh sách
        total.value = 0; // Đặt tổng số về 0
    } finally {
        isLoading.value = false; // Tắt trạng thái đang tải
    }
};

// Khởi tạo dữ liệu khi component được mount
onMounted(async () => {
    isLoading.value = true; // Bật trạng thái đang tải
    try {
        // Lấy tùy chọn khoảng giá từ cấu hình
        if (config.value?.price_filter_options) {
            priceOptions.value = JSON.parse(config.value.price_filter_options) || [];
        }

        // Lấy tùy chọn khoảng diện tích từ cấu hình
        if (config.value?.area_filter_options) {
            areaRangeOptions.value = JSON.parse(config.value.area_filter_options) || [];
        }

        // Lấy danh sách quận từ API
        const districtsResponse = await $api('/districts', { method: 'GET' });
        districts.value = districtsResponse.data.map(d => d.name);

        // Lấy danh sách tiện ích từ API
        const amenitiesResponse = await $api('/amenities', { method: 'GET' });
        amenitiesOptions.value = amenitiesResponse.data;

        // Lấy danh sách nhà trọ
        await fetchMotels();
    } catch (error) {
        console.error('Lỗi khi tải dữ liệu ban đầu:', error); // Ghi log lỗi
    } finally {
        isLoading.value = false; // Tắt trạng thái đang tải
    }
});
</script>

<!-- CSS tùy chỉnh cho trang -->
<style scoped>
/* Lớp phủ khi đang tải */
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

/* Spinner loading */
.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3; /* Viền xám nhạt */
    border-top: 5px solid #f91942; /* Viền trên đỏ */
    border-radius: 50%;
    animation: spin 1s linear infinite; /* Hiệu ứng quay */
}

/* Văn bản thông báo */
p {
    color: #333;
    margin-top: 10px;
    font-size: 16px;
}

/* Hiệu ứng quay cho spinner */
@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>
