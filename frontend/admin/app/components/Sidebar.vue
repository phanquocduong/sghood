<template>
    <aside class="w-64 overflow-auto h-screen bg-[#292929] text-white flex flex-col ml-auto hide-scrollbar">
        <!-- Logo -->
        <div class="flex items-center gap-2 text-5xl font-bold mb-8 p-4 bg-[#333]">
            <svg class="w-10 h-10 text-pink-500" fill="currentColor" viewBox="0 0 24 24">
                <path
                    d="M12 2C8 2 4 5.5 4 10c0 5.2 7.5 11.4 7.8 11.6a1 1 0 0 0 1.3 0C12.5 21.4 20 15.2 20 10c0-4.5-4-8-8-8zm0 12a4 4 0 1 1 0-8 4 4 0 0 1 0 8z"
                />
            </svg>
            <span>listeo</span>
        </div>

        <!-- MAIN -->
        <div class="text-xs text-gray-400 uppercase mb-2 p-4">Chính</div>
        <nav class="flex flex-col gap-2 mb-6">
            <NuxtLink
                to="/"
                :class="[
                    'flex items-center gap-3 px-3 py-2 pl-4 hover:bg-[#333] text-white hover:border-l-4 hover:border-red-500 border-transparent delay-75',
                    isActive('/') ? 'bg-[#333] border-l-4 border-red-500 hover:border-red-500 border-transparent' : ''
                ]"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 4v16m8-8H4" />
                </svg>
                Bảng điều khiển
            </NuxtLink>

            <NuxtLink
                to="/messages"
                :class="[
                    'relative flex items-center gap-3 px-3 py-2 pl-4 hover:bg-[#333] text-white hover:border-l-4 hover:border-red-500 border-transparent delay-75',
                    isActive('/messages') ? 'bg-[#333] border-l-4 border-red-500' : ''
                ]"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M4 4h16v16H4z" />
                </svg>
                Tin nhắn
                <span class="absolute right-3 bg-green-500 text-xs rounded-full px-2 py-0.5">2</span>
            </NuxtLink>

            <NuxtLink
                to="/bookings"
                :class="[
                    'flex items-center gap-3 px-3 py-2 pl-4 hover:bg-[#333] text-white hover:border-l-4 hover:border-red-500 border-transparent delay-75',
                    isActive('/bookings') ? 'bg-[#333] border-l-4 border-red-500' : ''
                ]"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 8h18M3 16h18" />
                </svg>
                Đặt chỗ
            </NuxtLink>

            <NuxtLink
                to="/wallet"
                :class="[
                    'flex items-center gap-3 px-3 py-2 pl-4 hover:bg-[#333] text-white hover:border-l-4 hover:border-red-500 border-transparent delay-75',
                    isActive('/wallet') ? 'bg-[#333] border-l-4 border-red-500' : ''
                ]"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M5 12h14M12 5v14" />
                </svg>
                Ví tiền
            </NuxtLink>
        </nav>

        <!-- LISTINGS -->
        <div class="text-xs text-gray-400 uppercase mb-2 p-4">Danh sách</div>
        <nav class="flex flex-col gap-2 mb-6 cursor-pointer">
            <button
                @click="toggleMenu"
                :class="[
                    'flex items-center gap-3 px-3 py-2 pl-4 hover:bg-[#333] text-white hover:border-l-4 hover:border-red-500 border-transparent delay-75 w-full',
                    isPrefix('/List') ? 'bg-[#333] border-l-4 border-red-500' : ''
                ]"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M3 7h18M3 12h18M3 17h18" />
                </svg>
                Danh sách của tôi
            </button>

            <transition name="fade-slide">
                <div v-if="isOpen" class="bg-[#292929] p-3 py-2 hover:border-l-4 hover:border-red-500 delay-75">
                    <NuxtLink
                        to="/ListMotels"
                        :class="[
                            'p-4 block py-3 hover:scale-110 transition-transform duration-300 ease-in-out',
                            isActive('/ListMotels') ? 'font-bold text-red-400' : ''
                        ]"
                    >
                        Danh sách Phòng
                    </NuxtLink>
                    <NuxtLink
                        to="/ListDistricts"
                        :class="[
                            'p-4 block py-3 hover:scale-110 transition-transform duration-300 ease-in-out',
                            isActive('/ListDistricts') ? 'font-bold text-red-400' : ''
                        ]"
                    >
                        Danh sách khu vực
                    </NuxtLink>
                    <NuxtLink
                        to="/ListUsers"
                        :class="[
                            'p-4 block py-3 hover:scale-110 transition-transform duration-300 ease-in-out',
                            isActive('/ListUsers') ? 'font-bold text-red-400' : ''
                        ]"
                    >
                        Danh sách người dùng
                    </NuxtLink>
                    <NuxtLink
                        to="/ListAmenities"
                        :class="[
                            'p-4 block py-3 hover:scale-110 transition-transform duration-300 ease-in-out',
                            isActive('/ListAmenities') ? 'font-bold text-red-400' : ''
                        ]"
                    >
                        Danh sách tiện ích
                    </NuxtLink>
                </div>
            </transition>

            <NuxtLink
                to="/reviews"
                :class="[
                    'flex items-center gap-3 px-3 py-2 pl-4 hover:bg-[#333] text-white hover:border-l-4 hover:border-red-500 border-transparent delay-75',
                    isActive('/reviews') ? 'bg-[#333] border-l-4 border-red-500' : ''
                ]"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 20l9-5-9-5-9 5 9 5z" />
                </svg>
                Đánh giá
            </NuxtLink>

            <NuxtLink
                to="/saved"
                :class="[
                    'flex items-center gap-3 px-3 py-2 pl-4 hover:bg-[#333] text-white hover:border-l-4 hover:border-red-500 border-transparent delay-75',
                    isActive('/saved') ? 'bg-[#333] border-l-4 border-red-500' : ''
                ]"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M5 12h14M12 5v14" />
                </svg>
                Đã lưu
            </NuxtLink>

            <NuxtLink
                to="AddMotels"
                :class="[
                    'flex items-center gap-3 px-3 py-2 pl-4 hover:bg-[#333] text-white hover:border-l-4 hover:border-red-500 border-transparent delay-75',
                    isActive('AddMotels') ? 'bg-[#333] border-l-4 border-red-500' : ''
                ]"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 4v16m8-8H4" />
                </svg>
                Thêm danh sách
            </NuxtLink>
        </nav>

        <!-- ACCOUNT -->
        <div class="text-xs text-gray-400 uppercase mb-2 p-4">Tài khoản</div>
        <nav class="flex flex-col gap-2">
            <NuxtLink
                to="/profile"
                :class="[
                    'flex items-center gap-3 px-3 py-2 pl-4 hover:bg-[#333] text-white hover:border-l-4 hover:border-red-500 border-transparent delay-75',
                    isActive('/profile') ? 'bg-[#333] border-l-4 border-red-500' : ''
                ]"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zM2 22c0-4.4 5.3-8 10-8s10 3.6 10 8" />
                </svg>
                Hồ sơ cá nhân
            </NuxtLink>

            <a
                href="#"
                @click.prevent="logout"
                class="flex items-center gap-3 px-3 py-2 pl-4 hover:bg-[#333] text-white hover:border-l-4 hover:border-red-500 border-transparent delay-75"
            >
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12" />
                </svg>
                Đăng xuất
            </a>
        </nav>
    </aside>
</template>

<script setup lang="ts">
import { useAuth } from '~/composables/useAuth';

const { logout } = useAuth();
const route = useRoute();
const isOpen = ref(route.path.startsWith('/List'));

const toggleMenu = () => {
    isOpen.value = !isOpen.value;
};

const isActive = (path: string) => route.path === path;
const isPrefix = (prefix: string) => route.path.startsWith(prefix);
</script>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.3s ease;
}
.fade-slide-enter-from,
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
.fade-slide-enter-to,
.fade-slide-leave-from {
    opacity: 1;
    transform: translateY(0);
}
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
