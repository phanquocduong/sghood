<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800">Đăng nhập Admin</h2>
            </div>
            <form @submit.prevent class="space-y-4">
                <!-- Phone Input -->
                <div class="relative">
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1"> Số điện thoại </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <Icon name="uil:phone" class="text-gray-400 text-lg" />
                        </span>
                        <input
                            v-model="phone"
                            type="tel"
                            id="phone"
                            placeholder="+84..."
                            class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#f91942]"
                            required
                        />
                    </div>
                </div>

                <!-- Submit Button (Send OTP) -->
                <div v-if="!otpSent" class="relative">
                    <button
                        @click="sendOTP"
                        class="w-full bg-[#f91942] text-white py-2 rounded-lg hover:bg-[#d11236] transition flex items-center justify-center"
                        :disabled="loading"
                    >
                        <span v-if="loading" class="spinner mr-2"></span>
                        {{ loading ? 'Đang gửi...' : 'Gửi OTP' }}
                    </button>
                    <div id="recaptcha-container"></div>
                </div>

                <!-- OTP Input -->
                <div v-if="otpSent" class="relative">
                    <label for="otp" class="block text-sm font-medium text-gray-700 mb-1"> Mã OTP </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <Icon name="mdi:lock" class="text-gray-400 text-lg" />
                        </span>
                        <input
                            v-model="otp"
                            type="text"
                            id="otp"
                            placeholder="Nhập mã OTP"
                            class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#f91942]"
                            required
                        />
                    </div>
                </div>

                <!-- Submit Button (Verify OTP) -->
                <button
                    v-if="otpSent"
                    @click="verifyOTP"
                    class="w-full bg-[#f91942] text-white py-2 rounded-lg hover:bg-[#d11236] transition flex items-center justify-center"
                    :disabled="loading"
                >
                    <span v-if="loading" class="spinner mr-2"></span>
                    {{ loading ? 'Đang xác minh...' : 'Xác minh OTP' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useAuth } from '~/composables/useAuth';
import { useRouter } from 'vue-router';
import { onMounted } from 'vue';

definePageMeta({
    layout: 'auth'
});

const { phone, otp, otpSent, loading, sendOTP, verifyOTP, user, role, authReady } = useAuth();
const router = useRouter();

onMounted(async () => {
    await authReady;
    if (user.value && role.value === 'Quản trị viên') {
        router.push('/dashboard');
    }
});
</script>

<style scoped>
.spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
