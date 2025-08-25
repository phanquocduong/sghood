<!-- Template cho trang liên hệ -->
<template>
    <!-- Container bản đồ liên hệ -->
    <div class="contact-map margin-bottom-60">
        <!-- Iframe hiển thị Google Maps -->
        <iframe
            v-if="config?.google_map_url"
            :src="config.google_map_url"
            width="100%"
            height="450"
            style="border: 0"
            allowfullscreen=""
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
        ></iframe>
        <!-- Lớp phủ hiển thị khi đang tải -->
        <div class="loading-overlay" v-show="loading">
            <div class=""></div>
            <p></p>
        </div>
        <!-- Thông tin văn phòng -->
        <div class="address-box-container">
            <div class="address-container" data-background-image="/images/our-office.jpg">
                <div class="office-address">
                    <h3>Văn phòng chúng tôi</h3>
                    <ul>
                        <!-- Hiển thị địa chỉ văn phòng nếu có -->
                        <li v-if="config?.office_address">{{ config.office_address }}</li>
                        <li></li>
                        <!-- Hiển thị số điện thoại liên hệ nếu có -->
                        <li v-if="config?.contact_phone">Điện thoại: {{ config.contact_phone }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Container chính -->
    <div class="container">
        <div class="row">
            <!-- Cột thông tin liên hệ -->
            <div class="col-md-4">
                <h4 class="headline margin-bottom-30">Tìm Chúng Tôi Tại Đây</h4>
                <div class="sidebar-textbox">
                    <!-- Mô tả ngắn về SGHood -->
                    <p>
                        {{ config.sghood_short_desc }}
                    </p>
                    <ul class="contact-details">
                        <!-- Số điện thoại liên hệ -->
                        <li>
                            <i class="im im-icon-Phone-2"></i> <strong>Số điện thoại:</strong> <span>{{ config.contact_phone }}</span>
                        </li>
                        <!-- Địa chỉ website -->
                        <li>
                            <i class="im im-icon-Globe"></i> <strong>Địa chỉ website:</strong>
                            <span>
                                <a :href="config.website_address">{{ config.website_address }}</a>
                            </span>
                        </li>
                        <!-- Email liên hệ -->
                        <li>
                            <i class="im im-icon-Envelope"></i> <strong>Email liên hệ:</strong>
                            <span>
                                <a :href="config.contact_email">{{ config.contact_email }}</a>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Form liên hệ -->
            <div class="col-md-8">
                <section id="contact">
                    <h4 class="headline margin-bottom-35">Liên Hệ Với Chúng Tôi</h4>
                    <!-- Form gửi thông tin liên hệ -->
                    <form @submit.prevent="handleSubmit" name="contactform" id="form-contact" autocomplete="on">
                        <div class="row">
                            <!-- Trường nhập họ và tên -->
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
                            <!-- Trường nhập email -->
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
                        <!-- Trường nhập chủ đề -->
                        <div>
                            <input name="subject" type="text" id="subject-contact" placeholder="Chủ đề" v-model="subject" />
                        </div>
                        <!-- Trường nhập lời nhắn -->
                        <div>
                            <textarea
                                v-model="message"
                                name="message"
                                cols="40"
                                rows="3"
                                id="contact-Message"
                                placeholder="Lời nhắn"
                                spellcheck="true"
                                style="min-height: 180px; width: 100%"
                            ></textarea>
                        </div>

                        <!-- Nút gửi form -->
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
import { useRoute } from 'vue-router';
import { useAppToast } from '~/composables/useToast';
import { useBehaviorStore } from '~/stores/behavior';
import { useHead } from '#app';

// Cấu hình SEO cho trang liên hệ
useHead({
    title: 'SGHood - Liên Hệ Với Chúng Tôi', // Tiêu đề trang
    meta: [
        { charset: 'utf-8' }, // Thiết lập mã hóa ký tự
        { name: 'viewport', content: 'width=device-width, initial-scale=1' }, // Responsive viewport
        {
            hid: 'description',
            name: 'description',
            content: 'Liên hệ với SGHood để được hỗ trợ về quản lý nhà trọ, thuê trọ và các dịch vụ liên quan tại TP. Hồ Chí Minh.' // Mô tả SEO
        },
        {
            name: 'keywords',
            content: 'SGHood, liên hệ, hỗ trợ thuê trọ, nhà trọ TP. Hồ Chí Minh, quản lý nhà trọ, dịch vụ thuê trọ' // Từ khóa SEO
        },
        { name: 'author', content: 'SGHood Team' }, // Tác giả
        // Open Graph metadata
        {
            property: 'og:title',
            content: 'SGHood - Liên Hệ Với Chúng Tôi' // Tiêu đề Open Graph
        },
        {
            property: 'og:description',
            content: 'Liên hệ với SGHood để được hỗ trợ về quản lý nhà trọ, thuê trọ và các dịch vụ liên quan tại TP. Hồ Chí Minh.' // Mô tả Open Graph
        },
        { property: 'og:type', content: 'website' }, // Loại nội dung Open Graph
        { property: 'og:url', content: 'https://sghood.com.vn/lien-he' } // URL Open Graph
    ]
});

// Lấy cấu hình từ state toàn cục
const config = useState('configs');
// Lấy store theo dõi hành vi người dùng
const behavior = useBehaviorStore();
// Lấy thông tin route hiện tại
const route = useRoute();

// Ghi log hành vi người dùng khi component được mount
onMounted(() => {
    behavior.addVisitedPage(route.path); // Lưu trang đã truy cập
    behavior.logAction(route.path, 'lien-he'); // Ghi log hành động
});

// Biến và hàm xử lý form liên hệ
const toast = useAppToast(); // Lấy composable hiển thị thông báo
const { $api } = useNuxtApp(); // Lấy đối tượng API từ Nuxt
const name = ref(''); // Biến lưu trữ họ và tên
const email = ref(''); // Biến lưu trữ email
const subject = ref(''); // Biến lưu trữ chủ đề
const message = ref(''); // Biến lưu trữ lời nhắn
const loading = ref(false); // Biến trạng thái đang tải
const authStore = useAuthStore(); // Lấy store xác thực

// Tự động điền thông tin người dùng nếu đã đăng nhập
onMounted(() => {
    if (authStore.user) {
        name.value = authStore.user.name || ''; // Điền tên người dùng
        email.value = authStore.user.email || ''; // Điền email người dùng
    }
});

// Hàm xử lý gửi form liên hệ
const handleSubmit = async () => {
    // Kiểm tra các trường bắt buộc
    if (!name.value) return toast.warning('Vui lòng nhập họ tên');
    if (!email.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) return toast.warning('Email không hợp lệ');
    if (!subject.value) return toast.warning('Vui lòng nhập chủ đề');
    if (!message.value) return toast.warning('Vui lòng nhập lời nhắn');

    loading.value = true; // Bật trạng thái đang tải
    try {
        // Gửi yêu cầu API liên hệ
        const res = await $api('/contact', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value // Gửi CSRF token
            },
            body: {
                name: name.value,
                email: email.value,
                subject: subject.value,
                message: message.value
            }
        });

        // Xử lý phản hồi từ API
        if (res?.status === true) {
            toast.success('Gửi thành công! Cảm ơn bạn đã liên hệ.'); // Hiển thị thông báo thành công
            subject.value = ''; // Xóa trường chủ đề
            message.value = ''; // Xóa trường lời nhắn
        } else {
            toast.error(` Gửi thất bại: ${res?.message || 'Lỗi không xác định.'}`); // Hiển thị thông báo lỗi
        }
    } catch (error) {
        toast.error(' Gửi thất bại: Lỗi kết nối đến máy chủ.'); // Hiển thị lỗi kết nối
    } finally {
        loading.value = false; // Tắt trạng thái đang tải
    }
};
</script>

<!-- CSS tùy chỉnh cho trang -->
<style scoped>
/* CSS cho spinner loading */
.spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite; /* Hiệu ứng quay liên tục */
    margin-right: 8px;
    vertical-align: middle;
}

/* Keyframes cho hiệu ứng quay của spinner */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* CSS cho nút bị vô hiệu hóa */
.button:disabled {
    opacity: 0.6;
    cursor: not-allowed; /* Biểu thị con trỏ không thể nhấn */
}

/* CSS cho textarea */
.textarea {
    min-height: 120px;
    width: 100%;
}

/* CSS cho container chính */
.container > .row {
    margin-bottom: 40px; /* Khoảng cách dưới của hàng */
}
</style>
