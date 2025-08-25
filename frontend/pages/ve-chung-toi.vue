<!-- Template cho trang giới thiệu về chúng tôi -->
<template>
    <div class="page-wrapper">
        <!-- Phần giới thiệu "Về chúng tôi" -->
        <section class="section bg-gray">
            <div class="section-inner">
                <!-- Tiêu đề chính của phần -->
                <h2 class="section-title title-large">VỀ CHÚNG TÔI</h2>
                <div class="flex-row">
                    <!-- Khối văn bản mô tả -->
                    <div class="text-block">
                        <h3 class="sub-title">CHÚNG TÔI LÀ AI</h3>
                        <div class="text-box">
                            <p>
                                SGHood là nền tảng quản lý thuê trọ thông minh, được phát triển nhằm kết nối người thuê và chủ trọ tại TP.
                                Hồ Chí Minh. Chúng tôi mang đến giải pháp toàn diện, giúp tiết kiệm thời gian, chi phí và công sức trong
                                việc tìm kiếm và quản lý phòng trọ.
                            </p>
                        </div>
                    </div>
                    <!-- Hình ảnh minh họa -->
                    <img src="/images/Questions-pana.png" alt="About" class="image" />
                </div>
            </div>
        </section>

        <!-- Phần sứ mệnh và tầm nhìn -->
        <section class="section bg-white">
            <div class="section-inner">
                <!-- Tiêu đề chính của phần -->
                <h2 class="section-title">SỨ MỆNH & TẦM NHÌN</h2>
                <div class="text-box">
                    <p>
                        SGHood hướng tới việc số hóa thị trường cho thuê trọ, giúp người dùng dễ dàng tìm được chỗ ở phù hợp, minh bạch và
                        an toàn. Đồng thời, chúng tôi hỗ trợ chủ trọ quản lý thông tin phòng, người thuê, thanh toán và vận hành trơn tru
                        qua nền tảng trực tuyến.
                    </p>
                    <p>
                        Trong tương lai, SGHood đặt mục tiêu trở thành hệ sinh thái cho thuê nhà trọ lớn nhất tại Việt Nam, là cầu nối uy
                        tín giữa hàng triệu người thuê trọ và các đơn vị quản lý nhà trọ chuyên nghiệp.
                    </p>
                </div>
            </div>
        </section>

        <!-- Phần "Chúng tôi làm gì" -->
        <section class="section bg-gray">
            <div class="section-inner">
                <!-- Tiêu đề chính của phần -->
                <h2 class="section-title">CHÚNG TÔI LÀM GÌ</h2>
                <div class="flex-row">
                    <!-- Khối văn bản liệt kê dịch vụ -->
                    <div class="text-block">
                        <div class="text-box">
                            <p>✔ Tìm kiếm & lọc phòng theo khu vực, giá cả, tiện ích và đánh giá.</p>
                            <p>✔ Đặt lịch xem phòng & thanh toán trực tuyến một cách nhanh chóng, an toàn.</p>
                            <p>✔ Kết nối trực tiếp với chủ trọ thông qua tin nhắn hoặc cuộc gọi.</p>
                            <p>✔ Cung cấp thông tin rõ ràng, hình ảnh thực tế, hợp đồng minh bạch.</p>
                        </div>
                    </div>
                    <!-- Hình ảnh minh họa -->
                    <img src="/images/Man thinking-pana.png" alt="About" class="image" />
                </div>
            </div>
        </section>

        <!-- Phần đội ngũ phát triển -->
        <section class="section bg-white">
            <div class="section-inner">
                <!-- Tiêu đề chính của phần -->
                <h2 class="section-title">ĐỘI NGŨ PHÁT TRIỂN</h2>
                <div class="text-box">
                    <p>
                        SGHood được xây dựng bởi những người từng là sinh viên và người đi thuê trọ. Chúng tôi thấu hiểu nỗi lo, sự phiền
                        phức và bất tiện trong việc tìm chỗ ở phù hợp. Với niềm tin vào công nghệ, đội ngũ phát triển mong muốn thay đổi thị
                        trường thuê trọ theo hướng hiện đại, minh bạch và thuận tiện cho tất cả mọi người.
                    </p>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { useHead } from '#app';

// Cấu hình SEO cho trang "Về chúng tôi"
useHead({
    title: 'SGHood - Về Chúng Tôi', // Tiêu đề trang
    meta: [
        { charset: 'utf-8' }, // Thiết lập mã hóa ký tự
        { name: 'viewport', content: 'width=device-width, initial-scale=1' }, // Responsive viewport
        {
            hid: 'description',
            name: 'description',
            content:
                'Tìm hiểu về SGHood - nền tảng quản lý thuê trọ thông minh tại TP. Hồ Chí Minh, sứ mệnh, tầm nhìn và đội ngũ phát triển.' // Mô tả SEO
        },
        {
            name: 'keywords',
            content: 'SGHood, về chúng tôi, quản lý nhà trọ, thuê trọ TP. Hồ Chí Minh, nền tảng thuê trọ, đội ngũ phát triển' // Từ khóa SEO
        },
        { name: 'author', content: 'SGHood Team' }, // Tác giả
        // Open Graph metadata
        {
            property: 'og:title',
            content: 'SGHood - Về Chúng Tôi' // Tiêu đề Open Graph
        },
        {
            property: 'og:description',
            content:
                'Tìm hiểu về SGHood - nền tảng quản lý thuê trọ thông minh tại TP. Hồ Chí Minh, sứ mệnh, tầm nhìn và đội ngũ phát triển.' // Mô tả Open Graph
        },
        { property: 'og:type', content: 'website' }, // Loại nội dung Open Graph
        { property: 'og:url', content: 'https://sghood.com.vn/ve-chung-toi' } // URL Open Graph
    ]
});
</script>

<!-- CSS tùy chỉnh cho trang -->
<style scoped>
@import '~/public/css/about.css';
</style>
