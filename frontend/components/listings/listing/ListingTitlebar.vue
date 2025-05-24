<!-- components/ListingTitlebar.vue -->
<template>
    <div id="titlebar" class="listing-titlebar">
        <div class="listing-titlebar-title">
            <h2>
                {{ title }} <span class="listing-tag">{{ availableRooms }} phòng trống</span>
            </h2>
            <span>
                <a href="#listing-location" class="listing-address"> <i class="fa fa-map-marker"></i>{{ address }} </a>
            </span>
            <div v-if="price" class="star-rating">
                <div class="rating-counter">
                    <a href="#listing-reviews">Giá từ {{ price }} / tháng</a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: String,
    address: String,
    price: String,
    availableRooms: Number
});
</script>
