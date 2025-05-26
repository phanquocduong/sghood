<!-- components/ListingTitlebar.vue -->
<template>
    <div id="titlebar" class="listing-titlebar">
        <div class="listing-titlebar-title">
            <h2>
                {{ title }} <span class="listing-tag">{{ tag }}</span>
            </h2>
            <span>
                <a href="#listing-location" class="listing-address"> <i class="fa fa-map-marker"></i>{{ address }} </a>
            </span>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: String,
    address: String,
    tag: String
});
</script>
