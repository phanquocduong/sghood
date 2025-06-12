<template>
    <!-- Footer -->
    <div id="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-5 col-sm-6">
                <img
                    v-if="config?.logo_ngang"
                    :src="baseUrl + '/' + config.logo_ngang"
                    alt="Logo"
                   class="logo-footer"
                />
                    <br /><br />
                    <p v-if="config?.mo_ta_footer">
                     {{config.mo_ta_footer}}
                    </p>
                </div>

                <div class="col-md-4 col-sm-6">
                    <h4>Liên kết nhanh</h4>
                    <ul class="footer-links">
                        <li><a href="#">Trang chủ</a></li>
                        <li><a href="#">Nhà trọ</a></li>
                    </ul>

                    <ul class="footer-links">
                        <li><a href="#">Chính sách bảo mật</a></li>
                        <li><a href="#">Liên hệ</a></li>
                    </ul>
                </div>

                <div class="col-md-3 col-sm-12">
                    <h4>Liên hệ với chúng tôi</h4>
                    <div class="text-widget">
                        <span>QTSC 9 Building, Đ. Tô Ký, Tân Chánh Hiệp, Quận 12, Hồ Chí Minh</span> <br />
                      <span v-if="config?.sdt">Điện thoại: <span>{{ config.sdt }}</span></span><br />
                     <span v-if="config?.email">Email: <a :href="'mailto:' + config.email">{{ config.email }}</a></span><br />
                    </div>

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

            <div class="row">
                <div class="col-md-12">
                    <div class="copyrights">© 2025 Trọ Việt.</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const config = useState('configs')
const baseUrl = useRuntimeConfig().public.baseUrl
console.log('🧪 Footer config:', config.value)
</script>

<style scoped>
.logo-footer {
    max-width: 150px; /* Đảm bảo logo không quá lớn */
    height: auto; /* Giữ tỷ lệ khung hình */
    object-fit: contain;
    margin-bottom: -5px;
}
</style>
