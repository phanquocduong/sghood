<template>
    <!-- Map Container -->
    <div class="contact-map margin-bottom-60">
        <!-- Google Maps -->
        <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3545.98648316269!2d106.62092318328997!3d10.853900716383444!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752b6c59ba4c97%3A0x535e784068f1558b!2zVHLGsOG7nW5nIENhbyDEkeG6s25nIEZQVCBQb2x5dGVjaG5pYw!5e1!3m2!1svi!2s!4v1746097904850!5m2!1svi!2s"
            width="100%"
            height="450"
            style="border: 0"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
        ></iframe>

        <!-- Office -->
        <div class="address-box-container">
            <div class="address-container" data-background-image="/images/our-office.jpg">
                <div class="office-address">
                    <h3>Văn phòng chúng tôi</h3>
                    <ul>
                        <li>141 - 143, Trung Mỹ Tây, Quận 12</li>
                        <li>TP.HCM</li>
                        <li>Điện thoại (123) 123-456</li>
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
                        Quản lý thông minh các kênh thuê trọ tại TP.HCM. Tối ưu hóa trải nghiệm thuê trọ với giải pháp hiện đại từ Trọ Việt.
                    </p>
                    <ul class="contact-details">
                        <li><i class="im im-icon-Phone-2"></i> <strong>Phone:</strong> <span>(123) 123-456</span></li>
                        <li><i class="im im-icon-Fax"></i> <strong>Fax:</strong> <span>(123) 123-456</span></li>
                        <li>
                            <i class="im im-icon-Globe"></i> <strong>Web:</strong> <span><a href="#">www.troviet.com</a></span>
                        </li>
                        <li>
                            <i class="im im-icon-Envelope"></i> <strong>Email:</strong> <span><a href="#">troviet@gmail.com</a></span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-md-8">
                <section id="contact">
                    <h4 class="headline margin-bottom-35">Liên Hệ Với Chúng Tôi</h4>
                    <div id="contact-message"></div>
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
                                name="message"
                                cols="40"
                                rows="3"
                                id="contact-message"
                                placeholder="Lời nhắn"
                                spellcheck="true"
                                required="required"
                                v-model="message"
                            ></textarea>
                        </div>
                        <input type="submit" class="submit button" id="submit" value="Gửi tin nhắn" :disabled="loading" />
                        <div
                            v-if="noficationMessage"
                            :class="{ 'notification-success': res?.status === true, 'notification-error': res?.status !== true }"
                            style="margin-top: 10px"
                        >
                            {{ noficationMessage }}
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '~/stores/auth';
const { $api } = useNuxtApp();
const name = ref('');
const email = ref('');
const subject = ref('');
const message = ref('');
const loading = ref(false);
const noficationMessage = ref('');
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
            noficationMessage.value = '✅ Gửi thành công! Cảm ơn bạn đã liên hệ.';
            name.value = '';
            email.value = '';
            subject.value = '';
            message.value = '';
        } else {
            noficationMessage.value = `❌ Gửi thất bại: ${res?.message || 'Lỗi không xác định.'}`;
        }
    } catch (error) {
        noficationMessage.value = '❌ Gửi thất bại: Lỗi kết nối đến máy chủ.';
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.notification-success {
    color: #2e7d32;
    background-color: #e6f4ea;
    padding: 10px;
    border-left: 4px solid #2e7d32;
    margin-top: 10px;
    border-radius: 4px;
}
.notification-error {
    color: #c62828;
    background-color: #fdecea;
    padding: 10px;
    border-left: 4px solid #c62828;
    margin-top: 10px;
    border-radius: 4px;
}

.container > .row {
    margin-bottom: 40px;
}
</style>
