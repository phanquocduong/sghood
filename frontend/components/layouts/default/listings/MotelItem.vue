<template>
    <NuxtLink :to="`/danh-sach-nha-tro/${item.slug}`" class="listing-item-container">
        <div class="listing-item">
            <img :src="`${config.public.baseUrl}${item.mainImage}`" :alt="item.name" />
            <div class="listing-item-details">
                <ul>
                    <li>Còn {{ item.availableRooms }} phòng trống</li>
                </ul>
            </div>
            <div class="listing-item-content">
                <span class="tag" id="tag">{{ item.district }}</span>
                <h3>{{ item.name }}</h3>
                <span>{{ item.address }}</span>
            </div>
        </div>
        <div class="star-rating">
            <div class="rating-counter">Giá từ {{ item.minPrice }}/tháng</div>
        </div>
    </NuxtLink>
</template>

<script setup>
const config = useRuntimeConfig();
defineProps(['item']);
</script>

<style>
.listing-item-content span {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

.listing-item-details ul {
    background-color: rgba(255, 255, 255, 0.8); /* Nền sáng với độ trong suốt */
    padding: 5px 10px !important;
    border-radius: 5px;
    border: 1px solid #ddd; /* Viền nhẹ để nổi bật */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Hiệu ứng bóng nhẹ */
    margin: 0;
    padding: 0;
    list-style: none;
}

.listing-item-details li {
    font-size: 14px;
    font-weight: bold; /* Làm đậm chữ */
    color: #333; /* Màu chữ đậm */
    margin-bottom: 3px;
}

.listing-item-details li + li {
    margin-left: 3px;
}

.listing-item-details li:before {
    content: '🏠 '; /* Biểu tượng ngôi nhà Unicode */
    color: #28a745; /* Màu xanh lá, có thể thay đổi */
}

.listing-item img {
    filter: blur(3px); /* Độ mờ, đơn vị là pixel (px), có thể điều chỉnh từ 1px đến 5px) */
    transition: filter 0.3s ease; /* Hiệu ứng mượt mà khi hover */
}

.listing-item:hover img {
    filter: blur(5px); /* Tăng độ mờ khi di chuột qua */
}
</style>
