<!-- ListingItem.vue -->
<template>
    <NuxtLink :to="`/nha-tro/${item.slug}`" class="listing-item-container">
        <div class="listing-item">
            <img :src="`${config.public.baseUrl}${item.image}`" alt="" />
            <div class="listing-item-details">
                <ul>
                    <li>Còn {{ item.availableRooms }} phòng trống</li>
                </ul>
            </div>
            <div class="listing-item-content">
                <span class="tag">{{ item.district }}</span>
                <h3>{{ item.name }}</h3>
                <span>{{ item.address }}</span>
            </div>
        </div>
        <div class="star-rating">
            <div class="rating-counter">Giá từ {{ item.price }}/tháng</div>
        </div>
    </NuxtLink>
</template>

<script setup>
const config = useRuntimeConfig();
defineProps(['item']);
</script>
