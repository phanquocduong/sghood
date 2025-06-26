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
                            <NuxtLink to="/" class="dashboard-logo"
                                ><img v-if="config && config.logo_ngang" :src="baseUrl + config.logo_ngang" alt=""
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
            <a href="#" class="dashboard-responsive-nav-trigger"><i class="fa fa-reorder"></i></a>
            <DashboardNavigation />
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

<script setup>
import { useAuthStore } from '~/stores/auth';
import { useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';
import { useRoute } from 'vue-router';
import { ref, watch, nextTick } from 'vue';
// Lấy store và router
const authStore = useAuthStore();
const router = useRouter();
const toast = useToast();
const route = useRoute();
const isLoading = ref(false);
const config = useState('configs');
const baseUrl = useRuntimeConfig().public.baseUrl;
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

// Kiểm tra trạng thái đăng nhập
const checkAuth = async () => {
    // Nếu chưa có thông tin user, thử lấy từ server
    if (!authStore.user) {
        await authStore.fetchUser();
    }

    // Nếu vẫn không có user (chưa đăng nhập), chuyển hướng đến trang đăng nhập
    if (!authStore.user) {
        toast.error('Vui lòng đăng nhập!');
        router.push('/'); // Thay '/login' bằng đường dẫn đến trang đăng nhập của bạn
    }
};

// Gọi hàm kiểm tra ngay khi component được mount
onMounted(() => {
    checkAuth();
});
</script>

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
</style>
