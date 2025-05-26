<template>
    <div id="wrapper">
        <!-- Header -->
        <header id="header-container" class="no-shadow">
            <div id="header">
                <div class="container">
                    <!-- Left Side Content -->
                    <div class="left-side">
                        <!-- Logo -->
                        <div id="logo">
                            <NuxtLink to="/"><img src="/images/troviet_logo1.png" alt="" /></NuxtLink>
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

                    <HeaderRightSide />

                    <AuthForm />
                </div>
            </div>
        </header>

        <Loading />

        <NuxtPage />

        <AppFooter />

        <!-- Back To Top Button -->
        <div id="backtotop"><a href="#"></a></div>
    </div>
</template>

<script setup></script>
