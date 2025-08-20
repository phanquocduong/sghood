<template>
    <Loading :is-loading="isLoading" />
    <div class="container">
        <!-- Content -->
        <div class="row sticky-wrapper">
            <div class="col-lg-8 col-md-8 padding-right-30">
                <ListingTitlebar
                    :title="motel.name"
                    :address="motel.address"
                    :district="motel.district_name"
                    :description="motel.description"
                />

                <div class="listing-section">
                    <ListingGallery :images="motel.images" />
                    <ListingAmenities :amenities="motel.amenities" />
                </div>

                <div id="listing-location" class="listing-section">
                    <h3 class="listing-desc-headline margin-top-60 margin-bottom-30">Vị trí</h3>
                    <iframe
                        :src="motel.map_url"
                        width="100%"
                        height="400"
                        style="border: 0"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                    ></iframe>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4 col-md-4 margin-top-75 sticky">
                <ViewingScheduleForm :motel-id="motel.id" />
                <ListingPricing :title="'Phí hàng tháng'" :fees="motel.fees" />
            </div>
        </div>

        <!-- Danh sách phòng -->
        <div id="listing-pricing-list" class="listing-section">
            <h3 class="listing-desc-headline margin-top-70 margin-bottom-30">Danh sách phòng</h3>

            <div class="row">
                <div v-for="room in motel.rooms" :key="room.id" class="col-lg-4 col-md-6">
                    <RoomItem :item="room" @open-modal="openModal" />
                </div>
            </div>
        </div>

        <!-- Modal -->
        <RoomModal :is-open="isModalOpen" :room="selectedRoom" @close="closeModal" />
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useHead } from '#app';
import { useFormatPrice } from '~/composables/useFormatPrice';

const { $api } = useNuxtApp();
const route = useRoute();
const motel = ref({});
const isModalOpen = ref(false);
const selectedRoom = ref(null);
const isLoading = ref(false);

// Sử dụng composable
const { formatPrice, formatFees } = useFormatPrice();

// Tính toán tiêu đề và mô tả SEO động dựa trên dữ liệu nhà trọ
const seoTitle = computed(() => {
    return motel.value.name ? `SGHood - ${motel.value.name} - Nhà Trọ Tại ${motel.value.district_name}` : 'SGHood - Chi Tiết Nhà Trọ';
});

const seoDescription = computed(() => {
    return motel.value.description
        ? `${motel.value.description.substring(0, 150)}... Tìm hiểu nhà trọ tại ${motel.value.district_name}, TP. Hồ Chí Minh với SGHood.`
        : `Khám phá nhà trọ tại ${motel.value.district_name}, TP. Hồ Chí Minh với SGHood. Đặt phòng trực tuyến, xem thông tin phòng trống và tiện ích chi tiết.`;
});

// Cấu hình SEO cho trang chi tiết nhà trọ
useHead({
    title: seoTitle,
    meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        {
            hid: 'description',
            name: 'description',
            content: seoDescription
        },
        {
            name: 'keywords',
            content: computed(() => {
                return `SGHood, nhà trọ ${motel.value.district_name}, thuê trọ TP. Hồ Chí Minh, đặt phòng trực tuyến, nhà trọ giá rẻ, ${
                    motel.value.name || 'nhà trọ'
                }`;
            })
        },
        { name: 'author', content: 'SGHood Team' },
        // Open Graph
        {
            property: 'og:title',
            content: seoTitle
        },
        {
            property: 'og:description',
            content: seoDescription
        },
        { property: 'og:type', content: 'website' },
        {
            property: 'og:url',
            content: computed(() => `https://sghood.com.vn/danh-sach-nha-tro/${route.params.slug}`)
        }
    ]
});

// Mở modal
const openModal = room => {
    selectedRoom.value = {
        ...room
    };
    isModalOpen.value = true;
};

// Đóng modal
const closeModal = () => {
    isModalOpen.value = false;
    selectedRoom.value = null;
};

// Fetch dữ liệu từ API khi component được mounted
onMounted(async () => {
    isLoading.value = true;
    try {
        const response = await $api(`/motels/${route.params.slug}`, { method: 'GET' });
        const data = response.data;

        // Định dạng giá và phí trước khi gán vào motel
        data.rooms = data.rooms.map(room => ({
            ...room,
            price: formatPrice(room.price)
        }));
        data.fees = formatFees(data.fees);
        motel.value = data;
    } catch (error) {
        console.error('Lỗi khi lấy dữ liệu chi tiết nhà trọ', error);
    } finally {
        isLoading.value = false;
    }
});
</script>

<style scoped>
.spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
    margin-right: 8px;
    vertical-align: middle;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
