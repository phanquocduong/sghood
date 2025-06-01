<!-- components/ListingSlider.vue -->
<template>
    <div class="listing-slider mfp-gallery-container margin-bottom-0">
        <a
            v-for="image in images"
            :key="image.src"
            :href="`${config.public.baseUrl}${image.src}`"
            :data-background-image="`${config.public.baseUrl}${image.src}`"
            class="item mfp-gallery"
        ></a>
    </div>
</template>

<script setup>
import { onMounted, watch } from 'vue';

const config = useRuntimeConfig();
const props = defineProps({
    images: {
        type: Array,
        default: () => []
    }
});

// Đợi dữ liệu images được cập nhật và khởi tạo lại slider
watch(
    () => props.images,
    newImages => {
        if (newImages && newImages.length > 0) {
            // Đợi DOM cập nhật
            nextTick(() => {
                // Kích hoạt sự kiện tùy chỉnh để thông báo dữ liệu đã sẵn sàng
                const event = new Event('initListingSlider');
                window.dispatchEvent(event);
            });
        }
    }
);

onMounted(() => {
    // Kiểm tra nếu images đã có dữ liệu ngay khi mounted
    if (props.images && props.images.length > 0) {
        nextTick(() => {
            const event = new Event('initListingSlider');
            window.dispatchEvent(event);
        });
    }
});
</script>
