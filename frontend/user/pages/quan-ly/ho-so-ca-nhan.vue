<!-- pages/quan-ly/ho-so-ca-nhan.vue -->
<template>
    <!-- Titlebar -->
    <div id="titlebar">
        <div class="row">
            <div class="col-md-12">
                <h2>Hồ sơ cá nhân</h2>
                <!-- Breadcrumbs -->
                <nav id="breadcrumbs">
                    <ul>
                        <li><NuxtLink to="/">Trang chủ</NuxtLink></li>
                        <li>Hồ sơ cá nhân</li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Profile -->
        <div class="col-lg-6 col-md-12">
            <div class="dashboard-list-box margin-top-0">
                <h4 class="gray">Thông tin hồ sơ</h4>
                <div class="dashboard-list-box-static">
                    <!-- Avatar -->
                    <div class="edit-profile-photo">
                        <img :src="avatarUrl || '/images/user-avatar.jpg'" alt="Avatar" />
                        <div class="change-photo-btn">
                            <div class="photoUpload">
                                <span><i class="fa fa-upload"></i> Tải ảnh lên</span>
                                <input type="file" class="upload" @change="handleAvatarUpload" />
                            </div>
                        </div>
                    </div>

                    <!-- Details -->
                    <form @submit.prevent="handleSubmit" class="my-profile">
                        <label>Họ tên</label>
                        <input v-model="form.name" type="text" required />

                        <label>Số điện thoại</label>
                        <input v-model="form.phone" type="tel" required readonly />

                        <label>Email</label>
                        <input v-model="form.email" type="email" required />

                        <label>Ngày sinh</label>
                        <input v-model="form.birthDate" type="date" required />

                        <button type="submit" class="button margin-top-15">Lưu thay đổi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

// Định nghĩa layout
definePageMeta({
    layout: 'management'
});

// Dữ liệu form
const form = ref({
    name: 'Tom Perrin',
    phone: '+84828283169',
    email: 'tom@example.com',
    birthDate: ''
});

const avatarUrl = ref(null);

// Xử lý tải ảnh lên
const handleAvatarUpload = event => {
    const file = event.target.files[0];
    if (file) {
        avatarUrl.value = URL.createObjectURL(file);
        // Thêm logic gửi file lên server nếu cần
    }
};

// Xử lý submit form
const handleSubmit = () => {
    console.log('Form submitted:', form.value);
    // Thêm logic gửi dữ liệu lên server nếu cần
};
</script>

<style scoped></style>
