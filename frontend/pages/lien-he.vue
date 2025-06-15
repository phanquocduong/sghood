<template>
    <!-- Map Container -->
    <div class="contact-map margin-bottom-60">
        <!-- Google Maps -->
        <iframe
            v-if="config?.gg_map"
            :src="config.gg_map"
            width="100%"
            height="450"
            style="border: 0"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
        ></iframe>
        <div class="loading-overlay" v-show="loading">
            <div class=""></div>
            <p></p>
        </div>
        <!-- Office -->
        <div class="address-box-container">
            <div class="address-container" data-background-image="/images/our-office.jpg">
                <div class="office-address">
                    <h3>Văn phòng chúng tôi</h3>
                    <ul>
                        <li v-if="config?.dia_chi">{{ config.dia_chi }}</li>
                        <li></li>
                        <li v-if="config?.sdt">Điện thoại {{ config.sdt }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Container / Start -->
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h4 class="headline margin-bottom-30">Tìm Chúng Tôi Tại Đây</h4>
                <div class="sidebar-textbox">
                    <p>
                        {{ config.nav_lien_he }}
                    </p>
                    <ul class="contact-details">
                        <li>
                            <i class="im im-icon-Phone-2"></i> <strong>Phone:</strong> <span>{{ config.sdt }}</span>
                        </li>
                        <li>
                            <i class="im im-icon-Globe"></i> <strong>Web:</strong>
                            <span
                                ><a :href="config.dia_chi_web">{{ config.dia_chi_web }}</a></span
                            >
                        </li>
                        <li>
                            <i class="im im-icon-Envelope"></i> <strong>Email:</strong>
                            <span
                                ><a :href="config.email">{{ config.email }}</a></span
                            >
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-md-8">
                <section id="contact">
                    <h4 class="headline margin-bottom-35">Liên Hệ Với Chúng Tôi</h4>
                    <form @submit.prevent="handleSubmit" name="contactform" id="form-contact" autocomplete="on">
                        <div class="row">
                            <div class="col-md-6">
                                <div>
                                    <input
                                        name="name"
                                        type="text"
                                        id="contact-name"
                                        placeholder="Họ và tên"
                                        required="required"
                                        v-model="name"
                                    />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div>
                                    <input
                                        name="email"
                                        type="text"
                                        id="contact-email"
                                        placeholder="Email"
                                        pattern="^[A-Za-z0-9](([_\.\-]?[a-zA-Z0-9]+)*)@([A-Za-z0-9]+)(([\.\-]?[a-zA-Z0-9]+)*)\.([A-Za-z]{2,})$"
                                        required="required"
                                        v-model="email"
                                    />
                                </div>
                            </div>
                        </div>
                        <div>
                            <input
                                name="subject"
                                type="text"
                                id="subject-contact"
                                placeholder="Chủ đề"
                                required="required"
                                v-model="subject"
                            />
                        </div>
                        <div>
                            <textarea
                                v-model="message"
                                name="message"
                                cols="40"
                                rows="3"
                                id="contact-Message"
                                placeholder="Lời nhắn"
                                spellcheck="true"
                                required="required"
                                style="min-height: 180px; width: 100%"
                            ></textarea>
                        </div>

                        <button
                            type="submit"
                            class="submit button"
                            id="submit"
                            value="Gửi tin nhắn"
                            :disabled="loading"
                            style="margin-bottom: 10px; margin-top: -10px"
                        >
                            <span v-if="loading" class="spinner"></span>
                            {{ loading ? ' Đang gửi...' : 'Gửi đi' }}
                        </button>
                    </form>
                </section>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '~/stores/auth';
import { useToast } from 'vue-toastification';
// api config
const config = useState('configs');
const baseUrl = useRuntimeConfig().public.baseUrl;
console.log('Config:', config.value);
// api lien he
const toast = useToast();
const { $api } = useNuxtApp();
const name = ref('');
const email = ref('');
const subject = ref('');
const message = ref('');
const loading = ref(false);
const authStore = useAuthStore();
onMounted(() => {
    if (authStore.user) {
        name.value = authStore.user.name || '';
        email.value = authStore.user.email || '';
    }
});
const handleSubmit = async () => {
    loading.value = true;
    try {
        const res = await $api('/contact', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value
            },
            body: {
                name: name.value,
                email: email.value,
                subject: subject.value,
                message: message.value
            }
        });
        console.log(res.value);
        if (res?.status === true) {
            toast.success('✅ Gửi thành công! Cảm ơn bạn đã liên hệ.');
            subject.value = '';
            message.value = '';
        } else {
            toast.error(`❌ Gửi thất bại: ${res?.message || 'Lỗi không xác định.'}`);
        }
    } catch (error) {
        toast.error('❌ Gửi thất bại: Lỗi kết nối đến máy chủ.');
    } finally {
        loading.value = false;
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
.textarea {
    min-height: 120px;
    width: 100%;
}

.container > .row {
    margin-bottom: 40px;
}
</style>
