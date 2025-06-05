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
                <ClientOnly>
                    <div class="user-name">
                        <span>
                            <img :src="user?.avatar ? config.public.baseUrl + user.avatar : '/images/dashboard-avatar.jpg'" alt="Avatar" />
                        </span>
                        Xin chào, {{ user?.name || 'Người dùng' }}!
                    </div>
                </ClientOnly>

                <ul>
                    <li>
                        <NuxtLink to="/dashboard-messages"><i class="fa fa-bell-o"></i> Thông báo</NuxtLink>
                    </li>
                    <li>
                        <NuxtLink to="/quan-ly/ho-so-ca-nhan"><i class="sl sl-icon-user"></i> Hồ sơ cá nhân</NuxtLink>
                    </li>
                    <li>
                        <NuxtLink to="/dashboard-bookings"><i class="fa fa-calendar-check-o"></i> Đặt phòng</NuxtLink>
                    </li>
                    <li>
                        <a href="#" @click.prevent="authStore.logout"><i class="sl sl-icon-power"></i> Đăng xuất</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useAuthStore } from '~/stores/auth';

const config = useRuntimeConfig();
const authStore = useAuthStore();
const { user } = storeToRefs(authStore);
</script>

<style lang="scss" scoped></style>
