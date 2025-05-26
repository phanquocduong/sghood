<!-- ListingItem.vue -->
<template>
    <NuxtLink to="/nha-tro/nha-tro-1" class="listing-item-container">
        <div class="listing-item">
            <img :src="item.image" alt="" />
            <div class="listing-item-details">
                <ul>
                    <li v-for="amenitiy in item.amenities" :key="amenitiy">{{ amenitiy }}</li>
                </ul>
            </div>
            <div class="listing-item-content">
                <span class="tag">{{ item.status }}</span>
                <h3>{{ item.name }}</h3>
                <span>Giá {{ item.price }} / tháng</span>
            </div>
        </div>
        <div class="star-rating">
            <div class="rating-counter">Diện tích: {{ item.area }}m<sup>2</sup></div>
        </div>
    </NuxtLink>
</template>

<script setup>
defineProps(['item']);
</script>
