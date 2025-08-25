<!-- Template cho component hiển thị một phòng -->
<template>
    <div class="listing-item-container" @click="$emit('open-modal', item)">
        <div class="listing-item">
            <!-- Hình ảnh chính của phòng -->
            <img :src="`${config.public.baseUrl}${item.main_image}`" :alt="item.name" />
            <!-- Danh sách tiện ích -->
            <div class="listing-item-details">
                <ul>
                    <li v-for="amenity in item.amenities" :key="amenity">{{ amenity }}</li>
                </ul>
            </div>
            <!-- Thông tin cơ bản -->
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
        <!-- Diện tích phòng -->
        <div class="star-rating">
            <div class="rating-counter">Diện tích: {{ item.area }}m<sup>2</sup></div>
        </div>
    </div>
</template>

<script setup>
const config = useRuntimeConfig(); // Lấy cấu hình runtime
defineProps(['item']); // Nhận dữ liệu phòng từ props
defineEmits(['open-modal']); // Phát sự kiện mở modal
</script>

<!-- CSS tùy chỉnh cho component -->
<style scoped>
/* CSS cho container phòng */
.listing-item-container {
    cursor: pointer;
    transition: transform 0.2s; /* Hiệu ứng phóng to khi hover */
}

/* Hiệu ứng hover cho container */
.listing-item-container:hover {
    transform: scale(1.02);
}

/* Thêm biểu tượng trước tiện ích */
.listing-item-details li:before {
    content: '✔ '; /* Dấu tích */
    color: #28a745; /* Màu xanh lá */
}

/* CSS cho nhãn trạng thái */
.tag {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 4px;
    color: #fff;
    font-weight: bold;
}

/* Màu cho trạng thái Trống */
.status-available {
    background-color: #4caf50 !important;
}

/* Màu cho trạng thái Đã thuê */
.status-rented {
    background-color: #ee3535 !important;
}

/* Màu cho trạng thái Sửa chữa */
.status-maintenance {
    background-color: #ffc107 !important;
}
</style>
