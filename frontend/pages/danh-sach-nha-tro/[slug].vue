<!-- Template cho trang chi tiết nhà trọ -->
<template>
    <!-- Hiển thị trạng thái đang tải -->
    <Loading :is-loading="isLoading" />
    <div class="container">
        <!-- Nội dung chính -->
        <div class="row sticky-wrapper">
            <div class="col-lg-8 col-md-8 padding-right-30">
                <!-- Tiêu đề và thông tin cơ bản -->
                <ListingTitlebar
                    :title="motel.name"
                    :address="motel.address"
                    :district="motel.district_name"
                    :description="motel.description"
                />

                <!-- Phần ảnh và tiện ích -->
                <div class="listing-section">
                    <ListingGallery :images="motel.images" />
                    <ListingAmenities :amenities="motel.amenities" />
                </div>

                <!-- Phần bản đồ vị trí -->
                <div id="listing-location" class="listing-section">
                    <h3 class="listing-desc-headline margin-top-60 margin-bottom-30">Vị trí</h3>
                    <!-- Iframe hiển thị Google Maps -->
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

            <!-- Sidebar cố định -->
            <div class="col-lg-4 col-md-4 margin-top-75 sticky">
                <!-- Form đặt lịch xem trọ -->
                <ViewingScheduleForm :motel-id="motel.id" />
                <!-- Thông tin giá và phí -->
                <ListingPricing :title="'Phí hàng tháng'" :fees="motel.fees" />
            </div>
        </div>

        <!-- Danh sách phòng -->
        <div id="listing-pricing-list" class="listing-section">
            <h3 class="listing-desc-headline margin-top-70 margin-bottom-30">Danh sách phòng</h3>

            <div class="row">
                <!-- Hiển thị từng phòng -->
                <div v-for="room in motel.rooms" :key="room.id" class="col-lg-4 col-md-6">
                    <RoomItem :item="room" @open-modal="openModal" />
                </div>
            </div>
        </div>

        <!-- Modal hiển thị chi tiết phòng -->
        <RoomModal :is-open="isModalOpen" :room="selectedRoom" @close="closeModal" />
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import { useHead } from '#app';
import { useFormatPrice } from '~/composables/useFormatPrice';

const { $api } = useNuxtApp(); // Lấy đối tượng API từ Nuxt
const route = useRoute(); // Lấy thông tin route hiện tại
const motel = ref({}); // Biến lưu trữ thông tin nhà trọ
const isModalOpen = ref(false); // Trạng thái hiển thị modal
const selectedRoom = ref(null); // Phòng được chọn để hiển thị trong modal
const isLoading = ref(false); // Trạng thái đang tải

// Sử dụng composable để định dạng giá và phí
const { formatPrice, formatFees } = useFormatPrice();

// Tính toán tiêu đề SEO động
const seoTitle = computed(() => {
    return motel.value.name ? `SGHood - ${motel.value.name} - Nhà Trọ Tại ${motel.value.district_name}` : 'SGHood - Chi Tiết Nhà Trọ';
});

// Tính toán mô tả SEO động
const seoDescription = computed(() => {
    return motel.value.description
        ? `${motel.value.description.substring(0, 150)}... Tìm hiểu nhà trọ tại ${motel.value.district_name}, TP. Hồ Chí Minh với SGHood.`
        : `Khám phá nhà trọ tại ${motel.value.district_name}, TP. Hồ Chí Minh với SGHood. Đặt phòng trực tuyến, xem thông tin phòng trống và tiện ích chi tiết.`;
});

// Cấu hình SEO cho trang chi tiết nhà trọ
useHead({
    title: seoTitle, // Tiêu đề SEO động
    meta: [
        { charset: 'utf-8' }, // Thiết lập mã hóa ký tự
        { name: 'viewport', content: 'width=device-width, initial-scale=1' }, // Responsive viewport
        {
            hid: 'description',
            name: 'description',
            content: seoDescription // Mô tả SEO động
        },
        {
            name: 'keywords',
            content: computed(() => {
                return `SGHood, nhà trọ ${motel.value.district_name}, thuê trọ TP. Hồ Chí Minh, đặt phòng trực tuyến, nhà trọ giá rẻ, ${
                    motel.value.name || 'nhà trọ'
                }`; // Từ khóa SEO động
            })
        },
        { name: 'author', content: 'SGHood Team' }, // Tác giả
        // Open Graph metadata
        {
            property: 'og:title',
            content: seoTitle // Tiêu đề Open Graph
        },
        {
            property: 'og:description',
            content: seoDescription // Mô tả Open Graph
        },
        { property: 'og:type', content: 'website' }, // Loại nội dung Open Graph
        {
            property: 'og:url',
            content: computed(() => `https://sghood.com.vn/danh-sach-nha-tro/${route.params.slug}`) // URL Open Graph
        }
    ]
});

// Hàm mở modal chi tiết phòng
const openModal = room => {
    selectedRoom.value = {
        ...room
    }; // Lưu thông tin phòng được chọn
    isModalOpen.value = true; // Hiển thị modal
};

// Hàm đóng modal
const closeModal = () => {
    isModalOpen.value = false; // Ẩn modal
    selectedRoom.value = null; // Xóa phòng được chọn
};

// Lấy dữ liệu nhà trọ từ API khi component được mount
onMounted(async () => {
    isLoading.value = true; // Bật trạng thái đang tải
    try {
        const response = await $api(`/motels/${route.params.slug}`, { method: 'GET' }); // Gọi API
        const data = response.data;

        // Định dạng giá và phí trước khi gán
        data.rooms = data.rooms.map(room => ({
            ...room,
            price: formatPrice(room.price) // Định dạng giá phòng
        }));
        data.fees = formatFees(data.fees); // Định dạng phí
        motel.value = data; // Gán dữ liệu vào biến motel
    } catch (error) {
        console.error('Lỗi khi lấy dữ liệu chi tiết nhà trọ', error); // Ghi log lỗi
    } finally {
        isLoading.value = false; // Tắt trạng thái đang tải
    }
});
</script>

<!-- CSS tùy chỉnh cho trang -->
<style scoped>
/* Spinner loading */
.spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite; /* Hiệu ứng quay */
    margin-right: 8px;
    vertical-align: middle;
}

/* Hiệu ứng quay cho spinner */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* CSS cho nút bị vô hiệu hóa */
.button:disabled {
    opacity: 0.6;
    cursor: not-allowed; /* Biểu thị con trỏ không thể nhấn */
}
</style>
