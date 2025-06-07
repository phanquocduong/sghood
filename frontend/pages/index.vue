<template>
    <SearchBanner
        :search="search"
        :districts="districts.map(d => d.name)"
        :price-options="priceOptions"
        @update:search="search = $event"
        @search="handleSearch"
    />

    <SectionFeaturedDistricts :districts="districts" />

    <SectionFeaturedMotels />

    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <h2 class="headline centered margin-top-80">
                    <strong class="headline-with-separator">Tìm Phòng Trọ Dễ Dàng Chỉ Với Vài Bước</strong>
                    <span class="margin-top-25"
                        >Chọn phòng phù hợp, đặt lịch xem trực tiếp và ký hợp đồng an tâm chỉ trong vài bước đơn giản.</span
                    >
                </h2>
            </div>
        </div>

        <div class="row icons-container">
            <!-- Stage -->
            <div class="col-md-4">
                <div class="icon-box-2 with-line">
                    <i class="im im-icon-Map2"></i>
                    <h3>Tìm Phòng Phù Hợp</h3>
                    <p>Sử dụng bộ lọc để tìm trọ theo khu vực, giá, diện tích, tiện ích...</p>
                </div>
            </div>

            <!-- Stage -->
            <div class="col-md-4">
                <div class="icon-box-2 with-line">
                    <i class="im im-icon-Mail-withAtSign"></i>
                    <h3>Đặt Lịch Xem Phòng</h3>
                    <p>Gửi yêu cầu hẹn giờ xem phòng với Trọ Việt ngay trên hệ thống.</p>
                </div>
            </div>

            <!-- Stage -->
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
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const { $api } = useNuxtApp();
const router = useRouter();

const search = ref({ keyword: '', district: '', priceRange: '' });
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
            district: search.value.district || undefined,
            priceRange: search.value.priceRange || undefined
        }
    });
};

onMounted(async () => {
    try {
        const response = await $api('/districts', { method: 'GET' });
        districts.value = response.data;
    } catch (error) {
        console.error('Lỗi khi tải danh sách quận:', error);
    }
});
</script>

<style scoped></style>
