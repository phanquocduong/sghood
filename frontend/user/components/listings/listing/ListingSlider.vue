<!-- components/ListingSlider.vue -->
<template>
    <div class="listing-slider mfp-gallery-container margin-bottom-0">
        <a v-for="image in images" :key="image.src" :href="image.src" :data-background-image="image.src" class="item mfp-gallery"></a>
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
