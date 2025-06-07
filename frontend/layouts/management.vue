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

// Lấy store và router
const authStore = useAuthStore();
const router = useRouter();
const toast = useToast();

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
</style>
