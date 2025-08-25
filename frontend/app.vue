<template>
    <!-- Sử dụng NuxtLayout để áp dụng layout mặc định cho toàn bộ ứng dụng -->
    <NuxtLayout>
        <!-- Hiển thị nội dung của trang hiện tại thông qua NuxtPage -->
        <NuxtPage />
    </NuxtLayout>
</template>

<script setup>
import { useHead } from '#app'; // Nhập composable useHead để cấu hình metadata cho SEO

// Cấu hình SEO mặc định cho toàn bộ ứng dụng SGHood
useHead({
    title: 'SGHood - Website Quản Lý Thuê Trọ Thông Minh', // Tiêu đề trang mặc định
    meta: [
        { charset: 'utf-8' }, // Thiết lập bảng mã ký tự UTF-8
        { name: 'viewport', content: 'width=device-width, initial-scale=1' }, // Cấu hình responsive cho viewport
        {
            hid: 'description', // ID duy nhất cho thẻ meta description
            name: 'description',
            content:
                'SGHood - Hệ thống quản lý thuê trọ thông minh tại TP. Hồ Chí Minh, hỗ trợ quản lý phòng trọ, hợp đồng, hóa đơn, thanh toán online và đặt phòng trực tuyến một cách minh bạch, tiện lợi.' // Mô tả trang cho SEO
        },
        {
            name: 'keywords',
            content:
                'SGHood, quản lý nhà trọ, thuê trọ TP. Hồ Chí Minh, đặt phòng trực tuyến, thanh toán online, quản lý hợp đồng, kiểm kê tài sản, trả phòng' // Từ khóa SEO
        },
        { name: 'author', content: 'SGHood Team' }, // Tác giả của trang
        // Open Graph (cho mạng xã hội)
        {
            property: 'og:title',
            content: 'SGHood - Website Quản Lý Thuê Trọ Thông Minh' // Tiêu đề Open Graph
        },
        {
            property: 'og:description',
            content:
                'SGHood cung cấp giải pháp quản lý nhà trọ hiện đại, minh bạch và tiện lợi tại TP. Hồ Chí Minh. Đặt phòng, ký hợp đồng, thanh toán hóa đơn và nhận thông báo trực tuyến.' // Mô tả Open Graph
        },
        { property: 'og:type', content: 'website' }, // Loại nội dung Open Graph
        { property: 'og:url', content: 'https://sghood.com.vn' } // URL chính thức của trang
    ]
});
</script>
