<template>
    <div class="pagination-container margin-top-20 margin-bottom-40">
        <nav v-if="totalPages" class="pagination">
            <ul>
                <li>
                    <a href="#" :class="{ disabled: currentPage === 1 }" @click.prevent="$emit('change:page', currentPage - 1)">
                        <i class="sl sl-icon-arrow-left"></i>
                    </a>
                </li>
                <li v-for="page in totalPages" :key="page">
                    <a href="#" :class="{ 'current-page': page === currentPage }" @click.prevent="$emit('change:page', page)">
                        {{ page }}
                    </a>
                </li>
                <li>
                    <a href="#" :class="{ disabled: currentPage === totalPages }" @click.prevent="$emit('change:page', currentPage + 1)">
                        <i class="sl sl-icon-arrow-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script setup>
defineProps(['currentPage', 'totalPages']);
defineEmits(['change:page']);
</script>

<style scoped>
.disabled {
    pointer-events: none;
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
