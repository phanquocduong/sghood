<template>
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
            </div>
        </div>
    </div>
</template>

<script setup></script>

<style scoped></style>
