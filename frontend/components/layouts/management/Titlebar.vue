<template>
    <!-- Tiêu đề và breadcrumb của trang -->
    <div id="titlebar">
        <div class="row">
            <div class="col-md-12">
                <!-- Tiêu đề chính -->
                <h2>{{ title }}</h2>
                <!-- Breadcrumb điều hướng -->
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
        required: true // Tiêu đề là prop bắt buộc
    }
});
</script>
