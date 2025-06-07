<template>
    <div id="titlebar">
        <div class="row">
            <div class="col-md-12">
                <h2>{{ title }}</h2>
                <nav id="breadcrumbs">
                    <ul>
                        <li><NuxtLink to="/">Trang chủ</NuxtLink></li>
                        <li>Quản lý</li>
                        <li>{{ title }}</li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: {
        type: String,
        required: true
    }
});
</script>

<style scoped></style>
