<!-- Template cho component danh sách phí -->
<template>
    <div id="listing-pricing-list" class="listing-section margin-top-35">
        <div class="pricing-list-container">
            <h4>{{ title }}</h4>
            <!-- Danh sách phí -->
            <ul>
                <li v-for="item in fees" :key="item.name">
                    <h5>{{ item.name }}</h5>
                    <span>{{ item.price }}</span>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String
    },
    fees: {
        type: Array,
        default: () => []
    }
});
</script>
