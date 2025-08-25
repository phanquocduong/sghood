<!-- Template cho component modal chi tiết phòng -->
<template>
    <div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
        <div class="modal-content">
            <!-- Nút đóng modal -->
            <button class="close-button" @click="closeModal">×</button>
            <h1>{{ room.name }}</h1>

            <!-- Slider ảnh -->
            <div class="image-slider">
                <div class="slider-container">
                    <img
                        v-for="(image, index) in room.images"
                        :key="index"
                        :src="`${config.public.baseUrl}${image.src}`"
                        :class="{ active: currentImageIndex === index }"
                        alt="Room image"
                    />
                    <!-- Nút điều hướng ảnh -->
                    <button v-if="room.images.length > 1" class="prev" @click="prevImage">❮</button>
                    <button v-if="room.images.length > 1" class="next" @click="nextImage">❯</button>
                </div>
            </div>

            <!-- Thông tin chi tiết phòng -->
            <div class="room-details">
                <p><strong class="label">Giá:</strong> {{ room.price }}/tháng</p>
                <p><strong class="label">Diện tích:</strong> {{ room.area }}m²</p>
                <p>
                    <strong class="label">Trạng thái:</strong> <span :class="getStatusClass(room.status)">{{ room.status }}</span>
                </p>
                <p><strong class="label">Mô tả:</strong> {{ room.description || 'Không có mô tả' }}</p>
                <p v-if="room.amenities.length != 0"><strong class="label">Tiện nghi:</strong></p>
                <ul v-if="room.amenities.length != 0" class="amenities-list">
                    <li v-for="amenity in room.amenities" :key="amenity">{{ amenity }}</li>
                </ul>
                <p><strong class="label">Số người ở tối đa:</strong> {{ room.max_occupants }}</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
const config = useRuntimeConfig(); // Lấy cấu hình runtime

const props = defineProps({
    isOpen: Boolean, // Trạng thái mở modal
    room: Object // Dữ liệu phòng
});
const emit = defineEmits(['close']); // Phát sự kiện đóng modal

const currentImageIndex = ref(0); // Chỉ số ảnh hiện tại trong slider

// Hàm đóng modal
const closeModal = () => {
    emit('close'); // Phát sự kiện đóng
    currentImageIndex.value = 0; // Reset slider
};

// Hàm chuyển đến ảnh trước
const prevImage = () => {
    if (currentImageIndex.value > 0) {
        currentImageIndex.value--;
    } else {
        currentImageIndex.value = props.room.images.length - 1; // Quay lại ảnh cuối
    }
};

// Hàm chuyển đến ảnh tiếp theo
const nextImage = () => {
    if (currentImageIndex.value < props.room.images.length - 1) {
        currentImageIndex.value++;
    } else {
        currentImageIndex.value = 0; // Quay lại ảnh đầu
    }
};

// Hàm lấy lớp CSS cho trạng thái
const getStatusClass = status => {
    switch (status) {
        case 'Trống':
            return 'status-available';
        case 'Đã thuê':
            return 'status-rented';
        case 'Sửa chữa':
            return 'status-repaired';
        default:
            return 'status-default';
    }
};
</script>

<!-- CSS tùy chỉnh cho component -->
<style scoped>
@import '~/public/css/room-modal.css';
</style>
