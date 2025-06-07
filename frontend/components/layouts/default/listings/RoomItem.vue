<!-- components/ListingItemChild.vue -->
<template>
    <NuxtLink :to="`/nha-tro/${$route.params.slug}/${item.id}`" class="listing-item-container">
        <div class="listing-item">
            <img :src="`${config.public.baseUrl}${item.image}`" alt="" />
            <div class="listing-item-details">
                <ul>
                    <li v-for="amenitiy in item.amenities" :key="amenitiy">{{ amenitiy }}</li>
                </ul>
            </div>
            <div class="listing-item-content">
                <span class="tag">{{ item.status }}</span>
                <h3>{{ item.name }}</h3>
                <span>Giá {{ item.price }}/tháng</span>
            </div>
        </div>
        <div class="star-rating">
            <div class="rating-counter">Diện tích: {{ item.area }}m<sup>2</sup></div>
        </div>
    </NuxtLink>
</template>

<script setup>
const config = useRuntimeConfig();
defineProps(['item']);
</script>
