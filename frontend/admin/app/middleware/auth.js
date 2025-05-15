import { useAuth } from '~/composables/useAuth';

export default defineNuxtRouteMiddleware(async (to, from) => {
    const { user, role, authReady } = useAuth();

    await authReady; // Đợi trạng thái xác thực

    if (!user.value || role.value !== 'Quản trị viên') {
        return navigateTo('/'); // Chuyển hướng nếu không hợp lệ
    }
});
