<!-- nha-tro/[slug]/[id].vue -->
<template>
    <!-- Content -->
    <div class="container">
        <div class="row sticky-wrapper">
            <div class="col-lg-8 col-md-8 padding-right-30">
                <ListingTitlebar :title="room.name" :location="room.motel_name" :tag="room.status" />

                <div class="listing-section">
                    <p>{{ room.description }}</p>
                    <p>Diện tích: {{ room.area }}m<sup>2</sup></p>

                    <ListingAmenities :amenities="room.amenities" />
                    <ListingGallery :images="room.images" />
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4 col-md-4 margin-top-75 sticky">
                <ListingPricing :title="'Giá thuê'" :fees="[{ name: room.name, price: room.price }]" />
                <ViewingScheduleForm />
            </div>
        </div>
        <!-- Food Menu -->
        <div id="listing-pricing-list" class="listing-section">
            <h3 class="listing-desc-headline margin-top-70 margin-bottom-30">Các phòng còn trống khác</h3>

            <div class="row">
                <div v-for="item in otherRooms" :key="item.id" class="col-lg-4 col-md-6">
                    <RoomItem :item="item" />
                </div>
            </div>
        </div>
        <!-- Food Menu / End -->
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';

const { $api } = useNuxtApp();
const route = useRoute();
const room = ref({});
const otherRooms = ref([]);

// Hàm định dạng giá tiền
const formatPrice = price => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
};

// Fetch dữ liệu từ API khi component được mounted
onMounted(async () => {
    try {
        const response = await $api(`/motels/${route.params.slug}/rooms/${route.params.id}`, { method: 'GET' });
        const data = response.data;

        // Định dạng giá phòng
        data.room.price = formatPrice(data.room.price);
        data.room.images = data.room.images.map(img => ({ src: img }));
        room.value = data.room;

        // Định dạng danh sách phòng trống khác
        otherRooms.value = data.other_rooms.map(item => ({
            ...item,
            price: formatPrice(item.price),
            main_image: item.main_image,
            status: item.status,
            amenities: item.amenities
        }));
    } catch (error) {
        console.error('Error fetching room details:', error);
    }
});
</script>
