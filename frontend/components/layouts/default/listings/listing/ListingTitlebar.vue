<!-- Template cho component tiêu đề nhà trọ -->
<template>
    <div id="titlebar" class="listing-titlebar">
        <div class="listing-titlebar-title">
            <!-- Tiêu đề và khu vực -->
            <h2>
                {{ title }} <span class="listing-tag">{{ district }}</span>
            </h2>
            <!-- Địa chỉ -->
            <span>
                <a class="listing-address"> <i class="fa fa-map-marker"></i> {{ address }} </a>
            </span>
            <!-- Mô tả -->
            <span>
                <a class="listing-address"> <i class="fa fa-bookmark"></i> {{ description }} </a>
            </span>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: String, // Tiêu đề nhà trọ
    address: String, // Địa chỉ
    district: String, // Khu vực
    description: String // Mô tả
});
</script>
