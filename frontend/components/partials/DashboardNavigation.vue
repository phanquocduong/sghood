<!-- components/DashboardNav.vue -->
<template>
    <div class="dashboard-nav">
        <div class="dashboard-nav-inner">
            <ul>
                <li v-for="item in navItems" :key="item.path" :class="{ active: $route.path === item.path }">
                    <NuxtLink :to="item.path">
                        <i :class="item.icon"></i> {{ item.label }}
                        <span v-if="item.badge" class="nav-tag messages">{{ item.badge }}</span>
                    </NuxtLink>
                </li>
                <li>
                    <a><i class="im im-icon-File-Clipboard"></i> Yêu cầu</a>
                    <ul>
                        <li>
                            <NuxtLink to="/quan-ly/yeu-cau/gia-han-hop-dong">Gia hạn</NuxtLink>
                        </li>
                        <li>
                            <a href="dashboard-my-listings.html">Trả phòng</a>
                        </li>
                        <li>
                            <a href="dashboard-my-listings.html">Hoàn tiền</a>
                        </li>
                        <li>
                            <a href="dashboard-my-listings.html">Sửa chữa</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '~/stores/auth';

const authUser = useAuthStore();
const role = computed(() => authUser.user?.role || '');
const FullnavItems = [
    { path: '/quan-ly/thong-bao', icon: 'sl sl-icon-envelope-open', label: 'Thông báo' },
    { path: '/quan-ly/ho-so-ca-nhan', icon: 'sl sl-icon-user', label: 'Hồ sơ cá nhân' },
    { path: '/quan-ly/lich-xem-nha-tro', icon: 'im im-icon-Dec', label: 'Lịch xem nhà trọ' },
    { path: '/quan-ly/dat-phong', icon: 'im im-icon-Bookmark', label: 'Đặt phòng' },
    { path: '/quan-ly/hop-dong', icon: 'im im-icon-Book', label: 'Hợp đồng' },
    { path: '/quan-ly/hoa-don', icon: 'im im-icon-Billing', label: 'Hoá đơn' },
    { path: '/quan-ly/lich-su-giao-dich', icon: 'sl sl-icon-wallet', label: 'Lịch sử giao dịch' },
    { path: '/quan-ly/yeu-cau-sua-chua', icon: 'im im-icon-Drill-2', label: 'Quản lý sữa chữa', roles: ['Người thuê'] }
];
const navItems = computed(() => {
    return FullnavItems.filter(item => {
        return !item.roles || item.roles.includes(role.value);
    });
});
</script>
