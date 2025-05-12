<!-- components/ListingMap.vue -->
<template>
    <div id="listing-location" class="listing-section">
        <h3 class="listing-desc-headline margin-top-60 margin-bottom-30">Vị trí</h3>
        <iframe
            :src="mapUrl"
            width="100%"
            height="400"
            style="border: 0"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
        ></iframe>
    </div>
</template>

<script setup>
defineProps({
    mapUrl: String
});
</script>
