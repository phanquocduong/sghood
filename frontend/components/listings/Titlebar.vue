<!-- Titlebar.vue -->
<template>
    <div id="titlebar" class="gradient">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h2>{{ title }}</h2>
                    <span>{{ resultCount }}</span>
                    <nav id="breadcrumbs">
                        <ul>
                            <li v-for="item in breadcrumbs" :key="item.text">
                                <NuxtLink v-if="item.to" :to="item.to">{{ item.text }}</NuxtLink>
                                <span v-else>{{ item.text }}</span>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps(['title', 'resultCount', 'breadcrumbs']);
</script>
