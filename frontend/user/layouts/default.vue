<template>
    <div id="wrapper">
        <!-- Header -->
        <header id="header-container">
            <div id="header">
                <div class="container">
                    <!-- Left Side Content -->
                    <div class="left-side">
                        <!-- Logo -->
                        <div id="logo">
                            <a href="index.html"><img src="public/images/logo.png" alt="" /></a>
                        </div>

                        <!-- Mobile Navigation -->
                        <div class="mmenu-trigger">
                            <button class="hamburger hamburger--collapse" type="button">
                                <span class="hamburger-box">
                                    <span class="hamburger-inner"></span>
                                </span>
                            </button>
                        </div>

                        <!-- Main Navigation -->
                        <nav id="navigation" class="style-1">
                            <ul id="responsive">
                                <li>
                                    <NuxtLink to="/" active-class="current">Trang Chủ</NuxtLink>
                                </li>
                                <li>
                                    <NuxtLink to="/nha-tro" active-class="current">Nhà Trọ</NuxtLink>
                                </li>
                                <li>
                                    <NuxtLink to="/ve-chung-toi" active-class="current">Về Chúng Tôi</NuxtLink>
                                </li>
                                <li>
                                    <NuxtLink to="/lien-he" active-class="current">Liên Hệ</NuxtLink>
                                </li>
                            </ul>
                        </nav>
                    </div>

                    <!-- Right Side Content -->
                    <div class="right-side">
                        <div class="header-widget">
                            <!-- Show login link if user is not logged in -->
                            <a v-show="!user" href="#sign-in-dialog" class="sign-in popup-with-zoom-anim">
                                <i class="sl sl-icon-login"></i> Đăng ký/Đăng nhập
                            </a>
                            <!-- User Menu if user is logged in -->
                            <div v-show="user" class="user-menu">
                                <div class="user-name">
                                    <span><img src="/images/dashboard-avatar.jpg" alt="" /></span>
                                    Hi, {{ userName || 'User' }}!
                                </div>
                                <ul>
                                    <li>
                                        <a href="/dashboard-messages"><i class="fa fa-bell-o"></i> Thông báo</a>
                                    </li>
                                    <li>
                                        <a href="/dashboard"><i class="sl sl-icon-user"></i> Hồ sơ cá nhân</a>
                                    </li>
                                    <li>
                                        <a href="/dashboard-bookings"><i class="fa fa-calendar-check-o"></i> Đặt phòng</a>
                                    </li>
                                    <li>
                                        <a href="#" @click.prevent="logout"><i class="sl sl-icon-power"></i> Đăng xuất</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <AuthForm />
                </div>
            </div>
        </header>

        <!-- Main -->
        <main>
            <NuxtPage />
        </main>

        <!-- Footer -->
        <div id="footer" class="margin-top-55">
            <div class="container">
                <div class="row">
                    <div class="col-md-5 col-sm-6">
                        <img class="footer-logo" src="public/images/logo.png" alt="" />
                        <br /><br />
                        <p>
                            Morbi convallis bibendum urna ut viverra. Maecenas quis consequat libero, a feugiat eros. Nunc ut lacinia tortor
                            morbi ultricies laoreet ullamcorper phasellus semper.
                        </p>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <h4>Liên kết nhanh</h4>
                        <ul class="footer-links">
                            <li><a href="#">Đăng nhập</a></li>
                            <li><a href="#">Tài khoản của tôi</a></li>
                            <li><a href="#">Chính sách bảo mật</a></li>
                        </ul>

                        <ul class="footer-links">
                            <li><a href="#">Tin tức</a></li>
                            <li><a href="#">Về chúng tôi</a></li>
                            <li><a href="#">Liên hệ</a></li>
                        </ul>
                        <div class="clearfix"></div>
                    </div>

                    <div class="col-md-3 col-sm-12">
                        <h4>Liên hệ với chúng tôi</h4>
                        <div class="text-widget">
                            <span>12345 Little Lonsdale St, Melbourne</span> <br />
                            Phone: <span>(123) 123-456 </span><br />
                            E-Mail:<span> <a href="#">office@example.com</a> </span><br />
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
                        <div class="copyrights">© 2021 Trọ Việt. All Rights Reserved.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back To Top Button -->
        <div id="backtotop"><a href="#"></a></div>
    </div>
</template>

<script setup></script>

<style scoped></style>
