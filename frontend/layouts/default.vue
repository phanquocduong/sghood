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
                            <NuxtLink to="/"><img v-if="config?.logo_doc" :src="baseUrl + config.logo_doc" /></NuxtLink>
                        </div>

                        <!-- Mobile Navigation -->
                        <MobileNavigation />

                        <!-- Main Navigation -->
                        <MainNavigation />
                    </div>

                    <UserMenu />

                    <div id="sign-in-dialog" class="zoom-anim-dialog mfp-hide">
                        <div class="small-dialog-header">
                            <h3>Đăng ký/Đăng nhập</h3>
                        </div>

                        <div class="sign-in-form style-1">
                            <ul class="tabs-nav">
                                <li><a href="#login">Đăng nhập</a></li>
                                <li><a href="#register">Đăng ký</a></li>
                            </ul>

                            <div class="tabs-container alt">
                                <LoginForm />
                                <RegisterForm />
                                <ForgotPasswordForm />
                            </div>
                        </div>
                    </div>
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

<script setup>
const config = useState('configs');
const baseUrl = useRuntimeConfig().public.baseUrl;
console.log('Header config:', config.value);
</script>
