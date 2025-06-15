<template>
    <div>
        <!-- Banner luôn hiển thị -->
        <SearchBanner
            :search="search"
            :districts="districts.map(d => d.name)"
            :price-options="priceOptions"
            @update:search="search = $event"
            @search="handleSearch"
        />

        <!-- Hiệu ứng loading -->
        <div class="loading-overlay" v-show="isLoading">
            <div class=""></div>
            <p>Đang tải dữ liệu</p>
        </div>

        <!-- Nội dung chỉ hiển thị khi load xong -->
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
                            <h3>Tìm Phòng Phù Hợp</h3>
                            <p>Sử dụng bộ lọc để tìm trọ theo khu vực, giá, diện tích, tiện ích...</p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="icon-box-2 with-line">
                            <i class="im im-icon-Mail-withAtSign"></i>
                            <h3>Đặt Lịch Xem Phòng</h3>
                            <p>Gửi yêu cầu hẹn giờ xem phòng với Trọ Việt ngay trên hệ thống.</p>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="icon-box-2">
                            <i class="im im-icon-Checked-User"></i>
                            <h3>Ký Hợp Đồng & Đặt Cọc</h3>
                            <p>Sau khi xem phòng ưng ý, tiến hành đặt phòng, ký hợp đồng và đặt cọc nhanh chóng.</p>
                        </div>
                    </div>
                </div>
            </div>

            <SectionRoomsAvailableSoon />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const { $api } = useNuxtApp();
const router = useRouter();

const search = ref({ keyword: '', district: '', priceRange: '' });
const districts = ref([]);
const isLoading = ref(true);

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
            district: search.value.district || undefined,
            priceRange: search.value.priceRange || undefined
        }
    });
};

onMounted(async () => {
    requestAnimationFrame(async () => {
        try {
            const response = await $api('/districts', { method: 'GET' });
            districts.value = response.data;
        } catch (error) {
            console.error('Lỗi khi tải danh sách quận:', error);
        } finally {
            isLoading.value = false;
        }
    });
});
</script>

<style scoped>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.85);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    transition: opacity 0.3s ease;
}

.loading-overlay[style*='display: none'] {
    opacity: 0;
    pointer-events: none;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #ddd;
    border-top: 5px solid #f91942;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0);
    }
    100% {
        transform: rotate(360deg);
    }
}
</style>
