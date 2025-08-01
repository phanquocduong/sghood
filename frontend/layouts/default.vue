<template>
    <Loading :is-loading="isLoading" />
    <div v-if="!isLoading" id="wrapper">
        <!-- Header -->
        <header id="header-container">
            <div id="header">
                <div class="container">
                    <div class="left-side">
                        <!-- Logo -->
                        <div id="logo">
                            <NuxtLink to="/"><img v-if="config?.main_logo" :src="baseUrl + config.main_logo" /></NuxtLink>
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
        <div class="clearfix"></div>

        <NuxtPage />

        <AppFooter />
        <!-- Back To Top Button -->
        <div id="backtotop"><a href="#"></a></div>

        <!-- chatbox -->
        <div>
            <ChatIcon v-if="user" :unreadMessages="unreadMessages" @toggle="toggleChat" />
            <div>
                <ChatBox v-if="user" :isOpen="isChatOpen" @close="isChatOpen = false" @unread="onUnreadMessage"></ChatBox>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useRoute } from 'vue-router'; 
import { ref, watch, nextTick } from 'vue';
import { useAuthStore } from '~/stores/auth';
import ChatIcon from '~/components/partials/ChatIcon.vue';
import ChatBox from '~/components/partials/ChatBox.vue';

const route = useRoute();
const isLoading = ref(false);
const config = useState('configs');
const baseUrl = useRuntimeConfig().public.baseUrl;
const isChatOpen = ref(false);
const unreadMessages = ref(0);
const authStore = useAuthStore();
const { user } = storeToRefs(authStore);

const toggleChat = () => {
    isChatOpen.value = !isChatOpen.value;
    if (isChatOpen.value) {
        unreadMessages.value = 0;
    }
};
const onUnreadMessage = () => {
    if (!isChatOpen.value) {
        unreadMessages.value++;
    }
};

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