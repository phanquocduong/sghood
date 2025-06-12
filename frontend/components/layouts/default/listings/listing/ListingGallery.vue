<template>
    <div id="listing-gallery" class="listing-section">
        <h3 class="listing-desc-headline margin-top-70">Ảnh</h3>
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
            nextTick(() => {
                const event = new Event('initListingSlider');
                window.dispatchEvent(event);
            });
        }
    }
);

onMounted(() => {
    if (props.images && props.images.length > 0) {
        nextTick(() => {
            const event = new Event('initListingSlider');
            window.dispatchEvent(event);
        });
    }
});
</script>
