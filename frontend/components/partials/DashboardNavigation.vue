<template>
    <!-- Thanh điều hướng chính của dashboard -->
    <div class="dashboard-nav">
        <div class="dashboard-nav-inner">
            <!-- Danh sách các mục điều hướng -->
            <ul>
                <!-- Lặp qua các mục điều hướng được lọc theo vai trò người dùng -->
                <li v-for="item in navItems" :key="item.path" :class="{ active: $route.path === item.path }">
                    <!-- Link điều hướng đến các trang tương ứng -->
                    <NuxtLink :to="item.path">
                        <i :class="item.icon"></i> {{ item.label }}
                        <!-- Hiển thị badge (ví dụ số thông báo) nếu có -->
                        <span v-if="item.badge" class="nav-tag messages">{{ item.badge }}</span>
                    </NuxtLink>
                </li>
                <!-- Mục điều hướng tĩnh cho yêu cầu hợp đồng -->
                <li>
                    <a><i class="im im-icon-File-Clipboard"></i> Yêu cầu hợp đồng</a>
                    <!-- Danh sách con cho các loại yêu cầu hợp đồng -->
                    <ul>
                        <li>
                            <NuxtLink to="/quan-ly/yeu-cau-hop-dong/gia-han">Gia hạn</NuxtLink>
                        </li>
                        <li>
                            <NuxtLink to="/quan-ly/yeu-cau-hop-dong/tra-phong">Trả phòng</NuxtLink>
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

// Lấy thông tin người dùng từ store auth
const authUser = useAuthStore();
// Tính toán vai trò của người dùng hiện tại
const role = computed(() => authUser.user?.role || '');

// Danh sách đầy đủ các mục điều hướng
const FullnavItems = [
    { path: '/quan-ly/thong-bao', icon: 'sl sl-icon-bell', label: 'Thông báo' },
    { path: '/quan-ly/ho-so-ca-nhan', icon: 'sl sl-icon-user', label: 'Hồ sơ cá nhân' },
    { path: '/quan-ly/lich-xem-nha-tro', icon: 'im im-icon-Dec', label: 'Lịch xem nhà trọ' },
    { path: '/quan-ly/dat-phong', icon: 'im im-icon-Bookmark', label: 'Đặt phòng' },
    { path: '/quan-ly/hop-dong', icon: 'im im-icon-Book', label: 'Hợp đồng' },
    { path: '/quan-ly/hoa-don', icon: 'im im-icon-Billing', label: 'Hoá đơn' },
    { path: '/quan-ly/lich-su-giao-dich', icon: 'sl sl-icon-wallet', label: 'Lịch sử giao dịch' },
    // Mục yêu cầu sửa chữa chỉ hiển thị cho vai trò "Người thuê"
    { path: '/quan-ly/yeu-cau-sua-chua', icon: 'im im-icon-Drill-2', label: 'Yêu cầu sửa chữa', roles: ['Người thuê'] }
];

// Lọc các mục điều hướng dựa trên vai trò người dùng
const navItems = computed(() => {
    return FullnavItems.filter(item => {
        // Chỉ hiển thị mục nếu không có hạn chế vai trò hoặc vai trò người dùng phù hợp
        return !item.roles || item.roles.includes(role.value);
    });
});
</script>
