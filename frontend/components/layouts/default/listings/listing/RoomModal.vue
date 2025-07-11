<template>
    <div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
        <div class="modal-content">
            <button class="close-button" @click="closeModal">×</button>
            <h2>{{ room.name }}</h2>

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
                    <button v-if="room.images.length > 1" class="prev" @click="prevImage">&#10094;</button>
                    <button v-if="room.images.length > 1" class="next" @click="nextImage">&#10095;</button>
                </div>
            </div>

            <!-- Thông tin phòng -->
            <div class="room-details">
                <p><strong>Giá:</strong> {{ room.price }}/tháng</p>
                <p><strong>Diện tích:</strong> {{ room.area }}m²</p>
                <p><strong>Trạng thái:</strong> {{ room.status }}</p>
                <p><strong>Mô tả:</strong> {{ room.description || 'Không có mô tả' }}</p>
                <p v-if="room.amenities.length != 0"><strong>Tiện nghi:</strong></p>
                <ul v-if="room.amenities.length != 0">
                    <li v-for="amenity in room.amenities" :key="amenity">{{ amenity }}</li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
const config = useRuntimeConfig();

const props = defineProps({
    isOpen: Boolean,
    room: Object
});
const emit = defineEmits(['close']);

const currentImageIndex = ref(0);

const closeModal = () => {
    emit('close');
    currentImageIndex.value = 0; // Reset slider
};

const prevImage = () => {
    if (currentImageIndex.value > 0) {
        currentImageIndex.value--;
    } else {
        currentImageIndex.value = props.room.images.length - 1;
    }
};

const nextImage = () => {
    if (currentImageIndex.value < props.room.images.length - 1) {
        currentImageIndex.value++;
    } else {
        currentImageIndex.value = 0;
    }
};
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    position: relative;
}

.close-button {
    position: absolute;
    top: 10px;
    right: 10px;
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
}

.image-slider {
    position: relative;
    width: 100%;
    height: 300px;
    overflow: hidden;
    margin: 20px 0;
}

.slider-container {
    position: relative;
    width: 100%;
    height: 100%;
}

.slider-container img {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: contain;
    opacity: 0;
    transition: opacity 0.5s;
}

.slider-container img.active {
    opacity: 1;
}

.prev,
.next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    padding: 10px;
    cursor: pointer;
}

.prev {
    left: 10px;
}

.next {
    right: 10px;
}

.room-details {
    padding: 10px;
}

.room-details p {
    margin: 10px 0;
}

.room-details ul {
    list-style: disc;
    margin-left: 20px;
}
</style>
