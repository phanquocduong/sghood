<template>
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
                    Hi, {{ user?.displayName || 'User' }}!
                </div>
                <ul>
                    <li>
                        <a href="/dashboard-messages"><i class="fa fa-bell-o"></i> Thông báo</a>
                    </li>
                    <li>
                        <NuxtLink to="/quan-ly/ho-so-ca-nhan"><i class="sl sl-icon-user"></i> Hồ sơ cá nhân</NuxtLink>
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
</template>

<script setup>
import { useAuth } from '~/composables/useAuth';

const { user, logout } = useAuth();
</script>

<style lang="scss" scoped></style>
