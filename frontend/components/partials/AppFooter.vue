<!-- Template cho footer của ứng dụng -->
<template>
    <!-- Phần footer chính -->
    <div id="footer">
        <div class="container">
            <!-- Hàng chứa các cột nội dung footer -->
            <div class="row">
                <!-- Cột 1: Logo và mô tả ngắn -->
                <div class="col-md-5 col-sm-6">
                    <!-- Hiển thị logo phụ nếu có trong cấu hình -->
                    <img v-if="config?.secondary_logo" :src="baseUrl + config.secondary_logo" alt="Logo" class="logo-footer" />
                    <br /><br />
                    <!-- Hiển thị mô tả ngắn nếu có trong cấu hình -->
                    <p v-if="config?.sghood_short_desc">
                        {{ config.sghood_short_desc }}
                    </p>
                </div>

                <!-- Cột 2: Liên kết nhanh -->
                <div class="col-md-4 col-sm-6">
                    <h4>Liên kết nhanh</h4>
                    <!-- Danh sách liên kết nhanh (bên trái) -->
                    <ul class="footer-links">
                        <li><NuxtLink to="/">Trang chủ</NuxtLink></li>
                        <li><NuxtLink to="/danh-sach-nha-tro">Nhà trọ</NuxtLink></li>
                        <li><NuxtLink to="/chia-se-kinh-nghiem">Kinh nghiệm</NuxtLink></li>
                    </ul>

                    <!-- Danh sách liên kết nhanh (bên phải) -->
                    <ul class="footer-links">
                        <li><NuxtLink to="/dieu-khoan-hop-dong">Điều khoản hợp đồng</NuxtLink></li>
                        <li><NuxtLink to="/ve-chung-toi">Về chúng tôi</NuxtLink></li>
                        <li><NuxtLink to="/lien-he">Liên hệ</NuxtLink></li>
                    </ul>
                    <div class="clearfix"></div>
                    <!-- Xóa float để đảm bảo bố cục -->
                </div>

                <!-- Cột 3: Thông tin liên hệ -->
                <div class="col-md-3 col-sm-12">
                    <h4>Liên hệ với chúng tôi</h4>
                    <div class="text-widget">
                        <!-- Hiển thị địa chỉ văn phòng -->
                        <span>{{ config.office_address }}</span> <br />
                        <!-- Hiển thị số điện thoại liên hệ nếu có -->
                        <span v-if="config?.contact_phone"
                            >Điện thoại: <span>{{ config.contact_phone }}</span></span
                        ><br />
                        <!-- Hiển thị email liên hệ nếu có -->
                        <span v-if="config?.contact_email"
                            >Email: <a :href="'mailto:' + config.contact_email">{{ config.contact_email }}</a></span
                        ><br />
                    </div>

                    <!-- Biểu tượng mạng xã hội -->
                    <ul class="social-icons margin-top-20">
                        <li>
                            <a class="facebook" href="#"><i class="icon-facebook"></i></a>
                        </li>
                        <li>
                            <a class="twitter" href="#"><i class="icon-twitter"></i></a>
                        </li>
                        <li>
                            <a class="gplus" href="#"><i class="icon-gplus"></i></a>
                        </li>
                        <li>
                            <a class="vimeo" href="#"><i class="icon-vimeo"></i></a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Phần bản quyền -->
            <div class="row">
                <div class="col-md-12">
                    <div class="copyrights">{{ config.copyright_title }}</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
// Lấy cấu hình từ state toàn cục
const config = useState('configs');
// Lấy base URL từ runtime config
const baseUrl = useRuntimeConfig().public.baseUrl;
</script>

<!-- CSS tùy chỉnh cho component -->
<style scoped>
/* CSS cho logo trong footer */
.logo-footer {
    max-width: 150px; /* Đảm bảo logo không quá lớn */
    height: auto; /* Giữ tỷ lệ khung hình */
    object-fit: contain; /* Đảm bảo logo hiển thị đầy đủ */
    margin-bottom: -5px; /* Điều chỉnh lề dưới */
}
</style>
