<!-- Template cho component hiển thị một nhà trọ -->
<template>
    <NuxtLink :to="`/danh-sach-nha-tro/${item.slug}`" class="listing-item-container">
        <div class="listing-item">
            <!-- Hình ảnh chính của nhà trọ -->
            <img :src="`${config.public.baseUrl}${item.mainImage}`" :alt="item.name" />
            <!-- Thông tin chi tiết -->
            <div class="listing-item-details">
                <ul>
                    <li>Còn {{ item.availableRooms }} phòng trống</li>
                </ul>
            </div>
            <!-- Nội dung chính -->
            <div class="listing-item-content">
                <span class="tag" id="tag">{{ item.district }}</span>
                <h3>{{ item.name }}</h3>
                <span>{{ item.address }}</span>
            </div>
        </div>
        <!-- Giá -->
        <div class="star-rating">
            <div class="rating-counter">Giá từ {{ item.minPrice }}/tháng</div>
        </div>
    </NuxtLink>
</template>

<script setup>
const config = useRuntimeConfig(); // Lấy cấu hình runtime
defineProps(['item']); // Nhận dữ liệu nhà trọ từ props
</script>

<!-- CSS tùy chỉnh cho component -->
<style>
/* CSS cho văn bản nội dung */
.listing-item-content span {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis; /* Cắt ngắn văn bản dài */
    max-width: 100%;
}

/* CSS cho danh sách chi tiết */
.listing-item-details ul {
    background-color: rgba(255, 255, 255, 0.8); /* Nền sáng với độ trong suốt */
    padding: 5px 10px !important;
    border-radius: 5px;
    border: 1px solid #ddd; /* Viền nhẹ */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Hiệu ứng bóng */
    margin: 0;
    padding: 0;
    list-style: none;
}

/* CSS cho mục trong danh sách chi tiết */
.listing-item-details li {
    font-size: 14px;
    font-weight: bold; /* Chữ đậm */
    color: #333;
    margin-bottom: 3px;
}

/* Khoảng cách giữa các mục */
.listing-item-details li + li {
    margin-left: 3px;
}

/* Thêm biểu tượng trước mỗi mục */
.listing-item-details li:before {
    content: '🏠 '; /* Biểu tượng ngôi nhà */
    color: #28a745; /* Màu xanh lá */
}

/* CSS cho hình ảnh nhà trọ */
.listing-item img {
    filter: blur(3px); /* Độ mờ ban đầu */
    transition: filter 0.3s ease; /* Hiệu ứng chuyển đổi */
}

/* Hiệu ứng hover cho hình ảnh */
.listing-item:hover img {
    filter: blur(5px); /* Tăng độ mờ khi hover */
}
</style>
