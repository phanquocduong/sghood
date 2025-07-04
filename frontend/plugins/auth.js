import { useAuthStore } from '~/stores/auth';

export default defineNuxtPlugin(async nuxtApp => {
    // Only run on client side
    if (process.client) {
        const authStore = useAuthStore();

        try {
            // Check if user is authenticated
            await authStore.checkAuth();
        } catch (error) {
            console.error('Auth initialization failed:', error);
            // Don't throw error, just log it
        }
    }
});
