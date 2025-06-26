<template>
    <Loading :is-loading="isLoading" />
    <div v-if="!isLoading" id="wrapper">
        <!-- Header -->
        <header id="header-container" class="no-shadow">
            <div id="header">
                <div class="container">
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

        <NuxtPage />

        <AppFooter />
        <!-- Back To Top Button -->
        <div id="backtotop"><a href="#"></a></div>

        <!-- chatbox -->
        <div>
            <ChatIcon v-if="user" @toggle="toggleChat" />
            <ChatBox v-if="isChatOpen" @close="isChatOpen = false"></ChatBox>
        </div>
    </div>
</template>

<script setup>
import { useRoute } from 'vue-router';
import { ref, watch, nextTick } from 'vue';
import { useAuthStore } from '~/stores/auth';

const route = useRoute();
const isLoading = ref(false);
const config = useState('configs');
const baseUrl = useRuntimeConfig().public.baseUrl;
const isChatOpen = ref(false);
const authStore = useAuthStore();
const { user } = storeToRefs(authStore);
const toggleChat = () => (isChatOpen.value = !isChatOpen.value);

watch(
    () => route.fullPath,
    async () => {
        isLoading.value = true;
        await nextTick();
        setTimeout(() => {
            isLoading.value = false;
        }, 500);
    },
    { immediate: true }
);
</script>
