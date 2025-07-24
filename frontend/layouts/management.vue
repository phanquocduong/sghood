<template>
    <Loading :is-loading="isLoading" />
    <div v-if="!isLoading" id="wrapper">
        <!-- Header Container -->
        <header id="header-container" class="fixed fullwidth dashboard">
            <div id="header" class="not-sticky">
                <div class="container">
                    <div class="left-side">
                        <!-- Logo -->
                        <div id="logo">
                            <NuxtLink to="/"
                                ><img v-if="config && config.secondary_logo" :src="baseUrl + config.secondary_logo" alt="SGHood Logo"
                            /></NuxtLink>
                            <NuxtLink to="/" class="dashboard-logo"
                                ><img v-if="config && config.secondary_logo" :src="baseUrl + config.secondary_logo" alt="SGHood Logo"
                            /></NuxtLink>
                        </div>

                        <!-- Mobile Navigation -->
                        <MobileNavigation />

                        <!-- Main Navigation -->
                        <MainNavigation />
                    </div>

                    <!-- Right Side Content -->
                    <UserMenu />
                </div>
            </div>
        </header>

        <!-- Dashboard -->
        <div id="dashboard">
            <a href="#" class="dashboard-responsive-nav-trigger"><i class="fa fa-reorder"></i>Thanh điều hướng</a>
            <DashboardNavigation />
            <Loading />
            <!-- Content -->
            <div class="dashboard-content">
                <NuxtPage />
                <!-- Copyrights -->
                <div class="col-md-12">
                    <div class="copyrights">{{ config.copyright_title }}</div>
                </div>
            </div>
            <div>
                <ChatIcon v-if="user" :unreadMessages="unreadMessages" @toggle="toggleChat" />
                <div>
                    <ChatBox v-if="user" :isOpen="isChatOpen" @close="isChatOpen = false" @unread="onUnreadMessage"></ChatBox>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useRouter, useRoute } from 'vue-router';
import { ref, watch, nextTick, onMounted } from 'vue';
import { useToast } from 'vue-toastification';
import { useAuthStore } from '~/stores/auth';
const route = useRoute();
const isLoading = ref(false);
const config = useState('configs');
const baseUrl = useRuntimeConfig().public.baseUrl;
const isChatOpen = ref(false);
const unreadMessages = ref(0);
const authStore = useAuthStore();
const { user } = storeToRefs(authStore);
const toast = useToast();
const router = useRouter();

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

const checkAuth = async () => {
    if (!authStore.user) {
        await authStore.fetchUser();
    }

    if (!authStore.user) {
        toast.error('Vui lòng đăng nhập!');
        router.push('/');
    }
};

onMounted(() => {
    checkAuth();
    console.log('user ở manager:', user.value);
});
</script>

<style scoped>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    transition: opacity 0.3s ease;
}

.loading-overlay[style*='display: none'] {
    opacity: 0;
    pointer-events: none;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #ddd;
    border-top: 5px solid #f91942;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% {
        transform: rotate(0);
    }
    100% {
        transform: rotate(360deg);
    }
}

.container {
    max-width: 100% !important;
}

.copyrights {
    font-size: 16px !important;
}
</style>
