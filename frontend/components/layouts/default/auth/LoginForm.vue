<template>
    <div class="tab-content" id="login" style="display: none">
        <form @submit.prevent="authStore.loginUser">
            <p class="form-row form-row-wide">
                <label for="username">
                    SĐT hoặc Email:
                    <i class="im im-icon-Male"></i>
                    <input type="text" class="input-text" name="username" id="username" v-model="username" required />
                </label>
            </p>

            <p class="form-row form-row-wide">
                <label for="password">
                    Mật khẩu:
                    <i class="im im-icon-Lock-2"></i>
                    <input class="input-text" type="password" name="password" id="password" v-model="password" required />
                </label>
                <span class="lost_password">
                    <a href="#" @click="showForgotPassword">Quên mật khẩu?</a>
                </span>
            </p>

            <div class="form-row">
                <button type="submit" class="button" :disabled="loading">
                    <span v-if="loading" class="spinner"></span>
                    {{ loading ? 'Đang đăng nhập...' : 'Đăng nhập' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { storeToRefs } from 'pinia';
import { useAuthStore } from '~/stores/auth';

const authStore = useAuthStore();
const { username, password, loading } = storeToRefs(authStore);

const showForgotPassword = () => {
    if (typeof window !== 'undefined' && window.$.magnificPopup) {
        window.$('.tab-content').hide();
        window.$('#forgot-password').show();
    }
};
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
    margin-right: 8px;
    vertical-align: middle;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>
