<template>
    <!-- Sign In Popup -->
    <div id="sign-in-dialog" class="zoom-anim-dialog mfp-hide">
        <div class="small-dialog-header">
            <h3>Đăng ký/Đăng nhập</h3>
        </div>

        <div class="sign-in-form style-1">
            <form class="auth-form" @submit.prevent>
                <!-- Sign-in with Phone Number -->
                <div v-show="!showRegisterFields">
                    <!-- Phone Input -->
                    <div class="form-row form-row-wide">
                        <label for="phone">
                            Số điện thoại:
                            <i class="im im-icon-Phone-2"></i>
                            <input type="tel" class="input-text" id="phone" v-model="phone" required placeholder="+84..." />
                        </label>
                    </div>

                    <!-- Send OTP Button -->
                    <div class="form-row" v-if="!otpSent">
                        <button @click="sendOTP" class="button border fw margin-top-10" :disabled="loading">
                            <span v-if="loading" class="spinner"></span>
                            {{ loading ? 'Đang gửi...' : 'Gửi OTP' }}
                        </button>
                        <div id="recaptcha-container"></div>
                    </div>

                    <!-- OTP Input and Verification -->
                    <div v-if="otpSent">
                        <div class="form-row form-row-wide">
                            <label for="otp">
                                Mã OTP:
                                <i class="im im-icon-Mailbox-Empty"></i>
                                <input type="text" class="input-text" id="otp" v-model="otp" />
                            </label>
                        </div>
                        <button @click="verifyOTP" class="button border fw margin-top-10" :disabled="loading">
                            <span v-if="loading" class="spinner"></span>
                            {{ loading ? 'Đang xác minh...' : 'Xác minh OTP' }}
                        </button>
                    </div>
                </div>

                <!-- Registration Fields (shown if user doesn't exist) -->
                <div v-show="showRegisterFields">
                    <div class="form-row form-row-wide">
                        <label for="name">
                            Họ và tên:
                            <i class="im im-icon-Male"></i>
                            <input type="text" class="input-text" id="name" v-model="name" required />
                        </label>
                    </div>

                    <div class="form-row form-row-wide">
                        <label for="birthdate">
                            Ngày sinh:
                            <i class="im im-icon-Birthday-Cake"></i>
                            <input class="input-text" type="date" id="birthdate" v-model="birthdate" required />
                        </label>
                    </div>

                    <div class="form-row form-row-wide">
                        <label for="email">
                            Email:
                            <i class="im im-icon-Mail"></i>
                            <input
                                type="email"
                                class="input-text"
                                id="email"
                                v-model="email"
                                required
                                pattern="^[A-Za-z0-9](([_\.\-]?[a-zA-Z0-9]+)*)@([A-Za-z0-9]+)(([\.\-]?[a-zA-Z0-9]+)*)\.([A-Za-z]{2,})$"
                            />
                        </label>
                    </div>

                    <button @click="registerUser" class="button border fw margin-top-10" :disabled="loading">
                        <span v-if="loading" class="spinner"></span>
                        {{ loading ? 'Đang đăng ký...' : 'Hoàn tất đăng ký' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { useAuth } from '~/composables/useAuth';

const { phone, otp, otpSent, showRegisterFields, name, email, birthdate, loading, sendOTP, verifyOTP, registerUser } = useAuth();
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
