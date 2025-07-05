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
            </ul>
        </div>
    </div>
</template>

<script setup>
const navItems = [
    { path: '/quan-ly/thong-bao', icon: 'sl sl-icon-envelope-open', label: 'Thông báo', badge: 2 },
    { path: '/quan-ly/ho-so-ca-nhan', icon: 'sl sl-icon-user', label: 'Hồ sơ cá nhân' },
    { path: '/quan-ly/lich-xem-nha-tro', icon: 'im im-icon-Calendar', label: 'Lịch xem nhà trọ' },
    { path: '/quan-ly/dat-phong', icon: 'fa fa-calendar-check-o', label: 'Đặt phòng' },
    { path: '/quan-ly/hop-dong', icon: 'im im-icon-Book', label: 'Hợp đồng' },
    { path: '/quan-ly/hoa-don', icon: 'im im-icon-Billing', label: 'Hoá đơn' },
    { path: '/quan-ly/lich-su-giao-dich', icon: 'sl sl-icon-wallet', label: 'Lịch sử giao dịch' },
    { path: '/quan-ly/yeu-cau-sua-chua', icon: 'im im-icon-Drill-2', label: 'Quản lý sữa chữa' }
];
</script>
