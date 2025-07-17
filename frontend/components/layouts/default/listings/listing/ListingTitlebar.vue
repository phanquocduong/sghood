<template>
    <div id="titlebar" class="listing-titlebar">
        <div class="listing-titlebar-title">
            <h2>
                {{ title }} <span class="listing-tag">{{ district }}</span>
            </h2>
            <span>
                <a class="listing-address"> <i class="fa fa-map-marker"></i> {{ address }} </a>
            </span>
            <span>
                <a class="listing-address"> <i class="fa fa-bookmark"></i> {{ description }} </a>
            </span>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: String,
    address: String,
    district: String,
    description: String
});
</script>
