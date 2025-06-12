<template>
    <div>
        <h3 class="listing-desc-headline">Tiện ích</h3>
        <ul class="listing-features checkboxes margin-top-0">
            <li v-for="amenity in amenities" :key="amenity">{{ amenity }}</li>
        </ul>
    </div>
</template>

<script setup>
defineProps({
    amenities: {
        type: Array,
        default: () => []
    }
});
</script>
