<!-- components/ListingShare.vue -->
<template>
    <div class="listing-share margin-top-40 margin-bottom-40 no-border">
        <button class="like-button" @click="$emit('toggle-like')"><span class="like-icon"></span> Yêu thích</button>
        <span>{{ likeCount }} người đã yêu thích nhà trọ này</span>
        <div class="clearfix"></div>
    </div>
</template>

<script setup>
defineProps({
    likeCount: Number
});
defineEmits(['toggle-like']);
</script>
