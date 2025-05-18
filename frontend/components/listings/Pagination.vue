<!-- Pagination.vue -->
<template>
    <div class="pagination-container margin-top-20 margin-bottom-40">
        <nav class="pagination">
            <ul>
                <li>
                    <a @click.prevent="$emit('change:page', currentPage > 1 ? currentPage - 1 : currentPage)">
                        <i class="sl sl-icon-arrow-left"></i>
                    </a>
                </li>
                <li v-for="page in totalPages" :key="page">
                    <a :class="{ 'current-page': page === currentPage }" @click.prevent="$emit('change:page', page)">
                        {{ page }}
                    </a>
                </li>
                <li>
                    <a @click.prevent="$emit('change:page', currentPage + 1)">
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
