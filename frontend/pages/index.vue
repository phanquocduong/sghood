<!-- Template hiển thị giao diện trang chủ -->
<template>
    <!-- Hiển thị component Loading khi dữ liệu đang tải -->
    <Loading :is-loading="isLoading" />

    <!-- Component SearchBanner để tìm kiếm phòng trọ -->
    <SearchBanner
        :search="search"
        :districts="districts.map(d => d.name)"
        :price-options="priceOptions"
        @update:search="search = $event"
        @search="handleSearch"
    />

    <div>
        <!-- Component hiển thị các khu vực nổi bật -->
        <SectionFeaturedDistricts :districts="districts" />
        <!-- Component hiển thị các nhà trọ nổi bật -->
        <SectionFeaturedMotels />

        <!-- Phần giới thiệu quy trình tìm trọ -->
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <h2 class="headline centered margin-top-80">
                        <strong class="headline-with-separator">Tìm Phòng Trọ Dễ Dàng Chỉ Với Vài Bước</strong>
                        <span class="margin-top-25">
                            Chọn phòng phù hợp, đặt lịch xem trực tiếp và ký hợp đồng an tâm chỉ trong vài bước đơn giản.
                        </span>
                    </h2>
                </div>
            </div>

            <!-- Hiển thị các bước hướng dẫn tìm trọ -->
            <div class="row icons-container">
                <div class="col-md-4">
                    <div class="icon-box-2 with-line">
                        <i class="im im-icon-Map2"></i>
                        <h3>Tìm Trọ Phù Hợp</h3>
                        <p>Sử dụng bộ lọc để tìm trọ theo khu vực, giá, diện tích, tiện ích...</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="icon-box-2 with-line">
                        <i class="im im-icon-Mail-withAtSign"></i>
                        <h3>Đặt Lịch Xem Trọ</h3>
                        <p>Gửi yêu cầu hẹn giờ xem trọ với SGHood ngay trên hệ thống.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="icon-box-2">
                        <i class="im im-icon-Checked-User"></i>
                        <h3>Ký Hợp Đồng & Đặt Cọc</h3>
                        <p>Sau khi xem trọ ưng ý, tiến hành đặt phòng, ký hợp đồng và đặt cọc nhanh chóng.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phần hiển thị bài viết mới -->
        <section class="fullwidth border-top margin-top-70 padding-top-75 padding-bottom-75" data-background-color="#fff">
            <div class="container">
                <div class="row">
                    <div class="col-md-12">
                        <h3 class="headline centered margin-bottom-50">
                            <strong class="headline-with-separator">Bài Viết Mới</strong>
                        </h3>
                    </div>
                </div>

                <div class="row">
                    <!-- Component Blogs hiển thị danh sách bài viết -->
                    <Blogs />
                    <div class="col-md-12 centered-content">
                        <!-- Nút dẫn đến trang danh sách bài viết -->
                        <NuxtLink to="/chia-se-kinh-nghiem" class="button border margin-top-30"> Xem thêm </NuxtLink>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

// Khởi tạo các biến trạng thái
const { $api } = useNuxtApp(); // Lấy đối tượng API từ Nuxt plugin
const router = useRouter(); // Sử dụng router để điều hướng
const config = useState('configs'); // Lấy cấu hình từ state toàn cục

// Khởi tạo các ref để lưu trữ dữ liệu tìm kiếm, danh sách quận và tùy chọn giá
const search = ref({ keyword: '', district: '', priceRange: '' });
const districts = ref([]);
const isLoading = ref(true);
const priceOptions = ref([]);

// Hàm chạy khi component được mount
onMounted(async () => {
    // Sử dụng requestAnimationFrame để đảm bảo DOM đã sẵn sàng
    requestAnimationFrame(async () => {
        try {
            // Kiểm tra và parse các tùy chọn giá từ cấu hình
            if (config.value?.price_filter_options) {
                priceOptions.value = JSON.parse(config.value.price_filter_options) || [];
            }
            // Gọi API để lấy danh sách quận
            const response = await $api('/districts', { method: 'GET' });
            districts.value = response.data;
        } catch (error) {
            console.error('Lỗi khi tải dữ liệu:', error); // Ghi log lỗi nếu có
        } finally {
            isLoading.value = false; // Tắt trạng thái loading sau khi hoàn tất
        }
    });
});

// Hàm xử lý tìm kiếm, chuyển hướng đến trang danh sách nhà trọ với query params
const handleSearch = () => {
    router.push({
        path: '/danh-sach-nha-tro',
        query: {
            keyword: search.value.keyword || undefined,
            district: search.value.district || undefined,
            priceRange: search.value.priceRange || undefined
        }
    });
};
</script>

<!-- CSS tùy chỉnh cho component -->
<style scoped>
/* CSS cho lớp phủ loading */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    transition: opacity 0.3s ease;
}

/* CSS cho spinner loading */
.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #f91942;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* CSS cho văn bản trong lớp phủ loading */
.loading-overlay p {
    color: #333;
    margin-top: 10px;
    font-size: 16px;
}

/* Keyframes cho hiệu ứng quay của spinner */
@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>
