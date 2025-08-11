<template>
    <div class="listing-item-container" @click="$emit('open-modal', item)">
        <div class="listing-item">
            <img :src="`${config.public.baseUrl}${item.main_image}`" :alt="item.name" />
            <div class="listing-item-details">
                <ul>
                    <li v-for="amenity in item.amenities" :key="amenity">{{ amenity }}</li>
                </ul>
            </div>
            <div class="listing-item-content">
                <span
                    class="tag"
                    :class="{
                        'status-available': item.status === 'Trống',
                        'status-rented': item.status === 'Đã thuê',
                        'status-maintenance': item.status === 'Sửa chữa'
                    }"
                    >{{ item.status }}</span
                >
                <h3>{{ item.name }}</h3>
                <span>Giá {{ item.price }}/tháng</span>
            </div>
        </div>
        <div class="star-rating">
            <div class="rating-counter">Diện tích: {{ item.area }}m<sup>2</sup></div>
        </div>
    </div>
</template>

<script setup>
const config = useRuntimeConfig();
defineProps(['item']);
defineEmits(['open-modal']);
</script>

<style scoped>
.listing-item-container {
    cursor: pointer;
    transition: transform 0.2s;
}

.listing-item-container:hover {
    transform: scale(1.02);
}

.listing-item-details li:before {
    content: '✔ '; /* Thêm dấu tích để làm nổi bật */
    color: #28a745; /* Màu xanh lá cho dấu tích */
}

.tag {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 4px;
    color: #fff;
    font-weight: bold;
}

.status-available {
    background-color: #4caf50 !important; /* Màu xanh lá cho trạng thái Trống */
}

.status-rented {
    background-color: #ee3535 !important; /* Màu đỏ cho trạng thái Đã thuê */
}

.status-maintenance {
    background-color: #ffc107 !important; /* Màu vàng cho trạng thái Sửa chữa */
}
</style>
