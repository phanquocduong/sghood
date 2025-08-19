<template>
    <NuxtLayout>
        <NuxtPage />
    </NuxtLayout>
</template>

<script setup>
import { useHead } from '#app';

// Cấu hình SEO mặc định cho toàn bộ ứng dụng SGHood
useHead({
    title: 'SGHood - Website Quản Lý Thuê Trọ Thông Minh',
    meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        {
            hid: 'description',
            name: 'description',
            content:
                'SGHood - Hệ thống quản lý thuê trọ thông minh tại TP. Hồ Chí Minh, hỗ trợ quản lý phòng trọ, hợp đồng, hóa đơn, thanh toán online và đặt phòng trực tuyến một cách minh bạch, tiện lợi.'
        },
        {
            name: 'keywords',
            content:
                'SGHood, quản lý nhà trọ, thuê trọ TP. Hồ Chí Minh, đặt phòng trực tuyến, thanh toán online, quản lý hợp đồng, kiểm kê tài sản, trả phòng'
        },
        { name: 'author', content: 'SGHood Team' },
        // Open Graph (cho mạng xã hội)
        {
            property: 'og:title',
            content: 'SGHood - Website Quản Lý Thuê Trọ Thông Minh'
        },
        {
            property: 'og:description',
            content:
                'SGHood cung cấp giải pháp quản lý nhà trọ hiện đại, minh bạch và tiện lợi tại TP. Hồ Chí Minh. Đặt phòng, ký hợp đồng, thanh toán hóa đơn và nhận thông báo trực tuyến.'
        },
        { property: 'og:type', content: 'website' },
        { property: 'og:url', content: 'https://sghood.com.vn' }
    ]
});
</script>

<style>
/* Các kiểu CSS toàn cục nếu cần */
</style>
