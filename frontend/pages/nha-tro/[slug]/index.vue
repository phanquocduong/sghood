<!-- nha-tro/[slug]/index.vue -->
<template>
    <div class="container">
        <!-- Content -->
        <div class="row sticky-wrapper">
            <div class="col-lg-8 col-md-8 padding-right-30">
                <ListingTitlebar :title="motel.name" :address="motel.address" :tag="motel.district_name" />

                <div class="listing-section">
                    <p>{{ motel.description }}</p>

                    <ListingAmenities :amenities="motel.amenities" />

                    <ListingSliderSmall :images="motel.images" />
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4 col-md-4 margin-top-75 sticky">
                <ListingPricing :title="'Phí hàng tháng'" :fees="motel.fees" />
            </div>
        </div>

        <ListingMap :map-url="motel.map_url" />

        <!-- Danh sách phòng trống -->
        <div id="listing-pricing-list" class="listing-section">
            <h3 class="listing-desc-headline margin-top-70 margin-bottom-30">Danh sách phòng trống</h3>

            <div class="row">
                <div v-for="room in motel.rooms" :key="room.id" class="col-lg-4 col-md-6">
                    <ListingItemChild :item="room" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';

const { $api } = useNuxtApp();
const route = useRoute();
const motel = ref({});

// Hàm định dạng giá tiền
const formatPrice = price => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
};

// Hàm định dạng phí
const formatFees = fees => {
    return fees.map(fee => ({
        name: fee.name,
        price: `${formatPrice(fee.price)}/${fee.unit}`
    }));
};

// Fetch dữ liệu từ API khi component được mounted
onMounted(async () => {
    try {
        const response = await $api(`/motels/${route.params.slug}`, { method: 'GET' });
        const data = response.data;

        // Định dạng giá và phí trước khi gán vào motel
        data.rooms = data.rooms.map(room => ({
            ...room,
            price: new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(room.price)
        }));
        console.log(data.rooms);
        data.fees = formatFees(data.fees);
        motel.value = data;
    } catch (error) {
        console.error('Error fetching motel details:', error);
    }
});
</script>
