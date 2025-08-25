<!-- Template cho component thư viện ảnh -->
<template>
    <div id="listing-gallery" class="listing-section">
        <h3 class="listing-desc-headline">Ảnh</h3>
        <!-- Slider ảnh với hỗ trợ phóng to (Magnific Popup) -->
        <div class="listing-slider-small mfp-gallery-container margin-bottom-0">
            <a
                v-for="image in images"
                :key="image.src"
                :href="`${config.public.baseUrl}${image.src}`"
                :data-background-image="`${config.public.baseUrl}${image.src}`"
                class="item mfp-gallery"
            ></a>
        </div>
    </div>
</template>

<script setup>
import { onMounted, watch } from 'vue';
import { nextTick } from 'vue';

const config = useRuntimeConfig(); // Lấy cấu hình runtime
const props = defineProps({
    images: {
        type: Array,
        default: () => []
    }
});

// Theo dõi thay đổi danh sách ảnh để khởi tạo lại slider
watch(
    () => props.images,
    newImages => {
        if (newImages && newImages.length > 0) {
            nextTick(() => {
                const event = new Event('initListingSlider'); // Kích hoạt sự kiện khởi tạo slider
                window.dispatchEvent(event);
            });
        }
    }
);

// Khởi tạo slider khi component được mount
onMounted(() => {
    if (props.images && props.images.length > 0) {
        nextTick(() => {
            const event = new Event('initListingSlider'); // Kích hoạt sự kiện khởi tạo slider
            window.dispatchEvent(event);
        });
    }
});
</script>
