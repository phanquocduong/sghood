<template>
    <div id="listing-pricing-list" class="listing-section">
        <div class="pricing-list-container">
            <h4>{{ title }}</h4>
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
