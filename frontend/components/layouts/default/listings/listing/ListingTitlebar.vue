<template>
    <div id="titlebar" class="listing-titlebar">
        <div class="listing-titlebar-title">
            <h2>
                {{ title }} <span class="listing-tag">{{ tag }}</span>
            </h2>
            <span>
                <a class="listing-address"> <i class="fa fa-map-marker"></i>{{ location }} </a>
            </span>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: String,
    location: String,
    tag: String
});
</script>
