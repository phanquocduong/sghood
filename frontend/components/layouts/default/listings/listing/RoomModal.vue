<template>
    <div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
        <div class="modal-content">
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
                    <button v-if="room.images.length > 1" class="prev" @click="prevImage">❮</button>
                    <button v-if="room.images.length > 1" class="next" @click="nextImage">❯</button>
                </div>
            </div>

            <!-- Thông tin phòng -->
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
    backdrop-filter: blur(3px); /* Hiệu ứng mờ nền */
}

.modal-content {
    background: #ffffff;
    padding: 24px;
    border-radius: 12px;
    max-width: 700px;
    width: 90%;
    max-height: 85vh;
    overflow-y: auto;
    position: relative;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.close-button {
    position: absolute;
    top: 16px;
    right: 16px;
    background: #f5f5f5;
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    font-size: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}

.close-button:hover {
    background: #e0e0e0;
}

.modal-content h1 {
    font-size: 30px;
    font-weight: 600;
    color: #333;
    margin: 0 0 22px;
    text-align: center;
}

.image-slider {
    position: relative;
    width: 100%;
    height: 350px;
    overflow: hidden;
    margin: 16px 0;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
    object-fit: cover; /* Đảm bảo ảnh đầy khung */
    opacity: 0;
    transition: opacity 0.5s ease-in-out;
    border-radius: 8px;
}

.slider-container img.active {
    opacity: 1;
}

.prev,
.next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.6);
    color: white;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    font-size: 18px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}

.prev:hover,
.next:hover {
    background: rgba(0, 0, 0, 0.8);
}

.prev {
    left: 12px;
}

.next {
    right: 12px;
}

.room-details {
    padding: 16px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-top: 16px;
}

.room-details p {
    margin: 12px 0;
    font-size: 16px;
    color: #444;
    display: flex;
    align-items: center;
    gap: 8px;
}

.room-details .label {
    font-weight: 600;
    color: #222;
    min-width: 100px;
}

.status-available {
    color: #28a745;
    font-weight: 500;
}

.status-rented {
    color: #dc3545;
    font-weight: 500;
}

.status-repaired {
    color: #ffc107;
    font-weight: 500;
}

.status-default {
    color: #6c757d;
    font-weight: 500;
}

.amenities-list {
    list-style: none;
    margin: 12px 0 0 20px;
    padding: 0;
}

.amenities-list li {
    font-size: 16px;
    color: #444;
    margin-bottom: 8px;
    position: relative;
    padding-left: 20px;
}

.amenities-list li::before {
    content: '✔';
    position: absolute;
    left: 0;
    color: #28a745;
    font-size: 14px;
}

/* Responsive */
@media (max-width: 600px) {
    .modal-content {
        width: 95%;
        padding: 16px;
    }

    .image-slider {
        height: 250px;
    }

    .modal-content h2 {
        font-size: 20px;
    }

    .room-details p,
    .amenities-list li {
        font-size: 14px;
    }

    .prev,
    .next {
        width: 32px;
        height: 32px;
        font-size: 16px;
    }
}
</style>
