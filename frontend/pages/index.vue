<template>
    <Loading :is-loading="isLoading" />

    <SearchBanner
        :search="search"
        :districts="districts.map(d => d.name)"
        :price-options="priceOptions"
        @update:search="search = $event"
        @search="handleSearch"
    />

    <div>
        <SectionFeaturedDistricts :districts="districts" />
        <SectionFeaturedMotels />

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
                    <Blogs />
                    <div class="col-md-12 centered-content">
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

const { $api } = useNuxtApp();
const router = useRouter();
const config = useState('configs');

const search = ref({ keyword: '', district: '', priceRange: '' });
const districts = ref([]);
const isLoading = ref(true);
const priceOptions = ref([]);

onMounted(async () => {
    requestAnimationFrame(async () => {
        try {
            if (config.value?.price_filter_options) {
                priceOptions.value = JSON.parse(config.value.price_filter_options) || [];
            }
            const response = await $api('/districts', { method: 'GET' });
            districts.value = response.data;
        } catch (error) {
            console.error('Lỗi khi tải dữ liệu:', error);
        } finally {
            isLoading.value = false;
        }
    });
});

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

<style scoped>
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

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #f91942;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

.loading-overlay p {
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
