<!-- ListingItem.vue -->
<template>
    <NuxtLink to="/nha-tro/nha-tro-1" class="listing-item-container">
        <div class="listing-item">
            <img :src="item.image" alt="" />
            <div class="listing-item-details">
                <ul>
                    <li>Còn {{ item.availableRooms }} phòng trống</li>
                </ul>
            </div>
            <div class="listing-item-content">
                <span class="tag">{{ item.district }}</span>
                <h3>{{ item.address }}</h3>
                <span>Giá từ {{ item.price }} / tháng</span>
            </div>
            <span class="like-icon"></span>
        </div>
        <div class="star-rating">
            <div class="rating-counter">Diện tích từ {{ item.area }}m<sup>2</sup></div>
        </div>
    </NuxtLink>
</template>

<script setup>
defineProps(['item']);
</script>
