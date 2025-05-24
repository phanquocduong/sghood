<template>
    <div id="wrapper">
        <!-- Header Container -->
        <header id="header-container" class="fixed fullwidth dashboard">
            <div id="header" class="not-sticky">
                <div class="container">
                    <!-- Left Side Content -->
                    <div class="left-side">
                        <!-- Logo -->
                        <div id="logo">
                            <NuxtLink to="/" class="dashboard-logo"><img src="/images/troviet_logo2.png" alt="" /></NuxtLink>
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
                                    <NuxtLink to="/danh-sach-nha-tro" active-class="current">Danh Sách Nhà Trọ</NuxtLink>
                                </li>
                                <li>
                                    <NuxtLink to="/lien-he" active-class="current">Liên Hệ</NuxtLink>
                                </li>
                            </ul>
                        </nav>
                    </div>

                    <!-- Right Side Content -->
                    <HeaderRightSide />
                </div>
            </div>
        </header>
        <!-- Dashboard -->
        <div id="dashboard">
            <a href="#" class="dashboard-responsive-nav-trigger"><i class="fa fa-reorder"></i></a>
            <DashboardNav />
            <Loading />
            <!-- Content -->
            <div class="dashboard-content">
                <NuxtPage />
                <!-- Copyrights -->
                <div class="col-md-12">
                    <div class="copyrights">© 2025 Trọ Việt.</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup></script>

<style scoped>
#logo {
    text-align: center;
}

#logo a {
    margin: 0;
}

#logo img {
    max-height: 55px;
}
</style>
