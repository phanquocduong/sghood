<template>
    <div id="listing-gallery" class="listing-section">
        <h3 class="listing-desc-headline margin-top-70">Ảnh</h3>
        <div class="listing-slider-small mfp-gallery-container margin-bottom-0">
            <a v-for="image in images" :key="image.src" :href="image.src" :data-background-image="image.src" class="item mfp-gallery"></a>
        </div>
    </div>
</template>

<script setup>
defineProps({
    images: {
        type: Array,
        default: () => []
    }
});
</script>
